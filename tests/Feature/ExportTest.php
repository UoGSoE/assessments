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
        $row1 = array_shift($data);
        $row2 = array_shift($data);
        $this->assertEquals($assessment1->course->code, $row1[0]);
        $this->assertEquals($assessment1->totalNegativeFeedbacks(), $row1[5]);
        $this->assertEquals($assessment2->course->code, $row2[0]);
        $this->assertEquals($assessment2->totalNegativeFeedbacks(), $row2[5]);
    }
}
