<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Notifications\OverdueFeedback;
use Carbon\Carbon;

class StaffAssessmentTest extends TestCase
{
    /** @test */
    public function can_get_a_list_of_feedbacks_which_have_not_already_been_notified_about()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course->students()->sync([$student1->id, $student2->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'user_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'user_id' => $staff->id]);

        $student1->recordFeedback($assessment1);
        $student2->recordFeedback($assessment2);

        $this->assertEquals(2, $staff->unreadFeedbacks()->count());
    }

    /** @test */
    public function can_mark_all_unread_feedbacks_as_read()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course->students()->sync([$student1->id, $student2->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'user_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'user_id' => $staff->id]);

        $student1->recordFeedback($assessment1);
        $student2->recordFeedback($assessment2);
        $staff->markAllFeedbacksAsRead();

        $this->assertEquals(0, $staff->unreadFeedbacks()->count());

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
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'user_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'user_id' => $staff->id]);
        $student1->recordFeedback($assessment1);
        $student2->recordFeedback($assessment2);

        $staff->notifyAboutUnreadFeedback();

        Notification::assertSentTo($staff, OverdueFeedback::class);
        $this->assertEquals(0, $staff->unreadFeedbacks()->count());
        
    }
}
