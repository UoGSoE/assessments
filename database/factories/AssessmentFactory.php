<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $types = [
            'Report',
            'Homework',
            'Assignment',
            'Report',
            'Tutorial',
            'Lab Group',
            'Group Report',
        ];
        $now = \Carbon\Carbon::now();
        $deadline = $now->addWeeks(rand(1, 26));

        return [
            'deadline' => $deadline,
            'type' => $this->faker->randomElement($types),
            'course_id' => Course::factory(),
            'staff_id' => User::factory()->staff(),
            'feedback_left' => null,
            'feedback_type' => $this->faker->sentence(),
        ];
    }
}
