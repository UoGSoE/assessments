<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Course;

class HomeController extends Controller
{
    public function index()
    {
        $assessments = Auth::user()->assessmentsAsJson();
        return view('home', compact('assessments'));
    }

    public function landing()
    {
        if (auth()->check()) {
            return redirect('/home');
        }
        $data = [];
        foreach (Course::active()->with('assessments')->get() as $course) {
            $year = $course->getYear();
            foreach ($course->assessments as $assessment) {
                $event = $this->getEvent($assessment, $course, $year);
                $data[] = $event;
            }
        }
        return view('landing', ['assessments' => json_encode($data)]);
    }

    protected function getEvent($assessment, $course, $year)
    {
        $event = [
            'id' => $assessment->id,
            'title' => $assessment->title,
            'course_code' => $course->code,
            'course_title' => $course->title,
            'start' => $assessment->deadline->toIso8601String(),
            'end' => $assessment->deadline->addHours(1)->toIso8601String(),
            'feedback_due' => $assessment->feedback_due->toIso8601String(),
            'type' => $assessment->type,
            'color' => 'whitesmoke',
            'textColor' => 'black',
            'mine' => true
        ];
        if ($year) {
            $event['year'] = $year;
        }
        return $event;
    }
}
