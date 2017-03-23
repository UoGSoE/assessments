<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Wlm\WlmClient;

class WlmClientTest extends TestCase
{
    /**
     * @test
     * @group integration
    */
    public function can_get_a_list_of_all_courses_from_the_real_wlm()
    {
        $client = new WlmClient;

        $courses = $client->getCourses();

        $this->assertEquals(200, $client->statusCode);
        $this->assertGreaterThan(2, $courses->count());
    }

    /**
     * @test
     * @group integration
    */
    public function can_get_a_member_of_staff_from_the_real_wlm()
    {
        $client = new WlmClient;

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
        $client = new WlmClient;

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
    public function can_get_a_single_course_from_the_real_wlm()
    {
        $client = new WlmClient;

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
    public function trying_to_talk_to_the_wlm_if_its_offline_throws_an_exception()
    {
        $client = new WlmClient;
        try {
            $staff = $client->getStaff('WLMDOWN');
        } catch (\Exception $e) {
            return;
        }

        $this->fail('Talking to the real wlm while it was offline did not throw an exception');
    }

}
