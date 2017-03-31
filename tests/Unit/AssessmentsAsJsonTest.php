<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Course;
use App\Assessment;
use App\AssessmentFeedback;

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
        $courses = factory(Course::class, 2)->create()->each(function ($course) use ($student) {
            $course->students()->attach($student);
            $assessments = factory(Assessment::class, 3)->create();
            $course->assessments()->saveMany($assessments);
        });

        $json = json_decode($student->assessmentsAsJson(), true);

        $this->assertEquals(6, count($json));

    }

    /** @test */
    public function we_can_fetch_all_assessments_for_a_given_staffmember_as_json()
    {
        $staff = $this->createStaff();
        // create 6 assessments in total
        $courses = factory(Course::class, 2)->create()->each(function ($course) use ($staff) {
            $course->staff()->attach($staff);
            $assessments = factory(Assessment::class, 3)->create();
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
