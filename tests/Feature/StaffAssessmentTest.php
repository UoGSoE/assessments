<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'staff_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'staff_id' => $staff->id]);

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
        $assessment1 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'staff_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'staff_id' => $staff->id]);

        $student1->recordFeedback($assessment1);
        $student2->recordFeedback($assessment2);
        $staff->markAllFeedbacksAsRead();

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
        $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE1', 'staff_id' => $staff->id]);
        $assessment2 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE2', 'staff_id' => $staff->id]);
        $assessment3 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE3', 'staff_id' => $staff->id]);
        $assessment4 = $this->createAssessment(['type' => 'SOMEONEELSES']);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertSee($assessment1->type);
        $response->assertSee($assessment2->type);
        $response->assertSee($assessment3->type);
        $response->assertSee($assessment4->type);
        $response->assertSee($assessment1->deadline->toIso8601String());
        $response->assertSee($assessment2->deadline->toIso8601String());
        $response->assertSee($assessment3->deadline->toIso8601String());
    }

    /** @test */
    public function staff_can_see_assessments_where_feedback_is_due()
    {
        $staff = $this->createStaff();
        $course1 = $this->createCourse();
        $course1->staff()->sync([$staff->id]);
        $course2 = $this->createCourse();
        $course2->staff()->sync([$staff->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE1', 'staff_id' => $staff->id, 'deadline' => Carbon::now()->subWeeks(5)->startOfWeek()]);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($course1->code);
        $response->assertSee($assessment1->type);
    }

    /** @test */
    public function staff_can_see_feedbacks_for_assessments_which_are_theirs()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id]);

        $response = $this->actingAs($staff)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee('Feedbacks');
        $response->assertSee($feedback->student->fullName());
        $response->assertSee('Feedback Completed');
    }

    /** @test */
    public function staff_cant_see_feedbacks_for_assessments_which_are_not_theirs()
    {
        $staff1 = $this->createStaff();
        $staff2 = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff1->id, $staff2->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff2->id]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id]);

        $response = $this->actingAs($staff1)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertDontSee('Feedbacks');
    }

    /** @test */
    public function staff_can_mark_that_an_assessment_has_had_complete_feedback_left()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id, 'deadline' => Carbon::now()->subWeeks(4)]);
        $givenDate = $assessment->feedback_due->subDays(2);

        $response = $this->actingAs($staff)->post(route('feedback.complete', $assessment->id), ['date' => $givenDate->format('d/m/Y')]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertEquals($assessment->fresh()->feedback_left->format('d/m/Y'), $givenDate->format('d/m/Y'));
    }

    /** @test */
    public function staff_cant_set_an_invalid_date_for_complete_feedback_left()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id]);

        $response = $this->actingAs($staff)->post(route('feedback.complete', $assessment->id), ['date' => 'blah blah blah']);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['date']);
        $this->assertNull($assessment->fresh()->feedback_left);
    }

    /** @test */
    public function can_see_the_date_feedback_was_marked_as_complete_if_it_has_been_marked_as_such()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id]);
        $givenDate = $assessment->feedback_due->subDays(2);
        $assessment->feedback_left = $givenDate;
        $assessment->save();

        $response = $this->actingAs($staff)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee($givenDate->format('d/m/Y'));
    }

    /** @test */
    public function can_see_the_form_form_feedback_completed_if_it_hasnt_been_marked_as_such_and_it_is_their_assessment()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id, 'deadline' => Carbon::now()->subWeeks(4)]);

        $response = $this->actingAs($staff)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee(route('feedback.complete', $assessment->id));
    }

    /** @test */
    public function cant_see_the_form_form_feedback_completed_if_it_isnt_their_assessment()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id]);

        $response = $this->actingAs($staff)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertDontSee(route('feedback.complete', $assessment->id));
    }

    /** @test */
    public function cant_see_the_form_form_feedback_completed_if_assessment_not_passed_its_deadline_yet()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $course->staff()->sync([$staff->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id, 'deadline' => Carbon::now()->addWeeks(10)]);

        $response = $this->actingAs($staff)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertDontSee('Save');
        $response->assertDontSee(route('feedback.complete', $assessment->id));
    }
}
