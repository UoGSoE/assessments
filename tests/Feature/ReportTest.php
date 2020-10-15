<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ReportTest extends TestCase
{
    /** @test */
    public function admin_can_view_the_overall_feedback_report()
    {
        $admin = $this->createAdmin();
        $assessments = \App\Models\Assessment::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('report.feedback'));

        $response->assertStatus(200);
        $response->assertSee('Feedback Report');
        foreach ($assessments as $assessment) {
            $response->assertSee($assessment->course->code);
            $response->assertSee($assessment->staff->fullName());
            $response->assertSee($assessment->type);
            $response->assertSee($assessment->feedback_due->format('Y-m-d'));
            $response->assertSee($assessment->reportSignedOff());
            $response->assertSee(''.$assessment->totalNegativeFeedbacks());
        }
    }

    /** @test */
    public function admin_can_view_the_staff_report()
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->count(2)->staff()->create()->each(function ($user) {
            $assessments = \App\Models\Assessment::factory()->count(2)->create(['staff_id' => $user->id]);
            $assessments->each(function ($assessment) {
                $feedbacks = \App\Models\AssessmentFeedback::factory()->count(rand(1, 5))->create(['assessment_id' => $assessment->id]);
            });
        });

        $response = $this->actingAs($admin)->get(route('report.staff'));

        $response->assertStatus(200);
        $response->assertSee('Staff Report');
        foreach ($staff as $user) {
            $response->assertSee($user->fullName());
            $response->assertSee(''.$user->numberOfAssessments());
            $response->assertSee(''.$user->totalStudentFeedbacks());
            $response->assertSee(''.$user->numberOfMissedDeadlines());
        }
        $response->assertSee("is-admin-{$admin->id}");
    }
}
