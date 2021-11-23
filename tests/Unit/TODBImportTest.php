<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Course;
use App\TODB\FakeTODBClient;
use App\TODB\TODBImporter;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TODBImportTest extends TestCase
{
    /** @test */
    public function can_convert_todb_format_course_to_a_local_course_model()
    {
        $todbCourse = [
            'Code' => 'ENG1234',
            'Title' => 'A Test Course',
            'CurrentFlag' => 'Yes',
            'Discipline' => 'Electronics'
        ];

        $course = Course::fromTODBData($todbCourse);

        $this->assertEquals('ENG1234', $course->code);
        $this->assertEquals('A Test Course', $course->title);
        $this->assertEquals('Electronics', $course->discipline);
        $this->assertTrue($course->is_active);
    }

    /** @test */
    public function a_course_marked_as_not_current_on_the_todb_is_marked_as_not_active_locally()
    {
        $todbCourse = [
            'Code' => 'ENG1234',
            'Title' => 'A Test Course',
            'CurrentFlag' => 'No',
            'Discipline' => 'Electronics'
        ];

        $course = Course::fromTODBData($todbCourse);

        $this->assertFalse($course->is_active);
    }

    /** @test */
    public function can_convert_todb_staff_data_to_a_local_user_model()
    {
        $todbStaff = [
            'GUID' => 'fake1x',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
            'Email' => 'fake@example.com'
        ];

        $staff = User::staffFromTODBData($todbStaff);

        $this->assertEquals('fake1x', $staff->username);
        $this->assertEquals('Fake', $staff->surname);
        $this->assertEquals('Jenny', $staff->forenames);
        $this->assertEquals('fake@example.com', $staff->email);
        $this->assertTrue($staff->isStaff());
    }

    /** @test */
    public function can_convert_todb_student_data_to_a_local_user_model()
    {
        $todbStudent = [
            'Matric' => '1234567',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
        ];

        $student = User::studentFromTODBData($todbStudent);

        $this->assertEquals('1234567f', $student->username);
        $this->assertEquals('Fake', $student->surname);
        $this->assertEquals('Jenny', $student->forenames);
        $this->assertEquals('1234567f@student.gla.ac.uk', $student->email);
        $this->assertTrue($student->isStudent());
    }

    /** @test */
    public function converting_todb_data_twice_doesnt_create_duplicates_locally()
    {
        $todbStudent = [
            'Matric' => '1234567',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
        ];
        $student = User::studentFromTODBData($todbStudent);
        $student = User::studentFromTODBData($todbStudent);
        $this->assertCount(1, User::student()->get());

        $todbStaff = [
            'GUID' => 'fake1x',
            'Surname' => 'Fake',
            'Forenames' => 'Jenny',
            'Email' => 'fake@example.com'
        ];
        $staff = User::staffFromTODBData($todbStaff);
        $staff = User::staffFromTODBData($todbStaff);
        $this->assertCount(1, User::staff()->get());

        $todbCourse = [
            'Code' => 'ENG1234',
            'Title' => 'A Test Course',
            'Discipline' => 'Electronics'
        ];
        $course = Course::fromTODBData($todbCourse);
        $course = Course::fromTODBData($todbCourse);
        $this->assertCount(1, Course::all());
    }
}
