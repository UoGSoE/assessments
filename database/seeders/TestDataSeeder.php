<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // \App\AssessmentFeedback::truncate();
        // \App\Models\Assessment::truncate();
        // \App\Models\Course::truncate();
        // \App\Models\User::truncate();

        $students = factory(\App\Models\User::class, 5)->states('student')->create();
        $courses = factory(\App\Models\Course::class, 30)->create()->each(function ($course) use ($students) {
            $course->students()->sync($students->pluck('id'));
        });
        foreach ($courses as $course) {
            foreach (range(1, 10) as $i) {
                $now = \Carbon\Carbon::now()->subWeeks(26);
                $deadline = $now->addWeeks(rand(0, 51))->addDays(rand(0, 6))->hour(16)->minute(0);
                $assessment = factory(\App\Models\Assessment::class)->create(['course_id' => $course->id, 'deadline' => $deadline]);
            }
        }
    }
}
