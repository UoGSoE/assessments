<?php

namespace Tests\Unit;

use App\Course;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_get_the_level_from_a_given_course()
    {
        $course = factory(Course::class)->create(['code' => 'ENG1234']);
        $this->assertEquals(1, $course->level);

        $course = factory(Course::class)->create(['code' => 'ENG3234']);
        $this->assertEquals(3, $course->level);

        $course = factory(Course::class)->create(['code' => 'ENG5234']);
        $this->assertEquals(5, $course->level);

        $course = factory(Course::class)->create(['code' => 'BLAHBLAH']);
        $this->assertEquals('Unknown', $course->level);
    }
}
