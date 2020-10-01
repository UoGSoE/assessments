<?php

namespace Database\Factories;

use App\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => 'ENG' . $this->faker->unique()->numberBetween(1000, 5999),
            'title' => $this->faker->sentence,
            'is_active' => true,
            'discipline' => 'Electronics'
        ];
    }
}
