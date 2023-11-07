<?php

namespace App\Wlm;

use App\Mail\WlmImportProblem;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WlmImporter
{
    protected $client;

    protected $staffList;

    protected $studentList;

    protected $courseList;

    public function __construct(WlmClientInterface $client)
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
                throw new \Exception('Failed to get data from the WLM');
            }
            $courseIds = $courses->filter(function ($wlmCourse) {
                if (! preg_match('/^(ENG|TEST)/', $wlmCourse['Code'])) {
                    return false;
                }

                return true;
            })->take($maximumCourses)->each(function ($wlmCourse) {
                $course = $this->courseFromWlm($wlmCourse);
                $this->courseList[$course->code] = $course;
                $course->staff()->sync($this->staffFromWlm($wlmCourse));
                $course->students()->sync($this->studentsFromWlm($wlmCourse));
            });
        } catch (\Exception $e) {
            Mail::to(config('assessments.sysadmin_email'))->send(new WlmImportProblem($e->getMessage()));

            return false;
        }

        return true;
    }

    public function sync($maximumCourses = 1000000)
    {
        $importResult = $this->run($maximumCourses);
        if ($importResult) {
            $this->removeDataNotInWlm();
        }

        return $importResult;
    }

    protected function removeDataNotInWlm()
    {
        // little last sanity check before we erase the whole system...
        if ($this->staffList->isEmpty() or $this->studentList->isEmpty() or $this->courseList->isEmpty()) {
            return;
        }

        User::staff()->whereNotIn('id', $this->staffList->pluck('id'))->delete();
        User::student()->whereNotIn('id', $this->studentList->pluck('id'))->delete();
        Course::whereNotIn('id', $this->courseList->pluck('id'))->delete();
    }

    protected function courseFromWlm($wlmCourse)
    {
        return Course::fromWlmData($wlmCourse);
    }

    protected function staffFromWlm($wlmCourse)
    {
        if (! array_key_exists('Staff', $wlmCourse)) {
            return collect([]);
        }

        return collect($wlmCourse['Staff'])->map(function ($wlmStaff) {
            if (! $this->staffList->has($wlmStaff['GUID'])) {
                $wlmStaff['Email'] = $this->getStaffEmail($wlmStaff);
                $this->staffList[$wlmStaff['GUID']] = User::staffFromWlmData($wlmStaff);
            }

            return $this->staffList[$wlmStaff['GUID']];
        })->pluck('id');
    }

    /**
     * This tries to extract the student info from the original WLM 'Students'
     * array.  It's a little nasty as once in a while there is a student matric
     * which is encoded in a weird way (not utf8 or ascii - probably an excel-ism)
     * so that's why there is some unpleasant reject() and try/catch stuff in
     * here... :: sadface ::.
     */
    protected function studentsFromWlm($wlmCourse)
    {
        if (! array_key_exists('Students', $wlmCourse)) {
            return collect([]);
        }

        return collect($wlmCourse['Students'])->reject(function ($wlmStudent) {
            return preg_match('/^[0-9]{7}$/u', $wlmStudent['Matric']) !== 1;
        })->map(function ($wlmStudent) {
            if (! $this->studentList->has($wlmStudent['Matric'])) {
                try {
                    $this->studentList[$wlmStudent['Matric']] = User::studentFromWlmData($wlmStudent);
                } catch (\Exception $e) {
                    Log::info('WLM Import - Failed to insert student with matric '.$wlmStudent['Matric']);

                    return false;
                }
            }

            return $this->studentList[$wlmStudent['Matric']];
        })->reject(function ($student) {
            return (bool) ! $student;
        })->pluck('id');
    }

    protected function getStaffEmail($wlmStaff)
    {
        $staff = $this->client->getStaff($wlmStaff['GUID']);
        if (! preg_match('/\@/', $staff['Email'])) {
            $staff['Email'] = $wlmStaff['GUID'].'@glasgow.ac.uk';
        }

        return $staff['Email'];
    }
}
