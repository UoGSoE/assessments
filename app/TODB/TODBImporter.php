<?php

namespace App\TODB;

use App\Course;
use App\Mail\TODBImportProblem;
use App\TODB\TODBClientInterface;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TODBImporter
{
    protected $client;

    public function __construct(TODBClientInterface $client)
    {
        $this->client = $client;
    }

    public function run($maximumCourses = 1000000)
    {
        try {
            $courses = $this->client->getCourses();
            if ($this->client->statusCode != 200) {
                throw new \Exception('Failed to get data from the TODB');
            }

            $courses->filter(function ($todbCourse) {
                if (!preg_match('/^(ENG|TEST)/', $todbCourse['code'])) {
                    return false;
                }
                return true;
            })
            ->take($maximumCourses)
            ->each(function ($todbCourse) {
                $course = Course::fromTODBData($todbCourse);
                $course->staff()->sync($this->getStaff($todbCourse['staff']));
                $course->students()->sync($this->getStudents($todbCourse['students']));
            });
        } catch (\Exception $e) {
            Mail::to(config('assessments.sysadmin_email'))->send(new TODBImportProblem($e->getMessage()));
            return false;
        }
        return true;
    }

    public function getStaff($todbStaffList)
    {
        $staffIds = [];
        foreach ($todbStaffList as $todbStaff) {
            $staff = User::staff()->where('username', $todbStaff['guid'])->first();
            if (!$staff) {
                $staff = User::create([
                    'username' => $todbStaff['guid'],
                    'forenames' => $todbStaff['forenames'],
                    'surname' => $todbStaff['surname'],
                    'email' => $todbStaff['email'],
                    'is_student' => false,
                    'password' => bcrypt(Str::random(32))
                ]);
            }
            $staffIds[] = $staff->id;
        }
        return $staffIds;
    }

    public function getStudents($todbStudentList)
    {
        $studentIds = [];
        foreach ($todbStudentList as $todbStudent) {
            $guid = $todbStudent['matric'] . $todbStudent['surname'][0];
            $student = User::student()->where('username', $guid)->first();
            if (!$student) {
                $student = User::create([
                    'username' => $guid,
                    'forenames' => $todbStudent['forenames'],
                    'surname' => $todbStudent['surname'],
                    'email' => $todbStudent['email'],
                    'is_student' => true,
                    'password' => bcrypt(Str::random(32))
                ]);
            }
            $studentIds[] = $student->id;
        }
        return $studentIds;
    }
}
