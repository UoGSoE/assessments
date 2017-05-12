<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

class StudentAssessmentTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function student_can_see_and_interact_with_their_assessments()
    {
        $this->browse(function ($browser) {
            $student = $this->createStudent();
            $course1 = $this->createCourse();
            $course1->students()->sync([$student->id]);
            $course2 = $this->createCourse();
            $course2->students()->sync([$student->id]);
            $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE1', 'deadline' => Carbon::parse('First Monday of Last Month')]);
            $assessment2 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE2', 'deadline' => Carbon::now()->startOfWeek()->addDays(3)]);
            $assessment3 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE3', 'deadline' => Carbon::now()->startOfWeek()->addDays(4)]);
            $assessment4 = $this->createAssessment(['type' => 'SHOULDNTSHOWUP']);

            $browser->loginAs($student)
                    ->visit('/home')
                    ->assertSee('Your Assessments')
                    ->assertSee($assessment2->title)
                    ->assertSee($assessment3->title)
                    ->assertDontSee($assessment4->title)
                    ->press('.fc-prev-button')   // ie, go back one month
                    ->assertSee($assessment1->title)
                    ->clickLink($assessment1->title)
                    ->assertSee('Assessment Details')
                    ->assertSee($assessment1->course->code)
                    ->assertSee($assessment1->course->title)
                    ->assertSee($assessment1->type)
                    ->press('Report assessment feedback is overdue')
                    ->assertSee('Feedback recorded')
                    ->clickLink($assessment2->title)
                    ->assertDontSee('Report assessment feedback');
            $this->assertDatabaseHas('assessment_feedbacks', [
                'course_id' => $course1->id,
                'student_id' => $student->id,
                'assessment_id' => $assessment1->id
            ]);
        });
    }
}
