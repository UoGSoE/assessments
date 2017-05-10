<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use App\Assessment;

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
    public function we_can_get_the_number_of_feedbacks_left_for_a_member_of_staff()
    {
        $students = factory(\App\User::class, 3)->states('student')->create();
        $course = $this->createCourse();
        $course->students()->saveMany($students);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        foreach ($students as $student) {
            $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'student_id' => $student->id, 'feedback_given' => false]);
        }

        $this->assertEquals(3, $assessment->staff->totalStudentFeedbacks());
    }

    /** @test */
    public function we_can_get_the_number_of_feedback_deadlines_a_member_of_staff_has_missed()
    {
        $staff = $this->createStaff();
        $noFeedbackLeft = $this->createAssessment([
            'staff_id' => $staff->id,
            'deadline' => Carbon::now()->subWeeks(4),
        ]);
        $leftOnTime = $this->createAssessment([
            'staff_id' => $staff->id,
            'deadline' => Carbon::now()->subWeeks(4),
            'feedback_left' => Carbon::now()->subweeks(2)
        ]);
        $leftLate = $this->createAssessment([
            'staff_id' => $staff->id,
            'deadline' => Carbon::now()->subWeeks(4),
            'feedback_left' => Carbon::now(),
        ]);

        $this->assertEquals(2, $staff->numberOfMissedDeadlines());
    }

    /** @test */
    public function an_assessment_with_lots_of_negative_feedback_is_flagged()
    {
        $students = factory(\App\User::class, 5)->states('student')->create();
        $course = $this->createCourse();
        $course->students()->saveMany($students);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        foreach ($students->take(3) as $student) {
            $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'student_id' => $student->id, 'feedback_given' => false]);
        }

        $this->assertTrue($assessment->isProblematic());
    }

    /** @test */
    public function we_can_get_a_list_of_assessments_with_no_feedback_left_by_academics()
    {
        factory(Assessment::class, 2)->create(['deadline' => Carbon::now()->subWeeks(3)]);
        factory(Assessment::class, 1)->create(['deadline' => Carbon::now()->subWeeks(3), 'feedback_left' => Carbon::now()]);

        $this->assertEquals(2, Assessment::notSignedOff()->count());

    }

    /** @test */
    public function an_assessment_can_be_signed_off_if_there_are_no_negative_feedbacks_and_a_sufficient_amount_of_time_has_passed()
    {
        $assessment = $this->createAssessment(['deadline' => Carbon::now()->subWeeks(7)]);

        $this->assertTrue($assessment->canBeAutoSignedOff());
    }

    /** @test */
    public function an_assessment_cant_be_signed_off_if_it_has_negative_feedbacks()
    {
        $assessment = $this->createAssessment(['deadline' => Carbon::now()->subWeeks(7)]);
        $feedback = $this->createFeedback(['assessment_id' => $assessment->id]);

        $this->assertFalse($assessment->canBeAutoSignedOff());
    }

    /** @test */
    public function an_assessment_cant_be_signed_off_too_soon()
    {
        $assessment = $this->createAssessment(['deadline' => Carbon::now()->subWeeks(5)]);

        $this->assertFalse($assessment->canBeAutoSignedOff());
    }

    /** @test */
    public function an_assessment_can_be_signed_off_by_the_system()
    {
        $assessment = $this->createAssessment(['deadline' => Carbon::now()->subWeeks(4)]);

        $assessment->autoSignOff();

        $this->assertNotNull($assessment->feedbackWasGiven());
        $this->assertEquals($assessment->feedback_due, $assessment->feedback_left);
    }

    /** @test */
    public function a_bit_of_feedback_can_be_marked_as_notifying_the_staff_member()
    {
        $feedback = $this->createFeedback();
        $this->assertFalse($feedback->staffNotified());

        $feedback->markAsNotified();

        $this->assertTrue($feedback->staffNotified());
    }
}
