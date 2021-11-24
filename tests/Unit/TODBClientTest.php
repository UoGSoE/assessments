<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\TODB\TODBClient;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TODBClientTest extends TestCase
{
    /**
     * @test
     * @group integration
    */
    public function can_get_a_list_of_all_courses_from_the_todb()
    {
        $client = $this->getTODBClient();

        $courses = $client->getCourses();

        $this->assertEquals(200, $client->statusCode);
        $this->assertGreaterThan(2, $courses->count());
    }

    public function getTODBClient()
    {
        return new TODBClient;
    }
}
