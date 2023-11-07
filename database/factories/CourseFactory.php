<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => 'ENG'.$this->faker->unique()->numberBetween(1000, 5999),
            'title' => $this->faker->sentence(),
            'is_active' => true,
            'discipline' => 'Electronics',
        ];
    }
}
