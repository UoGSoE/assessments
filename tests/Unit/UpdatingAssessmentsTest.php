<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdatingAssessmentsTest extends TestCase
{
    /** @test */
    public function adding_a_new_assessment_works_if_its_due_in_the_future()
    {
        
    }

    /** @test */
    public function adding_a_new_assessment_is_skipped_if_its_due_in_the_past()
    {
        
    }
}
