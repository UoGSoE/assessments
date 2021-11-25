<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\TODB\FakeTODBClient;
use Tests\TestCase;

class FakeTODBClientTest extends TestCase
{
    /** @test */
    public function can_get_a_list_of_all_courses_from_the_fake_client_todb()
    {
        $client = $this->getTODBClient();

        $courses = $client->getCourses();

        $this->assertEquals(200, $client->statusCode);
        $this->assertCount(2, $courses);
    }

    public function getTODBClient()
    {
        return new FakeTODBClient;
    }
}
