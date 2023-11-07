<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Models\AssessmentFeedback;
use App\Models\Course;
use App\Models\User;
use App\Wlm\FakeWlmClient;
use App\Wlm\WlmClient;
use App\Wlm\WlmImporter;
use Carbon\Carbon;
use Tests\TestCase;

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
        $courseA = Course::first();
        $this->assertEquals('TEST1234', $courseA->code);
        $this->assertEquals('Fake Course 1234', $courseA->title);
        $this->assertEquals('Electronics', $courseA->discipline);
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

    /** @test */
    public function data_not_in_the_wlm_can_be_removed_from_the_local_db_after_import()
    {
        $student = $this->createStudent();
        $assessment = $this->createAssessment(['deadline' => Carbon::now()->subWeeks(10)]);
        $course = $assessment->course;
        $course->students()->sync([$student->id]);
        $student->recordFeedback($assessment);
        $staff = $assessment->staff;
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->sync();

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('assessment_feedbacks', ['student_id' => $student->id]);
        //$this->assertEquals(0, AssessmentFeedback::count());
        $this->assertCount(2, Course::all());
        $this->assertCount(3, User::staff()->get());
        $this->assertCount(3, User::student()->get());
    }

    /**
     * @test
     *
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
