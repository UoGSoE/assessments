<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Wlm\WlmImporter;
use App\Wlm\FakeWlmClient;
use App\Wlm\WlmClient;
use App\Course;
use App\User;

class WlmImportTest extends TestCase
{
    /** @test */
    public function can_import_the_data_from_the_fake_wlm()
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

    /** @test */
    public function can_limit_course_numbers_while_importing_the_data_from_the_fake_wlm()
    {
        $importer = new WlmImporter(new FakeWlmClient);

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
    public function can_import_the_data_from_the_real_wlm()
    {
        $importer = new WlmImporter(new WlmClient);

        $importer->run(50);

        $this->assertGreaterThan(0, Course::count());
        $this->assertGreaterThan(0, User::staff()->count());
        $this->assertGreaterThan(0, User::student()->count());
    }

}
