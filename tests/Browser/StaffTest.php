<?php

// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class StaffTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function staff_can_see_all_assessments()
    {
        $this->browse(function ($browser) {
            $now = Carbon::now();
            $course = $this->createCourse();
            $staff = $this->createStaff();
            $course->staff()->sync([$staff->id]);
            $assessment1 = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id, 'deadline' => Carbon::now()]);
            $assessment2 = $this->createAssessment(['deadline' => Carbon::now()]);

            $browser->loginAs($staff)
                    ->visit('/home')
                    ->assertSee('Your Assessments')
                    ->assertSee($assessment1->course->code)
                    ->assertSee($assessment2->course->code)
                    ->clickLink($assessment2->title)
                    ->assertSee($assessment2->type)
                    ->assertSee($assessment2->deadline->format('d/m/Y'))
                    ->visit('/home')
                    ->clickLink($assessment1->title)
                    ->assertSee('Assessment Details')
                    ->assertSee($assessment1->type)
                    ->assertSee($assessment1->deadline->format('d/m/Y'));
        });
    }

    /** @test */
    public function staff_can_see_assessments_where_feedback_is_coming_up()
    {
        $this->browse(function ($browser) {
            $now = Carbon::now();
            $course = $this->createCourse();
            $staff = $this->createStaff();
            $course->staff()->sync([$staff->id]);
            $assessment1 = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id, 'deadline' => Carbon::now()->subWeeks(3)->startOfWeek()]);

            $browser->loginAs($staff)
                    ->visit('/home')
                    ->assertSee('Your Assessments')
                    ->assertSee($assessment1->course->code);
        });
    }

    /** @test */
    public function staff_can_mark_assessment_feedback_as_having_been_given()
    {
        $this->browse(function ($browser) {
            $now = Carbon::now();
            $course = $this->createCourse();
            $staff = $this->createStaff();
            $course->staff()->sync([$staff->id]);
            $assessment = $this->createAssessment(['course_id' => $course->id, 'staff_id' => $staff->id, 'deadline' => Carbon::now()->subWeeks(4)]);

            /*
                By way of an explanation... The date field on the form has a JS datepicker
                attached, so this just clicks the date field, picks the first day available and
                saves it.  The first day on the datepicker *should* be the first day of the month
                 - hence the startofMonth() carbon call.
            */
            $browser->loginAs($staff)
                    ->visit("/assessment/{$assessment->id}")
                    ->click('#datepicker')
                    ->waitFor('.pika-lendar')
                    ->click('[data-day="1"]')
                    ->press('Save')
                    ->assertSee('Feedback marked as complete')
                    ->assertSee(Carbon::now()->startOfMonth()->format('d/m/Y'))
                    ->assertDontSee('Save');
        });
    }
}
