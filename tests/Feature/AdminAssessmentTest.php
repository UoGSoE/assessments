<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class AdminAssessmentTest extends TestCase
{
    /** @test */
    public function admin_can_edit_an_assessment()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment();
        $now = Carbon::now();

        $response = $this->actingAs($admin)->post(route('assessment.update', $assessment->id), [
            'date' => $now->format('d/m/Y'),
            'time' => $now->format('H:i'),
            'type' => 'Whatever',
            'comment' => 'I am very happy with my cheese purchase',
            'staff_id' => $staff->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'comment' => 'I am very happy with my cheese purchase',
            'type' => 'Whatever',
            'staff_id' => $staff->id,
            'deadline' => $now->format('Y-m-d H:i:00'),
        ]);
    }

    /** @test */
    public function admin_cant_save_an_assessment_with_invalid_data()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment();

        $response = $this->actingAs($admin)->post(route('assessment.update', $assessment->id), [
            'date' => 'blah',
            'time' => 'something',
            'staff_id' => 1000000,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['date', 'time', 'staff_id']);
        $freshAssessment = $assessment->fresh();
        $this->assertEquals($assessment->user_id, $freshAssessment->user_id);
        $this->assertEquals($assessment->deadline->timestamp, $freshAssessment->deadline->timestamp);
    }

    /** @test */
    public function admin_can_delete_an_assessment_and_all_associated_records()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);
        $student->recordFeedback($assessment);

        $response = $this->actingAs($admin)->delete(route('assessment.destroy', $assessment->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
        $this->assertDatabaseMissing('assessment_feedbacks', ['assessment_id' => $assessment->id, 'student_id' => $student->id]);
    }

    /** @test */
    public function admin_can_create_an_assessment()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $now = Carbon::now();

        $response = $this->actingAs($admin)->post(route('assessment.store'), [
            'date' => $now->format('d/m/Y'),
            'time' => $now->format('H:i'),
            'type' => 'Whatever',
            'comment' => 'I am very happy with my cheese purchase',
            'staff_id' => $staff->id,
            'course_id' => $course->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('assessments', [
            'comment' => 'I am very happy with my cheese purchase',
            'type' => 'Whatever',
            'staff_id' => $staff->id,
            'deadline' => $now->format('Y-m-d H:i:00'),
            'course_id' => $course->id,
        ]);
    }

}
