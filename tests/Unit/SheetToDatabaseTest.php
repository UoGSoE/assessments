<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Spreadsheet\SheetToDatabase;
use App\Assessment;
use Carbon\Carbon;

class SheetToDatabaseTest extends TestCase
{
    /** @test */
    public function can_convert_a_valid_row_to_an_assessment()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();

        $assessment = $convertor->rowToAssessment($row);

        $this->assertEquals(1, Assessment::count());
        $this->assertCount(0, $convertor->errors->all());
        $this->assertEquals($row[0]->format('d/m/Y'), $assessment->deadline->format('d/m/Y'));
        $this->assertEquals($row[1], $assessment->deadline->format('H:i'));
        $this->assertEquals($row[2], $assessment->course->code);
        $this->assertEquals($row[4], $assessment->type);
    }

    /** @test */
    public function a_row_with_no_time_specified_defaults_to_4pm()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[1] = null;

        $assessment = $convertor->rowToAssessment($row);

        $this->assertEquals('16:00', $assessment->deadline->format('H:i'));
    }

    /** @test */
    public function a_row_with_invalid_staff_guid_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[6] = 'INVALIDUSERNAME';

        $convertor->rowToAssessment($row);

        $this->assertEquals(0, Assessment::count());
        $this->assertCount(1, $convertor->errors->all());
        $this->assertEquals('Row 1: Invalid GUID', $convertor->errors->first());
    }

    /** @test */
    public function a_row_with_invalid_date_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[0] = 'NOTADATE';

        $convertor->rowToAssessment($row);

        $this->assertEquals(0, Assessment::count());
        $this->assertCount(1, $convertor->errors->all());
        $this->assertEquals('Row 1: Invalid Date', $convertor->errors->first());
    }

    /** @test */
    public function a_row_with_a_date_string_rather_than_a_datetime_object_is_correctly_parsed()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $date = Carbon::now()->addDays(3);
        $row[0] = $date->format('l, F d, Y');

        $convertor->rowToAssessment($row);

        $this->assertEquals(1, Assessment::count());
        $this->assertEquals($date->format('d/m/Y'), Assessment::first()->deadline->format('d/m/Y'));
    }

    /** @test */
    public function when_importing_rows_those_with_a_date_in_the_past_are_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row1 = $this->getRowData();
        $row2 = $this->getRowData();
        $row2[0] = Carbon::now()->subWeeks(3);

        $assessments = $convertor->rowsToAssessments([$row1, $row2]);

        $this->assertEquals(1, Assessment::count());
    }

    protected function getRowData($attribs = [])
    {
        $staff = $this->createStaff();
        return array_merge([
            Carbon::now()->addWeeks(5),
            '15:00',
            'TEST1234',
            'Some course or other',
            'Homework',
            $staff->fullName(),
            $staff->username,
        ], $attribs);
    }
}
