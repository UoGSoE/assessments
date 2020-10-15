<?php

// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Assessment;
use App\Spreadsheet\SheetToDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesApplication;
use Tests\TestCase;

class SheetToDatabaseTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp() : void
    {
        parent::setUp();

        // Enable foreign key support for SQLITE databases
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::statement(DB::raw('PRAGMA foreign_keys=on'));
        }

        $this->createStaff([
            'email' => 'angela.busse@glasgow.ac.uk',
        ]);
        $this->createCourse(['code' => 'ENG4037']);
    }

    /** @test */
    public function can_convert_a_valid_row_to_an_assessment()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();

        $assessment = $convertor->rowToAssessment($row);

        $this->assertCount(0, $convertor->errors->all());
        $this->assertEquals(1, Assessment::count());
        $this->assertEquals(now()->format('d/m/Y 16:00'), $assessment->deadline->format('d/m/Y H:i'));
        $this->assertEquals($row[0], $assessment->course->code);
        $this->assertEquals($row[2], $assessment->type);
        $this->assertEquals($row[3], $assessment->feedback_type);
    }

    /** @test */
    public function assessment_date_can_also_be_datetime_objects()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[6] = now()->addWeeks(2);

        $assessment = $convertor->rowToAssessment($row);

        $this->assertEquals(now()->addWeeks(2)->format('d/m/Y 16:00'), $assessment->deadline->format('d/m/Y H:i'));
    }

    /** @test */
    public function a_row_with_invalid_staff_email_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[5] = 'INVALIDEMAIL';

        $assessment = $convertor->rowToAssessment($row);

        $this->assertEquals(0, Assessment::count());
        $this->assertCount(1, $convertor->errors->all());
        $this->assertEquals('Row 1: Unknown staff email : INVALIDEMAIL', $convertor->errors->first());
    }

    /** @test */
    public function a_row_with_invalid_date_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[6] = 'NOTADATE';

        $assessment = $convertor->rowToAssessment($row);

        $this->assertEquals(0, Assessment::count());
        $this->assertCount(1, $convertor->errors->all());
        $this->assertEquals('Row 1: Could not parse date : NOTADATE', $convertor->errors->first());
    }

    /** @test */
    public function a_row_with_a_date_in_the_past_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[6] = now()->subWeeks(10)->format('d/m/Y H:i');

        $assessment = $convertor->rowToAssessment($row);

        $this->assertEquals(0, Assessment::count());
        $this->assertCount(1, $convertor->errors->all());
        $this->assertMatchesRegularExpression('/Row 1: Assessment date is in the past/', $convertor->errors->first());
    }

    /** @test */
    public function a_row_without_comments_gets_an_empty_string_as_the_comment_db_column()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        unset($row[10]);

        $assessment = $convertor->rowToAssessment($row);

        $this->assertEquals(1, Assessment::count());
        $this->assertCount(0, $convertor->errors->all());
        $this->assertEquals('', $assessment->comment);
    }

    protected function getRowData($attribs = [])
    {
        $staff = $this->createStaff();

        return array_merge([
            'ENG4037',
            '4',
            'moodle quiz',
            'moodle - graded',
            'Angela Busse',
            'angela.busse@glasgow.ac.uk',
            now()->format('d/m/Y H:i'),
            now()->addWeeks(2)->format('d/m/Y H:i'),
            'No',
            '0',
            'my comments',
        ], $attribs);
    }
}
