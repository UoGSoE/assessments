<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->state(App\User::class, 'student', function ($faker) {
    return [
        'is_student' => true,
    ];
});

$factory->define(App\Course::class, function (Faker\Generator $faker) {
    return [
        'code' => 'ENG' . $faker->unique()->numberBetween(1000, 9999),
        'title' => $faker->sentence,
    ];
});

$factory->define(App\Assessment::class, function (Faker\Generator $faker) {
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
    $deadline = rand(1, 10) > 5 ? $now->subWeeks(rand(1, 26)) : $now->addWeeks(rand(1, 26));
    return [
        'deadline' => $deadline,
        'type' => $faker->randomElement($types),
        'course_id' => function () {
            return factory(App\Course::class)->create()->id;
        },
    ];
});
