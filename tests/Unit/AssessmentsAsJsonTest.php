<?php

// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentFeedback;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssessmentsAsJsonTest extends TestCase
{
    /** @test */
    public function if_student_has_no_assessments_an_empty_json_array_is_returned()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);

        $json = $student->assessmentsAsJson();

        $this->assertEquals([], json_decode($json));
    }

    /** @test */
    public function we_can_fetch_all_assessments_for_a_given_student_as_json()
    {
        $student = $this->createStudent();
        $courses = Course::factory()->count(2)->create()->each(function ($course) use ($student) {
            $course->students()->attach($student);
            $assessments = Assessment::factory()->count(3)->create();
            $course->assessments()->saveMany($assessments);
        });

        $json = json_decode($student->assessmentsAsJson(), true);

        $this->assertEquals(6, count($json));
    }

    /** @test */
    public function only_assessments_for_courses_marked_as_active_are_returned_to_students()
    {
        $student = $this->createStudent();
        $course1 = $this->createCourse(['is_active' => true]);
        $course2 = $this->createCourse(['is_active' => false]);
        $course1->students()->sync([$student->id]);
        $course2->students()->sync([$student->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course1->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course2->id]);

        $json = json_decode($student->assessmentsAsJson(), true);

        $this->assertEquals(1, count($json));
    }

    /** @test */
    public function we_can_fetch_all_assessments_for_a_given_staffmember_as_json()
    {
        $staff = $this->createStaff();
        // create 6 assessments in total
        $courses = Course::factory()->count(2)->create()->each(function ($course) use ($staff) {
            $course->staff()->attach($staff);
            $assessments = Assessment::factory()->count(3)->create();
            $course->assessments()->saveMany($assessments);
        });

        $json = json_decode($staff->assessmentsAsJson(), true);

        // this is double the number of created assessments as staff see a copy of each
        // on the date feedback is due
        $this->assertEquals(12, count($json));
    }

    /** @test */
    public function only_assessments_for_active_courses_are_returned_to_staff()
    {
        $staff = $this->createStaff();
        $course1 = $this->createCourse(['is_active' => true]);
        $course2 = $this->createCourse(['is_active' => false]);
        $course1->staff()->attach($staff);
        $course2->staff()->attach($staff);
        $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'staff_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course2->id, 'staff_id' => $staff->id]);

        $json = json_decode($staff->assessmentsAsJson(), true);

        // this is double the number of 'visible' assessments as staff see a copy of each
        // on the date feedback is due
        $this->assertEquals(2, count($json));
    }
}
