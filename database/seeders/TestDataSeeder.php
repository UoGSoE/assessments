<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \App\AssessmentFeedback::truncate();
        // \App\Models\Assessment::truncate();
        // \App\Models\Course::truncate();
        // \App\Models\User::truncate();

        $students = \App\Models\User::factory()->count(5)->student()->create();
        $courses = \App\Models\Course::factory()->count(30)->create()->each(function ($course) use ($students) {
            $course->students()->sync($students->pluck('id'));
        });
        foreach ($courses as $course) {
            foreach (range(1, 10) as $i) {
                $now = \Carbon\Carbon::now()->subWeeks(26);
                $deadline = $now->addWeeks(rand(0, 51))->addDays(rand(0, 6))->hour(16)->minute(0);
                $assessment = \App\Models\Assessment::factory()->create(['course_id' => $course->id, 'deadline' => $deadline]);
            }
        }
    }
}
