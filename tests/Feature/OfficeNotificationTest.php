<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Notifications\OverdueFeedback;
use App\Notifications\ProblematicAssessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OfficeNotificationTest extends TestCase
{
    /** @test */
    public function teaching_office_are_notified_when_an_assessment_becomes_problematic()
    {
        Notification::fake();
        $course = $this->createCourse();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course->students()->sync([$student1->id, $student2->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);
        $assessment2 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);
        $student1->recordFeedback($assessment1);
        $student2->recordFeedback($assessment1);

        $assessment1->notifyIfProblematic();
        $assessment2->notifyIfProblematic();

        Notification::assertSentTo(
            $assessment1,
            ProblematicAssessment::class
        );
        Notification::assertNotSentTo(
            $assessment2,
            ProblematicAssessment::class
        );
        $this->assertEquals(true, $assessment1->office_notified);
        $this->assertEquals(false, $assessment2->office_notified);
    }

    /** @test */
    public function teaching_office_are_only_notified_once_about_problematic_assessments()
    {
        Notification::fake();
        $course = $this->createCourse();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course->students()->sync([$student1->id, $student2->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);
        $student1->recordFeedback($assessment1);
        $student2->recordFeedback($assessment1);

        $assessment1->markOfficeNotified();

        $assessment1->notifyIfProblematic();

        Notification::assertNotSentTo(
            $assessment1,
            ProblematicAssessment::class
        );
    }

    /** @test */
    public function can_be_notified_about_unread_feedbacks()
    {
        Notification::fake();
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course->students()->sync([$student1->id, $student2->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'staff_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'staff_id' => $staff->id]);
        $student1->recordFeedback($assessment1);
        $student2->recordFeedback($assessment2);

        $staff->notifyAboutNewFeedback();

        Notification::assertSentTo($staff, OverdueFeedback::class);
        $this->assertEquals(0, $staff->newFeedbacks()->count());
    }
}
