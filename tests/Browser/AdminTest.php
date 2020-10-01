<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;
use App\Spreadsheet\Spreadsheet;

class AdminTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function admin_can_view_admin_stuff()
    {
        $this->browse(function ($browser) {
            $admin = $this->createAdmin();
            $student = $this->createStudent();
            $course1 = $this->createCourse();
            $course1->students()->sync([$student->id]);
            $course2 = $this->createCourse();
            $course2->students()->sync([$student->id]);
            $assessment1 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE1', 'deadline' => Carbon::now()->startOfWeek()->subWeeks(4)]);
            $assessment2 = $this->createAssessment(['course_id' => $course1->id, 'type' => 'TYPE2', 'deadline' => Carbon::now()->startOfWeek()->subWeeks(4)]);
            $assessment3 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE3', 'deadline' => Carbon::now()->startOfWeek()->subWeeks(5)]);
            $assessment4 = $this->createAssessment(['course_id' => $course2->id, 'type' => 'TYPE4', 'deadline' => Carbon::now()->startOfWeek()->addDays(3)]);
            $student->recordFeedback($assessment1);
            $browser->loginAs($admin)
                    ->visit('/')
                    ->assertSee('Admin')
                    ->clickLink('Admin')
                    ->assertSee('Feedback Report')
                    ->assertSee($assessment1->course->code)
                    ->clickLink($assessment1->course->code)
                    ->assertSee('Assessment Details')
                    ->assertSee('Feedbacks Left')
                    ->assertSee($student->fullName())
                    ->clickLink($student->fullName())
                    ->assertSee('Student Details')
                    ->assertSee($student->fullName())
                    ->assertSee('Assessments for')
                    ->assertSee($assessment4->title)
                    ->assertSee('Feedbacks Left')
                    ->assertSee($assessment1->title)
                    ->clickLink($assessment1->title)
                    ->assertSee('Assessment Details')
                    ->assertSee($assessment1->course->code)
                    ->clickLink($assessment1->course->code)
                    ->assertSee('Course Details')
                    ->assertSee($course1->title)
                    ->assertSee($course1->students()->first()->fullName())
                    ->clickLink('Admin')
                    ->clickLink($assessment1->staff->fullName())
                    ->assertSee('Staff Details')
                    ->assertSee($assessment1->staff->email)
                    ->assertSee($assessment1->course->code);
        });
    }

    /** @test */
    public function admin_can_edit_an_assessment()
    {
        $this->browse(function ($browser) {
            $now = Carbon::now();
            $admin = $this->createAdmin();
            $staff = $this->createStaff();
            $student = $this->createStudent();
            $course = $this->createCourse();
            $course->students()->sync([$student->id]);
            $assessment = $this->createAssessment(['course_id' => $course->id, 'type' => 'TYPE1', 'deadline' => Carbon::now()->subWeeks(4)]);
            $student->recordFeedback($assessment);
            $browser->loginAs($admin)
                    ->visit("/assessment/{$assessment->id}")
                    ->click('#edit-assessment-button')
                    ->assertSee("Edit Assessment")
                    ->type('type', 'something')
                    ->select('staff_id', "$staff->id")
                    ->type('date', $now->format('d/m/Y'))
                    ->type('time', $now->format('H:i'))
                    ->type('feedback_type', 'HAPPYEASTER')
                    ->press('Update')
                    ->assertSee('Updated')
                    ->assertSee($staff->fullName())
                    ->assertSee($now->format('d/m/Y H:i'))
                    ->assertSee('HAPPYEASTER');
        });
    }

    /** @test */
    public function admin_can_create_a_new_assessment()
    {
        $this->browse(function ($browser) {
            $now = Carbon::now();
            $admin = $this->createAdmin();
            $staff = $this->createStaff();
            $course = $this->createCourse();
            $browser->loginAs($admin)
                    ->visit("/admin/report/feedback")
                    ->click('#add-assessment-button')
                    ->assertSee("New Assessment")
                    ->type('type', 'something')
                    ->select('staff_id', "$staff->id")
                    ->type('date', $now->format('d/m/Y'))
                    ->type('time', $now->format('H:i'))
                    ->type('comment', 'blah blah blah')
                    ->type('feedback_type', 'HAPPYEASTER')
                    ->select('course_id', "$course->id")
                    ->press('Create')
                    ->assertSee('Created')
                    ->assertSee($staff->fullName())
                    ->assertSee($now->format('d/m/Y H:i'))
                    ->assertSee($course->title)
                    ->assertSee('blah blah blah')
                    ->assertSee('HAPPYEASTER');
        });
    }

    /** @test */
    public function admin_can_delete_an_assessment()
    {
        $this->browse(function ($browser) {
            $now = Carbon::now();
            $admin = $this->createAdmin();
            $staff = $this->createStaff();
            $student = $this->createStudent();
            $course = $this->createCourse();
            $course->students()->sync([$student->id]);
            $assessment = $this->createAssessment(['course_id' => $course->id, 'type' => 'TYPE1', 'deadline' => Carbon::now()->subWeeks(4)]);
            $student->recordFeedback($assessment);
            $browser->loginAs($admin)
                    ->visit("/assessment/{$assessment->id}")
                    ->press("#delete-button")
                    ->waitFor('#pop-up')
                    ->assertSee('Do you really want to delete')
                    ->clickLink('No')
                    ->assertDontSee('Do you really want to delete')
                    ->assertSee($assessment->course->code)
                    ->press("#delete-button")
                    ->waitFor('#pop-up')
                    ->clickLink('Yes')
                    ->assertSee('Feedback Report')
                    ->assertSee('Assessment deleted');
        });
    }

    /** @test */
    public function admin_can_delete_all_old_data()
    {
        $this->browse(function ($browser) {
            $admin = $this->createAdmin();
            $assessments = \App\Assessment::factory()->count(5)->create();
            $firstAssessment = $assessments->first();
            $feedbacks = \App\AssessmentFeedback::factory()->count(5)->create();
            $browser->loginAs($admin)
                    ->visit("/admin/report/feedback")
                    ->assertSee($firstAssessment->course->code)
                    ->press('#delete-button')
                    ->waitFor('#pop-up')
                    ->assertSee('Do you really want to delete')
                    ->clickLink('No')
                    ->assertDontSee('Do you really want to delete')
                    ->assertSee($firstAssessment->course->code)
                    ->press("#delete-button")
                    ->waitFor('#pop-up')
                    ->clickLink('Yes')
                    ->assertSee('Feedback Report')
                    ->assertSee('Old data removed')
                    ->assertDontSee($firstAssessment->course->code);
        });
    }

    /** @test */
    public function admin_can_see_the_staff_report()
    {
        $this->browse(function ($browser) {
            $admin = $this->createAdmin();
            $staff1 = $this->createStaff();
            $staff2 = $this->createStaff();
            $course = $this->createCourse();
            $course->staff()->sync([$staff1->id]);
            $assessments = \App\Assessment::factory()->count(5)->create(['staff_id' => $staff1->id]);
            $assessments = \App\Assessment::factory()->count(7)->create(['staff_id' => $staff2->id]);
            $assessments->each(function ($assessment) {
                $feedbacks = \App\AssessmentFeedback::factory()->count(rand(1, 5))->create(['assessment_id' => $assessment->id]);
            });
            $browser->loginAs($admin)
                    ->visit("/")
                    ->clickLink('Admin')
                    ->click('#staff-report-button')
                    ->assertSee('Staff Report')
                    ->assertSee($staff1->fullName())
                    ->clickLink($staff1->fullName())
                    ->assertSee('Staff Details')
                    ->assertSee($staff1->courses()->first()->code);
        });
    }

    /** @test */
    public function admin_can_see_toggle_users_admin_flag()
    {
        $this->browse(function ($browser) {
            $admin = $this->createAdmin();
            $assessment = \App\Assessment::factory()->create();
            $staff = $assessment->staff;

            $browser->loginAs($admin)
                    ->visit("/")
                    ->clickLink('Admin')
                    ->clickLink($staff->fullName())
                    ->assertSee('Staff Details')
                    ->check('is_admin')
                    ->pause(300);
            $this->assertTrue($staff->fresh()->is_admin);
            $browser->loginAs($admin)
                    ->visit("/")
                    ->clickLink('Admin')
                    ->clickLink($staff->fullName())
                    ->assertSee('Staff Details')
                    ->uncheck('is_admin')
                    ->pause(300);
            $this->assertFalse($staff->fresh()->is_admin);
        });
    }

    /** disabled test while working out dates :-( */
    public function admin_can_upload_the_coursework_spreadsheet()
    {
        $this->browse(function ($browser) {
            $admin = $this->createAdmin();
            $sheet = $this->createSpreadsheet();
            $browser->loginAs($admin)
                    ->visit("/")
                    ->clickLink('Admin')
                    ->click('#upload-coursework-button')
                    ->assertSee('Upload Coursework Spreadsheet')
                    ->attach('sheet', $sheet)
                    ->press('Upload')
                    ->assertSee('TEST9999')
                    ->assertSee('TEST1234')
                    ->assertSee('Feedback Report')
                    ->assertSee($this->staff1->fullName())
                    ->assertSee($this->staff2->fullName());
        });
    }

    protected function createSpreadsheet($data = null)
    {
        $spreadsheet = new Spreadsheet;
        $this->staff1 = $this->createStaff();
        $this->staff2 = $this->createStaff();
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
                    'In Person',
                ],
                [
                    Carbon::now()->addDays(3)->format('l, F d, Y'),
                    '',
                    'TEST9999',
                    'Test Course 2',
                    'Homework',
                    $this->staff2->fullName(),
                    $this->staff2->username,
                    'Whole Class',
                ],
            ];
        }
        return $spreadsheet->generate($data);
    }
}
