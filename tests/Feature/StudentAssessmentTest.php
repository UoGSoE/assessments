<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StudentAssessmentTest extends TestCase
{
    /** @test */
    public function student_can_see_all_applicable_assessments()
    {
        $student = $this->createStudent();
        $course1 = $this->createCourse();
        $course1->students()->sync([$student->id]);
        $course2 = $this->createCourse();
        $course2->students()->sync([$student->id]);
        $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE1']);
        $assessment2 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE2']);
        $assessment3 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE3']);
        $assessment4 = $this->createAssessment(['type' => 'SHOULDNTSHOWUP']);

        $response = $this->actingAs($student)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertSee($assessment1->type);
        $response->assertSee($assessment2->type);
        $response->assertSee($assessment3->type);
        $response->assertDontSee($assessment4->type);
        $response->assertSee($assessment1->deadline->toIso8601String());
        $response->assertSee($assessment2->deadline->toIso8601String());
        $response->assertSee($assessment3->deadline->toIso8601String());
    }
}
