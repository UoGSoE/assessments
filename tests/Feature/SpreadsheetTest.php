<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Assessment;
use App\Course;
use App\Spreadsheet\Spreadsheet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class SpreadsheetTest extends TestCase
{
    /** not being run while work out dates in spout/excel ..... */
    public function importing_the_assessments_spreadsheet_works_correctly()
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
        $this->assertDatabaseHas('courses', ['code' => 'ENG3075']);
        $this->assertDatabaseHas('courses', ['code' => 'ENG3080']);
        $this->assertCount(2, Assessment::all());
        $assessment = Assessment::first();
        $this->assertEquals('TEST1234', $assessment->course->code);
        $this->assertEquals(Carbon::now()->addDays(2)->format('d/m/Y'), $assessment->deadline->format('d/m/Y'));
    }

    /**
      This test is commented out as I can't figure out how to create a spreadsheet with
      a date that Spout/Excel is happy with... :-/
    */
    // public function test_importing_invalid_course_data_produces_session_errors()
    // {
    //     $this->staff1 = $this->createStaff();
    //     $this->staff2 = $this->createStaff();
    //     $admin = $this->createAdmin();
    //     $spreadsheet = $this->createSpreadsheet([
    //         [
    //             "2017",
    //             "S2",
    //             "ENG",
    //             "999999999",
    //             "UG",
    //             "Computational Fluid Dynamics 4",
    //             "ENG4037 Computational Fluid Dynamics 4",
    //             "A",
    //             "30",
    //             "2105713",
    //             "Busse",
    //             "Angela",
    //             "Angela.Busse@glasgow.ac.uk",
    //             "CFD project Moodle",
    //             \DateTime::createFromFormat('d/m/Y', "09/04/2017"),
    //             "30/04/2017",
    //             "YES",
    //             "Written on submitted work",
    //         ]
    //     ]);

    //     $file = new \Illuminate\Http\UploadedFile($spreadsheet, 'coursework.xlsx', 'application/octet-stream', filesize($spreadsheet), UPLOAD_ERR_OK, true);
    //     $response = $this->actingAs($admin)
    //                     ->call('POST', route('coursework.update'), [], [], ['sheet' => $file]);

    //     $response->assertStatus(302);
    //     $response->assertSessionHasErrors(['errors']);
    //     $this->assertEquals(0, Assessment::count());
    // }

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
        $response->assertHeader('content-disposition', 'attachment; filename=assessments.xlsx');
        $file = $response->getFile();
        $data = (new Spreadsheet)->import($file->getPathname());
        $headings = array_shift($data);
        $row1 = array_shift($data);
        $row2 = array_shift($data);
        $this->assertEquals($assessment1->course->code, $row1[0]);
        $this->assertEquals($assessment1->totalNegativeFeedbacks(), $row1[9]);
        $this->assertEquals($assessment2->course->code, $row2[0]);
        $this->assertEquals($assessment2->totalNegativeFeedbacks(), $row2[9]);
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
        $response->assertHeader('content-disposition', 'attachment; filename=staff.xlsx');
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

    /** @test */
    public function can_import_courses()
    {
        $admin = $this->createAdmin();
        $spreadsheet = $this->createCoursesSpreadsheet();

        $file = new \Illuminate\Http\UploadedFile($spreadsheet, 'courses.xlsx', 'application/octet-stream', filesize($spreadsheet), UPLOAD_ERR_OK, true);
        $response = $this->actingAs($admin)
                        ->call('POST', route('course.import.save'), [], [], ['sheet' => $file]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('courses', [
            'title' => 'Aero Engineering',
            'code' => 'ENG1000',
            'discipline' => 'Aero',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('courses', [
            'title' => 'Bio Engineering',
            'code' => 'ENG2000',
            'discipline' => 'Bio',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('courses', [
            'title' => 'Chemical Engineering',
            'code' => 'ENG3000',
            'discipline' => 'Chem',
            'is_active' => 0,
        ]);
    }

    /** @test */
    public function can_import_student_course_allocations()
    {
        $admin = $this->createAdmin();
        $course1 = $this->createCourse(['code' => 'ENG1000']);
        $course2 = $this->createCourse(['code' => 'ENG9999']);
        $spreadsheet = $this->createStudentsAllocationsSpreadsheet();

        $file = new \Illuminate\Http\UploadedFile($spreadsheet, 'courses.xlsx', 'application/octet-stream', filesize($spreadsheet), UPLOAD_ERR_OK, true);
        $response = $this->actingAs($admin)
                        ->call('POST', route('course.students.import.save'), [], [], ['sheet' => $file]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('users', [
            'forenames' => 'John',
            'surname' => 'Smith',
            'email' => '123456789S@student.gla.ac.uk',
            'username' => '123456789S',
        ]);
        $this->assertDatabaseHas('users', [
            'forenames' => 'Jane',
            'surname' => 'Doe',
            'email' => '99999999D@student.gla.ac.uk',
            'username' => '99999999D'
        ]);

        $this->assertCount(2, $course1->students);
        $this->assertCount(1, $course2->students);
    }

    /** @test */
    public function can_import_staff_course_allocations()
    {
        $admin = $this->createAdmin();
        $course1 = $this->createCourse(['code' => 'ENG1000']);
        $course2 = $this->createCourse(['code' => 'ENG9999']);
        $spreadsheet = $this->createStaffAllocationsSpreadsheet();

        $file = new \Illuminate\Http\UploadedFile($spreadsheet, 'courses.xlsx', 'application/octet-stream', filesize($spreadsheet), UPLOAD_ERR_OK, true);
        $response = $this->actingAs($admin)
                        ->call('POST', route('course.staff.import.save'), [], [], ['sheet' => $file]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('users', [
            'forenames' => 'John',
            'surname' => 'Smith',
            'email' => 'johnsmith@example.com',
            'username' => 'jsmi2x',
        ]);
        $this->assertDatabaseHas('users', [
            'forenames' => 'Jane',
            'surname' => 'Doe',
            'email' => 'janedoe@example.com',
            'username' => 'jdoi2x'
        ]);

        $this->assertCount(2, $course1->staff);
        $this->assertCount(1, $course2->staff);
    }

    public function createCoursesSpreadsheet($data = null)
    {
        $spreadsheet = new Spreadsheet;
        if (!$data) {
            $data = [
                [
                    'Aero Engineering',     //Course title
                    'ENG1000',              //Course code
                    'Aero',                 //Course discipline
                    'Yes'                   //Course active? (Yes/No)
                ],
                [
                    'Bio Engineering',      //Course title
                    'ENG2000',              //Course code
                    'Bio',                  //Course discipline
                    'Yes'                   //Course active? (Yes/No)
                ],
                [
                    'Chemical Engineering', //Course title
                    'ENG3000',              //Course code
                    'Chem',                 //Course discipline
                    'No'                    //Course active? (Yes/No)
                ],
            ];
        }
        return $spreadsheet->generate($data);
    }

    public function createStudentsAllocationsSpreadsheet($data = null)
    {
        $spreadsheet = new Spreadsheet;
        if (!$data) {
            $data = [
                [
                    'John',                     //Student forenames
                    'Smith',                    //Student surname
                    '123456789S',               //Student GUID
                    'ENG1000'                   //Course code
                ],
                [
                    'John',                     //Student forenames
                    'Smith',                    //Student surname
                    '123456789S',               //Student GUID
                    'ENG9999'                   //Course code
                ],
                [
                    'Jane',                     //Student forenames
                    'Doe',                    //Student surname
                    '99999999D',               //Student GUID
                    'ENG1000'                   //Course code
                ],
            ];
        }
        return $spreadsheet->generate($data);
    }

    public function createStaffAllocationsSpreadsheet($data = null)
    {
        $spreadsheet = new Spreadsheet;
        if (!$data) {
            $data = [
                [
                    'John',                     //Staff forenames
                    'Smith',                    //Staff surname
                    'jsmi2x',               //Staff GUID
                    'johnsmith@example.com',     //Staff email
                    'ENG1000'                   //Course code
                ],
                [
                    'John',                     //Staff forenames
                    'Smith',                    //Staff surname
                    'jsmi2x',               //Staff GUID
                    'johnsmith@example.com',     //Staff email
                    'ENG9999'                   //Course code
                ],
                [
                    'Jane',                     //Staff forenames
                    'Doe',                    //Staff surname
                    'jdoi2x',               //Staff GUID
                    'janedoe@example.com',     //Staff email
                    'ENG1000'                   //Course code
                ],
            ];
        }
        return $spreadsheet->generate($data);
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
