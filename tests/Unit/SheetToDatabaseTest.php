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
    protected function setUp() : void
    {
        parent::setUp();
        $this->setupDatabase();
        $this->createStaff([
            'email' => "angela.busse@glasgow.ac.uk",
        ]);
        $this->createCourse(['code' => 'ENG4037']);
    }


    /** @test */
    public function can_convert_a_valid_row_to_an_assessment()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();

        $assessment = $convertor->spreadsheetDataToAssessment(
            $convertor->extractAssessment(1, $row),
            $row);

        $this->assertEquals(1, Assessment::count());
        $this->assertCount(0, $convertor->errors->all());
        $this->assertEquals($row[15]->format('d/m/Y'), $assessment->deadline->format('d/m/Y'));
        $this->assertEquals($row[2] . $row[3], $assessment->course->code);
        $this->assertEquals($row[13] . ' / ' . $row[14], $assessment->type);
        $this->assertEquals($row[18] . '. Graded', $assessment->feedback_type);
    }

    /** @test */
    public function can_convert_a_valid_row_with_a_second_assessment()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();

        $assessment = $convertor->spreadsheetDataToAssessment(
            $convertor->extractAssessment(2, $row),
            $row);

        $this->assertEquals(1, Assessment::count());
        $this->assertCount(0, $convertor->errors->all());
        $this->assertEquals($row[21]->format('d/m/Y'), $assessment->deadline->format('d/m/Y'));
        $this->assertEquals($row[2] . $row[3], $assessment->course->code);
        $this->assertEquals($row[19] . ' / ' . $row[20], $assessment->type);
        $this->assertEquals($row[24] . '. Not Graded', $assessment->feedback_type);
    }

    /** @test */
    public function assessment_date_defaults_to_4pm()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[1] = null;

        $assessment = $convertor->spreadsheetDataToAssessment(
            $convertor->extractAssessment(1, $row),
            $row);

        $this->assertEquals('16:00', $assessment->deadline->format('H:i'));
    }

    /** @test */
    public function a_row_with_invalid_staff_email_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[12] = 'INVALIDEMAIL';

        $assessment = $convertor->spreadsheetDataToAssessment(
            $convertor->extractAssessment(1, $row),
            $row);

        $this->assertEquals(0, Assessment::count());
        $this->assertCount(1, $convertor->errors->all());
        $this->assertEquals('Row 1: Unknown staff email : invalidemail', $convertor->errors->first());
    }

    /** @test */
    public function a_row_with_invalid_date_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[15] = 'NOTADATE';

        $assessment = $convertor->spreadsheetDataToAssessment(
            $convertor->extractAssessment(1, $row),
            $row);

        $this->assertEquals(0, Assessment::count());
    }

    /** @test */
    public function a_row_with_a_date_in_the_past_is_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row = $this->getRowData();
        $row[15] = (new \DateTime)->sub(new \DateInterval('P10D'));

        $assessment = $convertor->spreadsheetDataToAssessment(
            $convertor->extractAssessment(1, $row),
            $row);

        $this->assertEquals(0, Assessment::count());
        $this->assertCount(1, $convertor->errors->all());
        $this->assertRegExp('/Row 1: Assessment date is in the past/', $convertor->errors->first());
    }

    /** @test */
    public function when_importing_rows_those_with_a_date_in_the_past_are_skipped()
    {
        $convertor = app(SheetToDatabase::class);
        $row1 = $this->getRowData();
        $row2 = $this->getRowData();
        $row2[13] = Carbon::now()->subWeeks(3);

        $assessments = $convertor->rowsToAssessments([$row1, $row2]);

        $this->assertEquals(1, Assessment::count());
    }

    protected function getRowData($attribs = [])
    {
        $staff = $this->createStaff();
        return array_merge([
            0 => "2017",
            1 => "S2",
            2 => "ENG",
            3 => "4037",
            4 => "UG",
            5 => "Computational Fluid Dynamics 4",
            6 => "ENG4037 Computational Fluid Dynamics 4",
            7 => "A",
            8 => "30",
            9 => "2105713",
            10 => "Busse",
            11 => "Angela",
            12 => "Angela.Busse@glasgow.ac.uk",
            13 => "CFD project",
            14 => "Moodle",
            15 => (new \DateTime)->add(new \DateInterval('P10D')),
            16 => new \DateTime,
            17 => "YES",
            18 => "Written on submitted work",
            19 => "Other project",
            20 => "Moodle",
            21 => (new \DateTime)->add(new \DateInterval('P10D')),
            22 => new \DateTime,
            23 => "NO",
            24 => "Written on submitted work",
        ], $attribs);
    }
}
