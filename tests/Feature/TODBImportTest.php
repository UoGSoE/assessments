<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Course;
use App\TODB\FakeTODBClient;
use App\TODB\TODBClient;
use App\TODB\TODBImporter;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;

class TODBImportTest extends TestCase
{
    /** @test */
    public function can_import_the_data_from_the_fake_todb()
    {
        $importer = new TODBImporter(new FakeTODBClient);

        $importer->run();

        $this->assertCount(2, Course::all());
        $this->assertCount(3, User::staff()->get());
        $this->assertCount(3, User::student()->get());
        Course::all()->each(function ($course) {
            $this->assertCount(2, $course->staff()->get());
            $this->assertCount(2, $course->students()->get());
        });
        $courseA = Course::first();
        $this->assertEquals('TEST1234', $courseA->code);
        $this->assertEquals('Fake Course 1234', $courseA->title);
        $this->assertEquals('Electronics', $courseA->discipline);
    }

    /** @test */
    public function can_limit_course_numbers_while_importing_the_data_from_the_fake_todb()
    {
        $importer = new TODBImporter(new FakeTODBClient);

        $importer->run(1);

        $this->assertCount(1, Course::all());
        $this->assertCount(2, User::staff()->get());
        $this->assertCount(2, User::student()->get());
        Course::all()->each(function ($course) {
            $this->assertCount(2, $course->staff()->get());
            $this->assertCount(2, $course->students()->get());
        });
    }

    /**
     * @test
     * @group integration
    */
    public function can_import_the_data_from_the_real_todb()
    {
        $importer = new TODBImporter(new TODBClient);

        $importer->run(50);

        $this->assertGreaterThan(0, Course::count());
        $this->assertGreaterThan(0, User::staff()->count());
        $this->assertGreaterThan(0, User::student()->count());
    }
}
