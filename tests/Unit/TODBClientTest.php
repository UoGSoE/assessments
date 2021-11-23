<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\TODB\TODBClient;
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

    /**
     * @test
     * @group integration
    */
    public function can_get_a_member_of_staff_from_the_todb()
    {
        $client = $this->getTODBClient();

        $staff = $client->getStaff('wra1z');

        $this->assertEquals(200, $client->statusCode);
        $this->assertEquals('William.Allan@glasgow.ac.uk', $staff['Email']);
    }

    /**
     * @test
     * @group integration
    */
    public function getting_a_non_existant_member_of_staff_returns_an_error()
    {
        $client = $this->getTODBClient();

        $staff = $client->getStaff('NONEXISTANT');

        $this->assertEquals(200, $client->statusCode);
        $this->assertEquals(-1, $client->responseCode);
        $this->assertEquals('No such GUID', $client->responseMessage);
        $this->assertEquals(collect([]), $staff);
    }

    /**
     * @test
     * @group integration
    */
    public function can_get_a_single_course_from_the_todb()
    {
        $client = $this->getTODBClient();

        $course = $client->getCourse('ENG4001');

        $this->assertEquals(200, $client->statusCode);
        $this->assertEquals('ENG4001', $course['Code']);
        $this->assertEquals('Acoustics and Audio Technology 4', $course['Title']);
        $this->assertArrayHasKey('Staff', $course);
        $this->assertArrayHasKey('Students', $course);
    }

    /**
     * @group integration
    */
    public function trying_to_talk_to_the_todb_if_its_offline_throws_an_exception()
    {
        $client = $this->getTODBClient();
        try {
            $staff = $client->getStaff('TODBDOWN');
        } catch (\Exception $e) {
            return;
        }

        $this->fail('Talking to the real teaching office db while it was offline did not throw an exception');
    }

    public function getTODBClient()
    {
        return new TODBClient;
    }
}
