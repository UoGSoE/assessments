<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

class AdminTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function admin_can_view_admin_stuff()
    {
        $this->browse(function ($browser) {
            $admin = $this->createAdmin();
            $student = $this->createStudent();
            $course1 = $this->createCourse();
            $course1->students()->sync([$student->id]);
            $course2 = $this->createCourse();
            $course2->students()->sync([$student->id]);
            $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE1', 'deadline' => Carbon::now()->subWeeks(4)]);
            $assessment2 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE2', 'deadline' => Carbon::now()->subWeeks(4)]);
            $assessment3 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE3', 'deadline' => Carbon::now()->subWeeks(5)]);
            $assessment4 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE4', 'deadline' => Carbon::now()->addDays(5)]);
            $student->recordFeedback($assessment1);
            $browser->loginAs($admin)
                    ->visit('/')
                    ->assertSee('Admin')
                    ->clickLink('Admin')
                    ->assertSee('All Assessments')
                    ->assertSee($assessment1->course->code)
                    ->clickLink($assessment1->course->code)
                    ->assertSee('Assessment Details')
                    ->assertSee('Feedbacks Left')
                    ->assertSee($student->fullName())
                    ->clickLink($student->fullName())
                    ->assertSee('Student Details')
                    ->assertSee($student->fullName())
                    ->assertSee('Assessments for')
                    ->assertSeeIn('.fc-title', $assessment4->title)
                    ->assertSee('Feedbacks Left')
                    ->assertSee($assessment1->title)
                    ->clickLink($assessment1->title)
                    ->assertSee('Assessment Details')
                    ->assertSee($assessment1->course->code)
                    ->clickLink($assessment1->course->code)
                    ->assertSee('Course Details')
                    ->assertSee($course1->title)
                    ->assertSee($course1->students()->first()->fullName());
        });
    }
}
