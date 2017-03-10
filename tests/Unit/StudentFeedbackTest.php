<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Exceptions\NotYourCourseException;
use App\Exceptions\TooMuchTimePassedException;

class StudentFeedbackTest extends TestCase
{
    /** @test */
    public function student_can_add_negative_feedback_for_an_assessment()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id]);

        $student->recordFeedback($assessment);

        $this->assertEquals(1, $assessment->totalNegativeFeedbacks());
    }

    /** @test */
    public function student_cant_add_feedback_for_an_assessment_which_isnt_associated_with_one_of_their_courses()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $assessment = $this->createAssessment(['course_id' => $course->id]);

        try {
            $student->recordFeedback($assessment);
        } catch (NotYourCourseException $e) {
            return;
        }

        return $this->fail('Student added feedback for an assessment which was not for one of their courses, but no exception thrown');        
    }

    /** @test */
    public function student_cant_add_feedback_for_an_assessment_which_is_way_back_in_time()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => \Carbon\Carbon::now()->subMonths(3)]);

        try {
            $student->recordFeedback($assessment);
        } catch (TooMuchTimePassedException $e) {
            return;
        }

        return $this->fail('Student added feedback for an assessment which was way in the past, but no exception thrown');        
    }

    /** @test */
    public function student_can_only_add_one_feedback()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id]);

        $student->recordFeedback($assessment);
        $student->recordFeedback($assessment);

        $this->assertEquals(1, $assessment->totalNegativeFeedbacks());
    }
}
