<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()
        );
    }

    public function log($value)
    {
        fwrite(STDERR, $value);
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
