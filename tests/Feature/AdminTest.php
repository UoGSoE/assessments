<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class AdminTest extends TestCase
{
    /** @test */
    public function admin_can_see_all_assessments(): void
    {
        $admin = $this->createAdmin();
        $assessment1 = $this->createAssessment();
        $assessment2 = $this->createAssessment();
        $assessment3 = $this->createAssessment(['feedback_left' => Carbon::now()->subDays(3)]);

        $response = $this->actingAs($admin)->get(route('report.feedback'));

        $response->assertStatus(200);
        $response->assertSee($assessment1->course->code);
        $response->assertSee($assessment2->course->code);
        $response->assertSee($assessment3->course->code);
        $response->assertSee($assessment3->feedback_left->format('Y-m-d'));
    }

    /** @test */
    public function problematic_assessments_are_flagged_up(): void
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $students = \App\Models\User::factory()->count(3)->student()->create();
        $course->students()->sync($students->pluck('id'));
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedbacks = \App\Models\AssessmentFeedback::factory()->count(2)->create(['assessment_id' => $assessment->id, 'course_id' => $course->id]);

        $response = $this->actingAs($admin)->get(route('report.feedback'));

        $response->assertStatus(200);
        $response->assertSee('is-problematic');
    }

    /** @test */
    public function admin_sees_details_of_negative_feedback_when_viewing_an_assessment(): void
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $students = \App\Models\User::factory()->count(3)->student()->create();
        $course->students()->sync($students->pluck('id'));
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedbacks = \App\Models\AssessmentFeedback::factory()->count(2)->create(['assessment_id' => $assessment->id, 'course_id' => $course->id]);

        $response = $this->actingAs($admin)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee('Feedbacks Left');
        foreach ($feedbacks as $feedback) {
            $response->assertSee($feedback->student->fullName());
        }
    }

    /** @test */
    public function admin_can_see_all_feedbacks_left_by_a_given_student(): void
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedbacks = \App\Models\AssessmentFeedback::factory()->count(3)->create(['assessment_id' => $assessment->id, 'course_id' => $course->id, 'student_id' => $student->id]);

        $response = $this->actingAs($admin)->get(route('student.show', $student->id));

        $response->assertStatus(200);
        $response->assertSee($student->fullName());
        $response->assertSee('Feedbacks Left');
        foreach ($feedbacks as $feedback) {
            $response->assertSee($feedback->course->code);
        }
    }

    /** @test */
    public function admin_can_see_the_details_for_a_course(): void
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $students = User::factory()->student()->count(2)->create();
        $course->students()->sync($students->pluck('id'));
        $assessments = Assessment::factory()->count(2)->create(['course_id' => $course->id]);

        $response = $this->actingAs($admin)->get(route('course.show', $course->id));

        $response->assertStatus(200);
        foreach ($assessments as $assessment) {
            $response->assertSee($assessment->title);
        }
        foreach ($students as $student) {
            $response->assertSee($student->fullName());
        }
    }

    /** @test */
    public function admin_can_see_the_details_for_an_assessment(): void
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'comment' => 'HAPPYDAYS']);
        $student->recordFeedback($assessment);

        $response = $this->actingAs($admin)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee($course->code);
        $response->assertSee($assessment->deadline->format('d/m/Y H:i'));
        $response->assertSee($assessment->feedback_due->format('d/m/Y'));
        $response->assertSee($student->fullName());
        $response->assertSee($assessment->feedbacks()->first()->created_at->format('d/m/Y H:i'));
        $response->assertSee($assessment->comment);
    }

    /** @test */
    public function admin_can_remove_all_old_data(): void
    {
        $admin = $this->createAdmin();
        $assessments = \App\Models\Assessment::factory()->count(2)->create();
        $feedbacks = \App\Models\AssessmentFeedback::factory()->count(2)->create();

        $response = $this->actingAs($admin)->delete(route('admin.clearold'));

        $response->assertStatus(302);
        $response->assertRedirect(route('report.feedback'));
        $response->assertSessionHas('success_message');
        $this->assertCount(0, \App\Models\Assessment::all());
        $this->assertCount(0, \App\Models\AssessmentFeedback::all());
    }

    /** @test */
    public function admin_can_see_details_of_a_member_of_staff(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $course1 = $this->createCourse();
        $course2 = $this->createCourse();
        $course3 = $this->createCourse();
        $course1->staff()->sync([$staff->id]);
        $course2->staff()->sync([$staff->id]);
        $assessments = \App\Models\Assessment::factory()->count(2)->create(['staff_id' => $staff->id, 'course_id' => $course1->id]);
        $assessments = \App\Models\Assessment::factory()->count(3)->create(['staff_id' => $staff->id, 'course_id' => $course2->id]);

        $response = $this->actingAs($admin)->get(route('staff.show', $staff->id));

        $response->assertStatus(200);
        $response->assertSee('Staff Details');
        $response->assertSee($staff->fullName());
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertDontSee($course3->code);
        foreach ($staff->assessments as $assessment) {
            $response->assertSee($assessment->course->code);
        }
    }

    /** @test */
    public function admin_can_toggle_a_users_admin_flag(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $response = $this->actingAs($admin)->post(route('staff.toggle_admin', $staff->id));
        $response->assertStatus(200);
        $this->assertTrue($staff->fresh()->is_admin);

        $response = $this->actingAs($admin)->post(route('staff.toggle_admin', $staff->id));
        $response->assertStatus(200);
        $this->assertFalse($staff->fresh()->is_admin);
    }
}
