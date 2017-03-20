<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OverdueFeedback;
use App\Notifications\ProblematicAssessment;
use Carbon\Carbon;

class ArtisanTest extends TestCase
{
    /** @test */
    public function running_the_staff_notification_command_triggers_notifications()
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

        \Artisan::call('assessments:notifystaff');

        Notification::assertSentTo($staff, OverdueFeedback::class);
        $this->assertEquals(0, $staff->unreadFeedbacks()->count());
    }

    /** @test */
    public function running_the_office_notification_command_triggers_notifications()
    {
        Notification::fake();
        $course = $this->createCourse();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course->students()->sync([$student1->id, $student2->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);
        $student1->recordFeedback($assessment);
        $student2->recordFeedback($assessment);

        \Artisan::call('assessments:notifyoffice');

        Notification::assertSentTo($assessment, ProblematicAssessment::class);
        $this->assertTrue($assessment->fresh()->officeHaveBeenNotified());
    }
}
