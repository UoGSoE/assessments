<?php

// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Exceptions\AssessmentNotOverdueException;
use App\Exceptions\NotYourCourseException;
use App\Exceptions\TooMuchTimePassedException;
use Carbon\Carbon;
use Tests\TestCase;

class StudentFeedbackTest extends TestCase
{
    /** @test */
    public function student_can_add_negative_feedback_for_an_assessment(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);

        $student->recordFeedback($assessment);

        $this->assertEquals(1, $assessment->totalNegativeFeedbacks());
    }

    /** @test */
    public function student_cant_add_feedback_for_an_assessment_which_isnt_associated_with_one_of_their_courses(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $assessment = $this->createAssessment(['course_id' => $course->id]);

        $this->expectException(NotYourCourseException::class);

        $student->recordFeedback($assessment);
    }

    /** @test */
    public function student_cant_add_feedback_for_an_assessment_which_is_way_back_in_time(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => \Carbon\Carbon::now()->subMonths(4)]);

        $this->expectException(TooMuchTimePassedException::class);

        $student->recordFeedback($assessment);
    }

    /** @test */
    public function student_can_only_add_one_feedback(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);

        $student->recordFeedback($assessment);
        $student->recordFeedback($assessment);

        $this->assertEquals(1, $assessment->totalNegativeFeedbacks());
    }

    /** @test */
    public function student_cant_add_feedback_for_assessment_where_feedback_is_not_overdue(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(2)]);

        $this->expectException(AssessmentNotOverdueException::class);

        $student->recordFeedback($assessment);
    }

    /** @test */
    public function we_can_check_if_a_student_has_left_any_feedback_at_all(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);

        $student->recordFeedback($assessment);

        $this->assertTrue($student->hasLeftFeedbacks());
    }
}
