<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Assessment;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /** @test */
    public function we_can_get_a_json_feed_of_assessments()
    {
        $assessment1 = Assessment::factory()->create();
        $assessment2 = Assessment::factory()->create();

        $response = $this->json('GET', '/api/assessments');

        $response->assertJson([
            'data' => [
                [
                    'id' => $assessment1->id,
                    'deadline' => $assessment1->deadline->format('Y-m-d H:i:s'),
                    'feedback_left' => $assessment1->feedback_left,
                    'feedback_type' => $assessment1->feedback_type,
                    'course' => [
                        'id' => $assessment1->course->id,
                    ],
                    'staff' => [
                        'id' => $assessment1->staff->id,
                    ],
                ],
                [
                    'id' => $assessment2->id,
                    'deadline' => $assessment2->deadline->format('Y-m-d H:i:s'),
                    'feedback_left' => $assessment2->feedback_left,
                    'feedback_type' => $assessment2->feedback_type,
                    'course' => [
                        'id' => $assessment2->course->id,
                    ],
                    'staff' => [
                        'id' => $assessment2->staff->id,
                    ],
                ],
            ],
        ]);
    }
}
