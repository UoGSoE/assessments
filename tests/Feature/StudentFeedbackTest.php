<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class StudentFeedbackTest extends TestCase
{
    /** @test */
    public function test_a_student_can_give_feedback_on_an_assessment()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4)]);

        $response = $this->actingAs($student)->post(route('feedback.store', $assessment->id), []);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('assessment_feedbacks', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
    }

    /** @test */
    public function test_a_student_cant_give_feedback_on_assessments_that_they_shouldnt_be_able_to()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);

        // feedback not overdue
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(2)]);
        $response = $this->actingAs($student)->post(route('feedback.store', $assessment->id), []);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing('success_message');
        $this->assertDatabaseMissing('assessment_feedbacks', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);

        // student not on the course the assessment is for
        $assessment = $this->createAssessment(['deadline' => Carbon::now()->subWeeks(2)]);
        $response = $this->actingAs($student)->post(route('feedback.store', $assessment->id), []);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing('success_message');
        $this->assertDatabaseMissing('assessment_feedbacks', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);

        // assessment is way in the past
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subMonths(6)]);
        $response = $this->actingAs($student)->post(route('feedback.store', $assessment->id), []);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing('success_message');
        $this->assertDatabaseMissing('assessment_feedbacks', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);

    }
}
