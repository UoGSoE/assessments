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
        'username' => $faker->unique()->userName,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'surname' => preg_replace('/[^a-z\s]/i', ' ', $faker->lastName),
        'forenames' => $faker->firstName(),
        'remember_token' => str_random(10),
        'is_student' => false,
    ];
});

$factory->state(App\User::class, 'student', function ($faker) {
    return [
        'is_student' => true,
    ];
});
$factory->state(App\User::class, 'staff', function ($faker) {
    return [
        'is_student' => false,
    ];
});
$factory->state(App\User::class, 'admin', function ($faker) {
    return [
        'is_student' => false,
        'is_admin' => true,
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
    $deadline = $now->addWeeks(rand(1, 26));
    return [
        'deadline' => $deadline,
        'type' => $faker->randomElement($types),
        'course_id' => function () {
            return factory(App\Course::class)->create()->id;
        },
        'user_id' => function () {
            return factory(App\User::class)->states('staff')->create()->id;
        },
        'feedback_left' => null,
    ];
});

$factory->define(App\AssessmentFeedback::class, function (Faker\Generator $faker) {
    return [
        'course_id' => function () {
            return factory(App\Course::class)->create()->id;
        },
        'assessment_id' => function () {
            return factory(App\Assessment::class)->create()->id;
        },
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'feedback_given' => false,
        'staff_notified' => false,
    ];
});
