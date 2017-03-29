<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Notifications\OverdueFeedback;
use App\Notifications\ProblematicAssessment;
use Carbon\Carbon;
use App\Assessment;
use App\Wlm\FakeWlmClient;
use App\Wlm\FakeBrokenWlmClient;
use App\Course;
use App\Mail\WlmImportProblem;

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
        $this->assertEquals(0, $staff->newFeedbacks()->count());
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

    /** @test */
    public function running_the_auto_signoff_command_signs_off_appropriate_assessments()
    {
        $canBeSignedOff = factory(Assessment::class, 3)->create([
            'deadline' => Carbon::now()->subWeeks(6)
        ]);
        $cantBeSignedOff = factory(Assessment::class, 2)->create([
            'deadline' => Carbon::now()->subWeeks(2)
        ]);

        $this->assertEquals(5, Assessment::noAcademicFeedback()->count());

        \Artisan::call('assessments:autosignoff');

        $this->assertEquals(2, Assessment::noAcademicFeedback()->count());
    }

    /** @test */
    public function running_the_wlm_import_command_creates_correct_data()
    {
        $this->app->instance('App\Wlm\WlmClientInterface', new FakeWlmClient);

        \Artisan::call('assessments:wlmimport');

        $this->assertCount(2, Course::all());
    }

    /** @test */
    public function running_the_wlm_import_command_notifies_sysadmin_if_it_goes_wrong()
    {
        Mail::fake();

        $this->app->instance('App\Wlm\WlmClientInterface', new FakeBrokenWlmClient);

        \Artisan::call('assessments:wlmimport');

        Mail::assertSent(WlmImportProblem::class, function ($mail) {
            return $mail->hasTo(config('assessments.sysadmin_email'));
        });
    }
}
