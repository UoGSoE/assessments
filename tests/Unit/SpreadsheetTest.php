<?php

// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Spreadsheet\Spreadsheet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpreadsheetTest extends TestCase
{
    /** @test */
    public function importing_a_spreadsheet_returns_correct_data()
    {
        $data = (new Spreadsheet)->import('./tests/data/assessments_2019_09_18.xlsx');

        $row = $data[1];
        $this->assertEquals('01/08/2017 16:00', $row[6]);
        $this->assertEquals('nritchie@example.org', $row[5]);
        $this->assertEquals('ENG1002', $row[0]);
    }

    /** @test */
    public function generating_a_sheet_from_an_array_produces_correct_results()
    {
        $spreadsheet = new Spreadsheet;
        $data = [
            0 => [
                'Jimmy', '01/02/2015', '1234567',
            ],
            1 => [
                'Fred', '02/03/2016', '9292929',
            ],
        ];

        $filename = $spreadsheet->generate($data);
        $newData = $spreadsheet->import($filename);

        $this->assertEquals('Jimmy', $newData[0][0]);
        $this->assertEquals('01/02/2015', $newData[0][1]);
        $this->assertEquals('1234567', $newData[0][2]);
        $this->assertEquals('Fred', $newData[1][0]);
        $this->assertEquals('02/03/2016', $newData[1][1]);
        $this->assertEquals('9292929', $newData[1][2]);
    }
}
