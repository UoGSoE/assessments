<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ImportCourseworkSpreadsheetTest extends TestCase
{
    /** @test */
    public function an_admin_can_import_the_coursework_sheet()
    {
        // $admin = $this->createAdmin();

        // $filename = './tests/data/coursework.xlsx';
        // $file = new \Illuminate\Http\UploadedFile($filename, 'coursework.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true);
        // $response = $this->actingAs($admin)
        //                 ->call('POST', route('coursework.update'), [], [], ['sheet' => $file]);

        // $response->assertStatus(302);
        // $response->assertSessionHas('success_message');
        // $eng4020 = Course::where('code', '=', 'ENG4020')->get();
        // $this->assertEquals(5, $eng4020->assessments()->count());
    }
}
