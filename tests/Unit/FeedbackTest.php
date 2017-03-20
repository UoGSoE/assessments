<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'student_id' => $student1->id, 'feedback_given' => false]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'student_id' => $student2->id, 'feedback_given' => false]);

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
            $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'student_id' => $student->id, 'feedback_given' => false]);
        }

        $this->assertEquals(60, $assessment->percentageNegativeFeedbacks());
    }

    /** @test */
    public function an_assessment_with_lots_of_negative_feedback_is_flagged()
    {
        $students = factory(\App\User::class, 10)->states('student')->create();
        $course = $this->createCourse();
        $course->students()->saveMany($students);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        foreach ($students->take(6) as $student) {
            $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'student_id' => $student->id, 'feedback_given' => false]);
        }

        $this->assertTrue($assessment->isProblematic());
    }
}
