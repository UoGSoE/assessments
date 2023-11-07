<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Auth;

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
        $assessments = Course::active()->with('assessments')->get()->flatMap(function ($course) {
            $year = $course->getYear();

            return $course->assessments->map->toEvent($course, $year);
        })->toJson();

        return view('landing', compact('assessments'));
    }
}
