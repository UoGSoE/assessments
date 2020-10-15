<?php

// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Wlm\FakeWlmClient;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FakeWlmClientTest extends TestCase
{
    /** @test */
    public function can_get_a_list_of_all_courses_from_the_wlm()
    {
        $client = $this->getWlmClient();

        $courses = $client->getCourses();

        $this->assertEquals(200, $client->statusCode);
        $this->assertCount(2, $courses);
    }

    /** @test */
    public function can_get_a_member_of_staff_from_the_wlm()
    {
        $client = $this->getWlmClient();

        $staff = $client->getStaff('fake1z');

        $this->assertEquals(200, $client->statusCode);
        $this->assertEquals('fake1z@glasgow.ac.uk', $staff['Email']);
    }

    /** @test */
    public function getting_a_non_existant_member_of_staff_returns_an_error()
    {
        $client = $this->getWlmClient();

        $staff = $client->getStaff('NONEXISTANT');

        $this->assertEquals(200, $client->statusCode);
        $this->assertEquals(-1, $client->responseCode);
        $this->assertEquals('No such GUID', $client->responseMessage);
        $this->assertEquals(collect([]), $staff);
    }

    /** @test */
    public function can_get_a_single_course_from_the_wlm()
    {
        $client = $this->getWlmClient();

        $course = $client->getCourse('TEST1234');

        $this->assertEquals(200, $client->statusCode);
        $this->assertEquals('TEST1234', $course['Code']);
        $this->assertEquals('Fake Course 1234', $course['Title']);
        $this->assertArrayHasKey('Staff', $course);
        $this->assertArrayHasKey('Students', $course);
    }

    /** @test */
    public function trying_to_talk_to_the_wlm_if_its_offline_throws_an_exception()
    {
        $client = $this->getWlmClient();

        $this->expectException(\Exception::class);

        $staff = $client->getStaff('WLMDOWN');
    }

    public function getWlmClient()
    {
        return new FakeWlmClient;
    }
}
