<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WlmImportTest extends TestCase
{
    /** @test */
    public function can_get_a_list_of_all_courses_from_the_wlm()
    {
        $courses = $httpClient->getJson('http://localhost:8088/persons3/api/getcourse/all');
        dd($courses);
    }
}
