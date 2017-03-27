<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Spreadsheet\Spreadsheet;
use Carbon\Carbon;
use App\Course;
use App\Assessment;

class SpreadsheetImportTest extends TestCase
{
    /** @test */
    public function test_importing_the_assessments_spreadsheet_works_correctly()
    {
        $this->staff1 = $this->createStaff();
        $this->staff2 = $this->createStaff();
        $admin = $this->createAdmin();
        $spreadsheet = $this->createSpreadsheet();

        $file = new \Illuminate\Http\UploadedFile($spreadsheet, 'coursework.xlsx', 'application/octet-stream', filesize($spreadsheet), UPLOAD_ERR_OK, true);
        $response = $this->actingAs($admin)
                        ->call('POST', route('coursework.update'), [], [], ['sheet' => $file]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('courses', ['code' => 'TEST1234']);
        $this->assertDatabaseHas('courses', ['code' => 'TEST9999']);
        $this->assertCount(2, Assessment::all());
        $assessment = Assessment::first();
        $this->assertEquals('TEST1234', $assessment->course->code);
        $this->assertEquals(Carbon::now()->addDays(2)->format('d/m/Y'), $assessment->deadline->format('d/m/Y'));
    }

    /** @test */
    public function test_importing_garbage_data_produces_session_errors()
    {
        $this->staff1 = $this->createStaff();
        $this->staff2 = $this->createStaff();
        $admin = $this->createAdmin();
        $spreadsheet = $this->createSpreadsheet([['BLAH']]);

        $file = new \Illuminate\Http\UploadedFile($spreadsheet, 'coursework.xlsx', 'application/octet-stream', filesize($spreadsheet), UPLOAD_ERR_OK, true);
        $response = $this->actingAs($admin)
                        ->call('POST', route('coursework.update'), [], [], ['sheet' => $file]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['errors']);
        $this->assertEquals(0, Assessment::count());
    }

    protected function createSpreadsheet($data = null)
    {
        $spreadsheet = new Spreadsheet;
        if (!$data) {
            $data = [
                [
                    Carbon::now()->addDays(2)->format('l, F d, Y'),
                    '',
                    'TEST1234',
                    'Test Course 1',
                    'Homework',
                    $this->staff1->fullName(),
                    $this->staff1->username,
                ],
                [
                    Carbon::now()->addDays(3)->format('l, F d, Y'),
                    '',
                    'TEST9999',
                    'Test Course 2',
                    'Homework',
                    $this->staff2->fullName(),
                    $this->staff2->username,
                ],
            ];
        }
        return $spreadsheet->generate($data);
    }
}
