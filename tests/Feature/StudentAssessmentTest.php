<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

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

    /** @test */
    // public function student_cant_see_an_assessment_that_isnt_for_one_of_their_course()
    // {
    //     $student = $this->createStudent();
    //     $assessment1 = $this->createAssessment();

    //     $response = $this->actingAs($student)->get(route('assessment.show', $assessment1->id));

    //     $response->assertStatus(302);
    //     $response->assertRedirect('/home');
    // }

    /** @test */
    public function student_can_see_an_assessment_which_is_for_one_of_their_courses()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id]);

        $response = $this->actingAs($student)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee($assessment->course->title);
        $response->assertSee($assessment->deadline->format('d/m/Y H:i'));
        $response->assertSee($assessment->staff->fullName());
        $this->assertMatchesRegularExpression('/Feedback Completed.+No/s', $response->content());
    }

    /** @test */
    public function student_can_see_feedback_completed_date_if_has_been_set()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'feedback_left' => Carbon::now()]);
        $feedbackLeftString = $assessment->feedback_left->format('d/m/Y');

        $response = $this->actingAs($student)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $this->assertMatchesRegularExpression('#Feedback Completed.+'.$feedbackLeftString.'#s', $response->content());
    }
}
