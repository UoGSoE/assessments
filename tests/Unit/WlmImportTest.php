<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Wlm\FakeWlmClient;
use App\Course;
use App\User;
use App\Wlm\WlmImporter;

class WlmImportTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_convert_wlm_format_course_to_a_local_course_model()
    {
        $wlmCourse = [
            'Code' => 'ENG1234',
            'Title' => 'A Test Course',
            'CurrentFlag' => 'Yes',
        ];

        $course = Course::fromWlmData($wlmCourse);

        $this->assertEquals('ENG1234', $course->code);
        $this->assertEquals('A Test Course', $course->title);
        $this->assertTrue($course->is_active);
    }

    /** @test */
    public function a_course_marked_as_not_current_on_the_wlm_is_marked_as_not_active_locally()
    {
        $wlmCourse = [
            'Code' => 'ENG1234',
            'Title' => 'A Test Course',
            'CurrentFlag' => 'No'
        ];

        $course = Course::fromWlmData($wlmCourse);

        $this->assertEquals('ENG1234', $course->code);
        $this->assertEquals('A Test Course', $course->title);
        $this->assertFalse($course->is_active);
    }

    /** @test */
    public function can_convert_wlm_staff_data_to_a_local_user_model()
    {
        $wlmStaff = [
            'GUID' => 'fake1x',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
            'Email' => 'fake@example.com'
        ];

        $staff = User::staffFromWlmData($wlmStaff);

        $this->assertEquals('fake1x', $staff->username);
        $this->assertEquals('Fake', $staff->surname);
        $this->assertEquals('Jenny', $staff->forenames);
        $this->assertEquals('fake@example.com', $staff->email);
        $this->assertTrue($staff->isStaff());
    }

    /** @test */
    public function can_convert_wlm_student_data_to_a_local_user_model()
    {
        $wlmStudent = [
            'Matric' => '1234567',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
        ];

        $student = User::studentFromWlmData($wlmStudent);

        $this->assertEquals('1234567f', $student->username);
        $this->assertEquals('Fake', $student->surname);
        $this->assertEquals('Jenny', $student->forenames);
        $this->assertEquals('1234567f@student.gla.ac.uk', $student->email);
        $this->assertTrue($student->isStudent());
    }

    /** @test */
    public function converting_wlm_data_twice_doesnt_create_duplicates_locally()
    {
        $wlmStudent = [
            'Matric' => '1234567',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
        ];
        $student = User::studentFromWlmData($wlmStudent);
        $student = User::studentFromWlmData($wlmStudent);
        $this->assertCount(1, User::student()->get());

        $wlmStaff = [
            'GUID' => 'fake1x',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
            'Email' => 'fake@example.com'
        ];
        $staff = User::staffFromWlmData($wlmStaff);
        $staff = User::staffFromWlmData($wlmStaff);
        $this->assertCount(1, User::staff()->get());

        $wlmCourse = [
            'Code' => 'ENG1234',
            'Title' => 'A Test Course'
        ];
        $course = Course::fromWlmData($wlmCourse);
        $course = Course::fromWlmData($wlmCourse);
        $this->assertCount(1, Course::all());
    }
}
