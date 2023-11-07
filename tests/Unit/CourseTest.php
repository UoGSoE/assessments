<?php

namespace Tests\Unit;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_get_the_level_from_a_given_course(): void
    {
        $course = Course::factory()->create(['code' => 'ENG1234']);
        $this->assertEquals(1, $course->level);

        $course = Course::factory()->create(['code' => 'ENG3234']);
        $this->assertEquals(3, $course->level);

        $course = Course::factory()->create(['code' => 'ENG5234']);
        $this->assertEquals(5, $course->level);

        $course = Course::factory()->create(['code' => 'BLAHBLAH']);
        $this->assertEquals('Unknown', $course->level);
    }
}
