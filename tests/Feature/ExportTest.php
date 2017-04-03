<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Spreadsheet\Spreadsheet;

class ExportTest extends TestCase
{
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
}
