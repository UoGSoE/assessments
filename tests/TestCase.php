<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp() : void
    {
        parent::setUp();

        // Enable foreign key support for SQLITE databases
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::statement(DB::raw('PRAGMA foreign_keys=on'));
        }
    }

    public function createStudent($attribs = [])
    {
        return \App\User::factory()->student()->create($attribs);
    }

    public function createStaff($attribs = [])
    {
        return \App\User::factory()->staff()->create($attribs);
    }

    public function createAdmin($attribs = [])
    {
        return \App\User::factory()->admin()->create($attribs);
    }

    public function createCourse($attribs = [])
    {
        return \App\Course::factory()->create($attribs);
    }

    public function createAssessment($attribs = [])
    {
        return \App\Assessment::factory()->create($attribs);
    }

    public function createFeedback($attribs = [])
    {
        return \App\AssessmentFeedback::factory()->create($attribs);
    }
}
