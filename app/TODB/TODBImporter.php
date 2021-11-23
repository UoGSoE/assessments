<?php

namespace App\TODB;

use App\Course;
use App\Mail\TODBImportProblem;
use App\TODB\TODBClientInterface;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TODBImporter
{
    protected $client;
    protected $staffList;
    protected $studentList;
    protected $courseList;

    public function __construct(TODBClientInterface $client)
    {
        $this->client = $client;
        $this->staffList = collect([]);
        $this->studentList = collect([]);
        $this->courseList = collect([]);
    }

    public function run($maximumCourses = 1000000)
    {
        try {
            $courses = $this->client->getCourses();
            if ($this->client->statusCode != 200) {
                throw new \Exception('Failed to get data from the TODB');
            }
            $courseIds = $courses->filter(function ($todbCourse) {
                if (!preg_match('/^(ENG|TEST)/', $todbCourse['Code'])) {
                    return false;
                }
                return true;
            })->take($maximumCourses)->each(function ($todbCourse) {
                $course = $this->courseFromTODB($todbCourse);
                $this->courseList[$course->code] = $course;
                $course->staff()->sync($this->staffFromTODB($todbCourse));
                $course->students()->sync($this->studentsFromTODB($todbCourse));
            });
        } catch (\Exception $e) {
            Mail::to(config('assessments.sysadmin_email'))->send(new TODBImportProblem($e->getMessage()));
            return false;
        }
        return true;
    }

    public function sync($maximumCourses = 1000000)
    {
        $importResult = $this->run($maximumCourses);
        if ($importResult) {
            $this->removeDataNotInTODB();
        }
        return $importResult;
    }

    protected function removeDataNotInTODB()
    {
        // little last sanity check before we erase the whole system...
        if ($this->staffList->isEmpty() or $this->studentList->isEmpty() or $this->courseList->isEmpty()) {
            return;
        }

        User::staff()->whereNotIn('id', $this->staffList->pluck('id'))->delete();
        User::student()->whereNotIn('id', $this->studentList->pluck('id'))->delete();
        Course::whereNotIn('id', $this->courseList->pluck('id'))->delete();
    }

    protected function courseFromTODB($todbCourse)
    {
        return Course::fromTODBData($todbCourse);
    }

    protected function staffFromTODB($todbCourse)
    {
        if (!array_key_exists('Staff', $todbCourse)) {
            return collect([]);
        }
        return collect($todbCourse['Staff'])->map(function ($todbStaff) {
            if (!$this->staffList->has($todbStaff['GUID'])) {
                $todbStaff['Email'] = $this->getStaffEmail($todbStaff);
                $this->staffList[$todbStaff['GUID']] = User::staffFromTODBData($todbStaff);
            }
            return $this->staffList[$todbStaff['GUID']];
        })->pluck('id');
    }

    /**
     * This tries to extract the student info from the original TODB 'Students'
     * array.  It's a little nasty as once in a while there is a student matric
     * which is encoded in a weird way (not utf8 or ascii - probably an excel-ism)
     * so that's why there is some unpleasant reject() and try/catch stuff in
     * here... :: sadface ::
     */
    protected function studentsFromTODB($todbCourse)
    {
        if (!array_key_exists('Students', $todbCourse)) {
            return collect([]);
        }
        return collect($todbCourse['Students'])->reject(function ($todbStudent) {
            return preg_match('/^[0-9]{7}$/u', $todbStudent['Matric']) !== 1;
        })->map(function ($todbStudent) use ($todbCourse) {
            if (!$this->studentList->has($todbStudent['Matric'])) {
                try {
                    $this->studentList[$todbStudent['Matric']] = User::studentFromTODBData($todbStudent);
                } catch (\Exception $e) {
                    Log::info('TODB Import - Failed to insert student with matric ' . $todbStudent['Matric']);
                    return false;
                }
            }
            return $this->studentList[$todbStudent['Matric']];
        })->reject(function ($student) {
            return (bool) !$student;
        })->pluck('id');
    }

    protected function getStaffEmail($todbStaff)
    {
        $staff = $this->client->getStaff($todbStaff['GUID']);
        if (!preg_match('/\@/', $staff['Email'])) {
            $staff['Email'] = $todbStaff['GUID'] . '@glasgow.ac.uk';
        }
        return $staff['Email'];
    }
}
