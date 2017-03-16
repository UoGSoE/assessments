<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class AdminTest extends TestCase
{
    /** @test */
    public function admin_can_see_all_assessments()
    {
        $admin = $this->createAdmin();
        $assessment1 = $this->createAssessment();
        $assessment2 = $this->createAssessment();
        $assessment3 = $this->createAssessment(['feedback_left' => Carbon::now()->subDays(3)]);

        $response = $this->actingAs($admin)->get(route('report.assessments'));

        $response->assertStatus(200);
        $response->assertSee($assessment1->course->code);
        $response->assertSee($assessment2->course->code);
        $response->assertSee($assessment3->course->code);
        $response->assertSee($assessment3->feedback_left->format('Y-m-d'));
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

    /** @test */
    public function admin_can_see_all_feedbacks_left_by_a_given_student()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedbacks = factory(\App\AssessmentFeedback::class, 30)->create(['assessment_id' => $assessment->id, 'course_id' => $course->id, 'user_id' => $student->id]);

        $response = $this->actingAs($admin)->get(route('student.show', $student->id));

        $response->assertStatus(200);
        $response->assertSee($student->fullName());
        $response->assertSee('Feedbacks Left');
        foreach ($feedbacks as $feedback) {
            $response->assertSee($feedback->course->code);
        }
    }

    /** @test */
    public function admin_can_see_the_details_for_a_course()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $students = factory(\App\User::class, 5)->states('student')->create();
        $course->students()->sync($students->pluck('id'));
        $assessments = factory(\App\Assessment::class, 5)->create(['course_id' => $course->id]);

        $response = $this->actingAs($admin)->get(route('course.show', $course->id));

        $response->assertStatus(200);
        foreach ($assessments as $assessment) {
            $response->assertSee($assessment->title);
        }
        foreach ($students as $student) {
            $response->assertSee($student->fullName());
        }
    }

    /** @test */
    public function admin_can_see_the_details_for_an_assessment()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $course->students()->sync([$student->id]);
        $assessment = $this->createAssessment(['course_id' => $course->id, 'deadline' => Carbon::now()->subWeeks(4), 'comment' => 'HAPPYDAYS']);
        $student->recordFeedback($assessment);

        $response = $this->actingAs($admin)->get(route('assessment.show', $assessment->id));

        $response->assertStatus(200);
        $response->assertSee($course->code);
        $response->assertSee($assessment->deadline->format('d/m/Y H:i'));
        $response->assertSee($assessment->feedback_due->format('d/m/Y'));
        $response->assertSee($student->fullName());
        $response->assertSee($assessment->feedbacks()->first()->created_at->format('d/m/Y H:i'));
        $response->assertSee($assessment->comment);
    }
}
