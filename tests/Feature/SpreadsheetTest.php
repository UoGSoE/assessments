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

class SpreadsheetTest extends TestCase
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

    /** @test */
    public function can_export_assessments_as_a_spreadsheet()
    {
        $admin = $this->createAdmin();
        $assessment1 = $this->createAssessment();
        $assessment2 = $this->createAssessment();
        $feedback = $this->createFeedback(['assessment_id' => $assessment1->id, 'course_id' => $assessment1->course_id]);
        $feedback = $this->createFeedback(['assessment_id' => $assessment1->id, 'course_id' => $assessment1->course_id]);
        $feedback = $this->createFeedback(['assessment_id' => $assessment2->id, 'course_id' => $assessment1->course_id]);

        $response = $this->actingAs($admin)->get(route('export.assessments'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="assessments.xlsx"');
        $file = $response->getFile();
        $data = (new Spreadsheet)->import($file->getPathname());
        $headings = array_shift($data);
        $row1 = array_shift($data);
        $row2 = array_shift($data);
        $this->assertEquals($assessment1->course->code, $row1[0]);
        $this->assertEquals($assessment1->totalNegativeFeedbacks(), $row1[5]);
        $this->assertEquals($assessment2->course->code, $row2[0]);
        $this->assertEquals($assessment2->totalNegativeFeedbacks(), $row2[5]);
    }

    /** @test */
    public function can_export_staff_report_as_a_spreadsheet()
    {
        // create staff with specific surnames as the spreadsheet is ordered by surname
        // otherwise checking the data in specific rows is a bit tricky
        $admin = $this->createAdmin(['surname' => 'zzz']);
        $staff1 = $this->createStaff(['surname' => 'bbb']);
        $staff2 = $this->createStaff(['surname' => 'ccc']);
        $assessment1 = $this->createAssessment(['staff_id' => $staff1->id]);
        $assessment2 = $this->createAssessment(['staff_id' => $staff2->id]);
        $feedback = $this->createFeedback(['assessment_id' => $assessment1->id, 'course_id' => $assessment1->course_id]);
        $feedback = $this->createFeedback(['assessment_id' => $assessment1->id, 'course_id' => $assessment1->course_id]);
        $feedback = $this->createFeedback(['assessment_id' => $assessment2->id, 'course_id' => $assessment1->course_id]);

        $response = $this->actingAs($admin)->get(route('export.staff'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="staff.xlsx"');
        $file = $response->getFile();
        $data = (new Spreadsheet)->import($file->getPathname());
        $headers = array_shift($data);
        $row1 = array_shift($data);
        $row2 = array_shift($data);
        $this->assertEquals($assessment1->staff->fullName(), $row1[0]);
        $this->assertEquals($assessment1->staff->numberOfAssessments(), $row1[1]);
        $this->assertEquals($assessment1->staff->totalStudentFeedbacks(), $row1[2]);
        $this->assertEquals($assessment1->staff->numberOfMissedDeadlines(), $row1[3]);
        $this->assertEquals($assessment2->staff->fullName(), $row2[0]);
        $this->assertEquals($assessment2->staff->numberOfAssessments(), $row2[1]);
        $this->assertEquals($assessment2->staff->totalStudentFeedbacks(), $row2[2]);
        $this->assertEquals($assessment2->staff->numberOfMissedDeadlines(), $row2[3]);
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
                    'HAPPYEASTER',
                ],
                [
                    Carbon::now()->addDays(3)->format('l, F d, Y'),
                    '',
                    'TEST9999',
                    'Test Course 2',
                    'Homework',
                    $this->staff2->fullName(),
                    $this->staff2->username,
                    'CADBURYS'
                ],
            ];
        }
        return $spreadsheet->generate($data);
    }
}
