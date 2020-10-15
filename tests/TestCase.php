<?php

namespace Tests;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

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
        return \App\Models\User::factory()->student()->create($attribs);
    }

    public function createStaff($attribs = [])
    {
        return \App\Models\User::factory()->staff()->create($attribs);
    }

    public function createAdmin($attribs = [])
    {
        return \App\Models\User::factory()->admin()->create($attribs);
    }

    public function createCourse($attribs = [])
    {
        return \App\Models\Course::factory()->create($attribs);
    }

    public function createAssessment($attribs = [])
    {
        return \App\Models\Assessment::factory()->create($attribs);
    }

    public function createFeedback($attribs = [])
    {
        return \App\Models\AssessmentFeedback::factory()->create($attribs);
    }
}
