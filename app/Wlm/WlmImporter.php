<?php

namespace App\Wlm;

use App\Course;
use App\User;

class WlmImporter
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function run()
    {
        $courses = $this->client->getCourses();
        if ($this->client->statusCode != 200) {
            throw new \Exception('Failed to get data from the WLM');
        }
        $courses->each(function ($wlmCourse) {
            $course = $this->courseFromWlm($wlmCourse);
            $course->staff()->sync($this->staffFromWlm($wlmCourse));
            $course->students()->sync($this->studentsFromWlm($wlmCourse));
        });
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
            $wlmStaff['Email'] = $this->getStaffEmail($wlmStaff);
            return User::staffFromWlmData($wlmStaff);
        })->pluck('id');
    }

    protected function studentsFromWlm($wlmCourse)
    {
        if (!array_key_exists('Students', $wlmCourse)) {
            return collect([]);
        }
        return collect($wlmCourse['Students'])->map(function ($wlmStudent) {
            return User::studentFromWlmData($wlmStudent);
        })->pluck('id');
    }

    protected function getStaffEmail($wlmStaff)
    {
        $staff = $this->client->getStaff($wlmStaff['GUID']);
        return $staff['Email'];
    }
}
