<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    public function createStudent($attribs = [])
    {
        return factory(\App\User::class)->states('student')->create($attribs);
    }
    public function createCourse($attribs = [])
    {
        return factory(\App\Course::class)->create($attribs);
    }
    public function createAssessment($attribs = [])
    {
        return factory(\App\Assessment::class)->create($attribs);
    }
    public function createFeedback($attribs = [])
    {
        return factory(\App\AssessmentFeedback::class)->create($attribs);
    }
}
