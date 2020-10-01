<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Assessment;

class FeedbackTypeTest extends TestCase
{
    /** @test */
    public function can_get_a_list_of_unique_feedback_types()
    {
        $assessments = Assessment::factory()->count(3)->create();

        $types = Assessment::getFeedbackTypes();

        $this->assertEquals(3, $types->count());
        $this->assertEquals(3, $types->unique()->count());
    }
}
