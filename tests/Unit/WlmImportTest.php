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
    /** @test */
    public function can_convert_wlm_format_course_to_a_local_one()
    {
        $wlmCourse = [
            'Code' => 'ENG1234',
            'Title' => 'A Test Course'
        ];

        $course = Course::fromWlmData($wlmCourse);

        $this->assertEquals('ENG1234', $course->code);
        $this->assertEquals('A Test Course', $course->title);
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

    /** @test */
    public function can_get_a_list_of_all_courses_from_the_wlm()
    {
        $client = new FakeWlmClient;
        
        $courses = $client->getCourses();
        
        $this->assertEquals(200, $client->statusCode);
        $this->assertCount(2, $courses);
    }

    /** @test */
    public function can_get_a_member_of_staff_from_the_wlm()
    {
        $client = new FakeWlmClient;
        
        $staff = $client->getStaff('fake1z');
        
        $this->assertEquals(200, $client->statusCode);
        $this->assertEquals('fake1z@glasgow.ac.uk', $staff['Email']);
    }

    /** @test */
    public function can_import_the_data_from_the_wlm()
    {
        $importer = new WlmImporter(new FakeWlmClient);
        $importer->run();

        $this->assertCount(2, Course::all());
        $this->assertCount(3, User::staff()->get());
        $this->assertCount(3, User::student()->get());
        Course::all()->each(function ($course) {
            $this->assertCount(2, $course->staff()->get());
            $this->assertCount(2, $course->students()->get());
        });
        User::staff()->get()->each(function ($staff) {
            $this->assertEquals("{$staff->username}@glasgow.ac.uk", $staff->email);
        });
    }
}
