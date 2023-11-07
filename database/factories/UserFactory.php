<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        static $password;

        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $password ?: $password = bcrypt('secret'),
            'surname' => preg_replace('/[^a-z\s]/i', ' ', $this->faker->lastName()),
            'forenames' => $this->faker->firstName(),
            'remember_token' => Str::random(10),
            'is_student' => false,
        ];
    }

    public function student()
    {
        return $this->state(function (array $attributes) {
            return [
                'username' => $this->faker->unique()->numberBetween(1000000, 9999999).$this->faker->randomLetter(),
                'is_student' => true,
            ];
        });
    }

    public function staff()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_student' => false,
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_student' => false,
                'is_admin' => true,
            ];
        });
    }
}
