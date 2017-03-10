<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Exceptions\NotYourCourseException;

class FeedbackTest extends TestCase
{
    /** @test */
    public function we_can_get_the_total_negative_feedback_for_an_assessment()
    {
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->saveMany([$student1, $student2]);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'user_id' => $student1->id, 'feedback_given' => false]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'user_id' => $student2->id, 'feedback_given' => false]);

        $this->assertEquals(2, $assessment->totalNegativeFeedbacks());
    }

    /** @test */
    public function we_can_get_the_percantage_of_negative_feedback_for_an_assessment()
    {
        $students = factory(\App\User::class, 10)->states('student')->create();
        $course = $this->createCourse();
        $course->students()->saveMany($students);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        foreach ($students->take(6) as $student) {
            $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'user_id' => $student->id, 'feedback_given' => false]);
        }

        $this->assertEquals(60, $assessment->percentageNegativeFeedbacks());
    }

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
}
