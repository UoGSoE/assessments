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

    /** @test */
    public function staff_can_see_all_applicable_assessments()
    {
        $staff = $this->createStaff();
        $course1 = $this->createCourse();
        $course1->staff()->sync([$staff->id]);
        $course2 = $this->createCourse();
        $course2->staff()->sync([$staff->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE1', 'user_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE2', 'user_id' => $staff->id]);
        $assessment3 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE3', 'user_id' => $staff->id]);
        $assessment4 = $this->createAssessment(['type' => 'SHOULDNTSHOWUP']);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertSee($assessment1->type);
        $response->assertSee($assessment2->type);
        $response->assertSee($assessment3->type);
        $response->assertDontSee($assessment4->type);
        $response->assertSee($assessment1->deadline->toIso8601String());
        $response->assertSee($assessment2->deadline->toIso8601String());
        $response->assertSee($assessment3->deadline->toIso8601String());
    }

    /** @test */
    public function staff_can_see_feedbacks_for_assessments_which_are_theirs()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'user_id' => $staff->id]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id]);

        $response = $this->actingAs($staff)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee('Feedbacks');
        $response->assertSee($feedback->student->fullName());
    }

    /** @test */
    public function staff_cant_see_feedbacks_for_assessments_which_are_not_theirs()
    {
        $staff1 = $this->createStaff();
        $staff2 = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff1->id, $staff2->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'user_id' => $staff2->id]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id]);

        $response = $this->actingAs($staff1)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertDontSee('Feedbacks');
    }
}
