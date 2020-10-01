<?php

namespace Database\Factories;

use App\Assessment;
use App\Course;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Assessment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
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
