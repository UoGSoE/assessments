<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    //use DatabaseMigrations;
    use DatabaseSetup;

    protected function setUp()
    {
        parent::setUp();
//        $this->setupDatabase();

//        $this->disableExceptionHandling();
    }

    public function disableExceptionHandling()
    {
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);

        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(\Exception $e) {}
            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }

    protected function withExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, $this->oldExceptionHandler);
        return $this;
    }

    public function createStudent($attribs = [])
    {
        return factory(\App\User::class)->states('student')->create($attribs);
    }

    public function createStaff($attribs = [])
    {
        return factory(\App\User::class)->states('staff')->create($attribs);
    }

    public function createAdmin($attribs = [])
    {
        return factory(\App\User::class)->states('admin')->create($attribs);
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
