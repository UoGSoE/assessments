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
    public function if_student_left_negative_feedback_given_json_has_feedback_missed_flagged()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'student_id' => $student->id, 'feedback_given' => false]);

        $json = $student->assessmentsAsJson();

        $this->assertEquals([
            'feedback_missed' => true,
            'course_code' => $course->code,
            'start' => $assessment->deadline->toIso8601String(),
            'end' => $assessment->deadline->addHours(1)->toIso8601String(),
            'feedback_due' => $assessment->feedback_due->toIso8601String(),
            'type' => $assessment->type,
            'course_title' => $course->title,
            'id' => $assessment->id,
            'title' => $assessment->title,
            'mine' => true,
        ], json_decode($json, true)[0]);
    }

    /** @test */
    public function we_can_fetch_all_assessments_for_a_given_student_as_json()
    {
        $student = $this->createStudent();
        $courses = factory(Course::class, 20)->create()->each(function ($course) use ($student) {
            $course->students()->attach($student);
            $assessments = factory(Assessment::class, 5)->create();
            $course->assessments()->saveMany($assessments);
        });

        $json = json_decode($student->assessmentsAsJson(), true);

        $this->assertEquals(100, count($json));

    }

    /** @test */
    public function we_can_fetch_all_assessments_for_a_given_staffmember_as_json()
    {
        $staff = $this->createStaff();
        $courses = factory(Course::class, 20)->create()->each(function ($course) use ($staff) {
            $course->staff()->attach($staff);
            $assessments = factory(Assessment::class, 5)->create();
            $course->assessments()->saveMany($assessments);
        });

        $json = json_decode($staff->assessmentsAsJson(), true);

        $this->assertEquals(100, count($json));

    }
}
