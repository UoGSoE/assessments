<?php

namespace App\Wlm;

use App\Course;
use App\User;
use App\Wlm\WlmClientInterface;
use App\Mail\WlmImportProblem;
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
                if (!preg_match('/^(ENG|TEST)/', $wlmCourse['Code'])) {
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
            $f = $e->getMessage();
            $q = new WlmImportProblem($e->getMessage());
//            Mail::to(config('assessments.sysadmin_email'))->send(new WlmImportProblem($e->getMessage()));
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
        if (!array_key_exists('Staff', $wlmCourse)) {
            return collect([]);
        }
        return collect($wlmCourse['Staff'])->map(function ($wlmStaff) {
            if (!$this->staffList->has($wlmStaff['GUID'])) {
                $wlmStaff['Email'] = $this->getStaffEmail($wlmStaff);
                $this->staffList[$wlmStaff['GUID']] = User::staffFromWlmData($wlmStaff);
            }
            return $this->staffList[$wlmStaff['GUID']];
        })->pluck('id');
    }

    protected function studentsFromWlm($wlmCourse)
    {
        if (!array_key_exists('Students', $wlmCourse)) {
            return collect([]);
        }
        return collect($wlmCourse['Students'])->map(function ($wlmStudent) {
            if (!$this->studentList->has($wlmStudent['Matric'])) {
                $this->studentList[$wlmStudent['Matric']] = User::studentFromWlmData($wlmStudent);
            }
            return $this->studentList[$wlmStudent['Matric']];
        })->pluck('id');
    }

    protected function getStaffEmail($wlmStaff)
    {
        $staff = $this->client->getStaff($wlmStaff['GUID']);
        if (!preg_match('/\@/', $staff['Email'])) {
            $staff['Email'] = $wlmStaff['GUID'] . '@glasgow.ac.uk';
        }
        return $staff['Email'];
    }
}
