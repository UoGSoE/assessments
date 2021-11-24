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
            'code' => 'ENG1234',
            'title' => 'A Test Course',
            'is_current' => true,
            'discipline' => [
                'title' => 'Electronics'
            ]
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
            'code' => 'ENG1234',
            'title' => 'A Test Course',
            'is_current' => false,
            'discipline' => [
                'title' => 'Electronics'
            ],
        ];

        $course = Course::fromTODBData($todbCourse);

        $this->assertFalse($course->is_active);
    }

    /** @test */
    public function converting_todb_data_twice_doesnt_create_duplicates_locally()
    {
        $todbCourse = [
            'code' => 'ENG1234',
            'title' => 'A Test Course',
            'is_current' => true,
            'discipline' => [
                'title' => 'Electronics'
            ]
        ];
        $course = Course::fromTODBData($todbCourse);
        $course = Course::fromTODBData($todbCourse);
        $this->assertCount(1, Course::all());
    }
}
