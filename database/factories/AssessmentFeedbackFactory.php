<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentFeedback;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFeedbackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssessmentFeedback::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory(),
            'assessment_id' => Assessment::factory(),
            'student_id' => User::factory()->student(),
            'feedback_given' => false,
            'staff_notified' => false,
        ];
    }
}
