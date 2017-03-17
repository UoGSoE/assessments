<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

class StaffTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function staff_can_mark_assessment_feedback_as_having_been_given()
    {
        $this->browse(function ($browser) {
            $now = Carbon::now();
            $course = $this->createCourse();
            $staff = $this->createStaff();
            $course->staff()->sync([$staff->id]);
            $assessment = $this->createAssessment(['course_id' => $course->id, 'user_id' => $staff->id, 'deadline' => Carbon::now()->subWeeks(4)]);
            $browser->loginAs($staff)
                    ->visit("/assessment/{$assessment->id}")
                    ->click('#datepicker')
                    ->click('[data-day="1"]')
                    ->press('Save')
                    ->assertSee('Feedback marked as complete')
                    ->assertSee(Carbon::now()->startOfMonth()->format('d/m/Y'))
                    ->assertDontSee('Save');
        });
        
    }
}
