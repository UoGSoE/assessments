<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Course;
use App\Assessment;

class StudentAssessmentsAsJsonTest extends TestCase
{
    /** @test */
    public function we_can_fetch_all_assessments_for_a_given_student_as_json()
    {
        $student = $this->createStudent();
        $courses = factory(Course::class, 20)->create()->each(function ($course) use ($student) {
            $course->students()->attach($student);
            $assessments = factory(Assessment::class, 5)->create();
            $course->assessments()->attach($assessments->pluck('id'));
        });

        $json = $student->assessmentsAsJson();

        dd($json);
    }
}
