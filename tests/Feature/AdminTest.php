<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminTest extends TestCase
{
    /** @test */
    public function admin_can_see_all_assessments()
    {
        $admin = $this->createAdmin();
        $assessment1 = $this->createAssessment();
        $assessment2 = $this->createAssessment();
        $assessment3 = $this->createAssessment();

        $response = $this->actingAs($admin)->get(route('report.assessments'));

        $response->assertStatus(200);
        $response->assertSee($assessment1->course->code);
        $response->assertSee($assessment2->course->code);
        $response->assertSee($assessment3->course->code);
    }

    /** @test */
    public function problematic_assessments_are_flagged_up()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $students = factory(\App\User::class, 10)->states('student')->create();
        $course->students()->sync($students->pluck('id'));
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedbacks = factory(\App\AssessmentFeedback::class, 30)->create(['assessment_id' => $assessment->id, 'course_id' => $course->id]);

        $response = $this->actingAs($admin)->get(route('report.assessments'));

        $response->assertStatus(200);
        $response->assertSee('is-problematic');
    }

    /** @test */
    public function admin_sees_details_of_negative_feedback_when_viewing_an_assessment()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $students = factory(\App\User::class, 10)->states('student')->create();
        $course->students()->sync($students->pluck('id'));
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedbacks = factory(\App\AssessmentFeedback::class, 30)->create(['assessment_id' => $assessment->id, 'course_id' => $course->id]);

        $response = $this->actingAs($admin)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee('Feedbacks Left');
        foreach ($feedbacks as $feedback) {
            $response->assertSee($feedback->student->fullName());
        }
    }
}
