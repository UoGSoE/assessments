<?php

// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Calendar\Calendar;
use Storage;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    /** @test */
    public function can_render_a_calendar_to_a_string(): void
    {
        $cal = resolve(Calendar::class);

        $contents = $cal->render();

        $this->assertStringContainsString('BEGIN:VCALENDAR', $contents);
        $this->assertStringContainsString('PRODID', $contents);
        $this->assertStringContainsString('END:VCALENDAR', $contents);
    }

    /** @test */
    public function can_add_an_assessment_to_a_calendar(): void
    {
        $cal = resolve(Calendar::class);
        $assessment = $this->createAssessment(['comment' => 'A COMMENT']);

        $cal->addAssessment($assessment);
        $contents = $cal->render();

        $this->assertStringContainsString($assessment->deadline->format('Ymd\THis\Z'), $contents);
        $this->assertStringContainsString($assessment->title, $contents);
        $this->assertStringContainsString($assessment->comment, $contents);
    }

    /** @test */
    public function can_add_multiple_assessments_to_a_calendar(): void
    {
        $cal = resolve(Calendar::class);
        $assessment1 = $this->createAssessment();
        $assessment2 = $this->createAssessment();

        $cal->addAssessments([$assessment1, $assessment2]);
        $contents = $cal->render();

        $this->assertStringContainsString($assessment1->deadline->format('Ymd\THis\Z'), $contents);
        $this->assertStringContainsString($assessment1->title, $contents);
        $this->assertStringContainsString($assessment2->deadline->format('Ymd\THis\Z'), $contents);
        $this->assertStringContainsString($assessment2->title, $contents);
    }

    /** @test */
    public function can_save_a_calender_to_disk(): void
    {
        $cal = resolve(Calendar::class);

        $cal->save('__test.ics');

        $contents = Storage::disk('calendars')->get('__test.ics');
        Storage::disk('calendars')->delete('__test.ics');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $contents);
        $this->assertStringContainsString('PRODID', $contents);
        $this->assertStringContainsString('END:VCALENDAR', $contents);
    }
}
