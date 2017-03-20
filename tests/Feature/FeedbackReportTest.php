<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FeedbackReportTest extends TestCase
{
    /** @test */
    public function admin_can_view_the_overall_feedback_report()
    {
        $admin = $this->createAdmin();
        $assessments = factory(\App\Assessment::class, 10)->create();

        $response = $this->actingAs($admin)->get(route('report.feedback'));

        $response->assertStatus(200);
        $response->assertSee('Feedback Report');
        foreach ($assessments as $assessment) {
            $response->assertSee($assessment->course->code);
            $response->assertSee($assessment->user->fullName());
            $response->assertSee($assessment->type);
            $response->assertSee($assessment->deadline->format('Y-m-d H:i'));
            $response->assertSee($assessment->reportFeedbackLeft());
            $response->assertSee("" . $assessment->totalNegativeFeedbacks());
        }
    }
}
