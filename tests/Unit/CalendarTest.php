<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Calendar\Calendar;
use Storage;

class CalendarTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_render_a_calendar_to_a_string()
    {
        $cal = resolve(Calendar::class);

        $contents = $cal->render();

        $this->assertContains('BEGIN:VCALENDAR', $contents);
        $this->assertContains('PRODID', $contents);
        $this->assertContains('X-PUBLISHED-TTL', $contents);
        $this->assertContains('END:VCALENDAR', $contents);
    }

    /** @test */
    public function can_add_an_assessment_to_a_calendar()
    {
        $cal = resolve(Calendar::class);
        $assessment = $this->createAssessment(['comment' => 'A COMMENT']);

        $cal->addAssessment($assessment);
        $contents = $cal->render();

        $this->assertContains($assessment->deadline->format('Ymd\THis\Z'), $contents);
        $this->assertContains($assessment->title, $contents);
        $this->assertContains($assessment->comment, $contents);
    }

    /** @test */
    public function can_add_multiple_assessments_to_a_calendar()
    {
        $cal = resolve(Calendar::class);
        $assessment1 = $this->createAssessment();
        $assessment2 = $this->createAssessment();

        $cal->addAssessments([$assessment1, $assessment2]);
        $contents = $cal->render();

        $this->assertContains($assessment1->deadline->format('Ymd\THis\Z'), $contents);
        $this->assertContains($assessment1->title, $contents);
        $this->assertContains($assessment2->deadline->format('Ymd\THis\Z'), $contents);
        $this->assertContains($assessment2->title, $contents);
    }

    /** @test */
    public function can_save_a_calender_to_disk()
    {
        $cal = resolve(Calendar::class);

        $cal->save('__test.ics');

        $contents = Storage::disk('calendars')->get('__test.ics');
        Storage::disk('calendars')->delete('__test.ics');
        $this->assertContains('BEGIN:VCALENDAR', $contents);
        $this->assertContains('PRODID', $contents);
        $this->assertContains('X-PUBLISHED-TTL', $contents);
        $this->assertContains('END:VCALENDAR', $contents);
    }
}
