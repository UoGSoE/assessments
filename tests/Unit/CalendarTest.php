<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Calendar\Calendar;

class CalendarTest extends TestCase
{
    /** @test */
    public function can_add_an_event_to_a_calendar()
    {
        $cal = resolve(Calendar::class);
    }
}
