<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Course;
use App\Assessment;
use App\AssessmentFeedback;

class StudentAssessmentsAsJsonTest extends TestCase
{
    /** @test */
    public function if_negative_feedback_given_json_has_feedback_missed_flagged()
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->save($student);
        $assessment = $this->createAssessment(['course_id' => $course->id]);
        $feedback = $this->createFeedback(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'user_id' => $student->id, 'feedback_given' => false]);

        $json = $student->fresh()->assessmentsAsJson();

        dd($json);
    }

    /** @test */
    public function we_can_fetch_all_assessments_for_a_given_student_as_json()
    {
        $student = $this->createStudent();
        $courses = factory(Course::class, 20)->create()->each(function ($course) use ($student) {
            $course->students()->attach($student);
            $assessments = factory(Assessment::class, 5)->create();
            $course->assessments()->attach($assessments->pluck('id'));
        });
        $assessment = Assessment::first();
        $assessment->feedbacks()->save(new AssessmentFeedback(['user_id' => $student->id, 'course_id' => $assessment->course_id, 'assessment_id' => $assessment->id, 'feedback_given' => false]));
        $json = $student->assessmentsAsJson();

        dd($json);
    }
}
