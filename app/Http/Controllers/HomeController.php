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
        $assessments = Course::active()->with('assessments')->get()->flatMap(function ($course) {
            $year = $course->getYear();
            return $course->assessments->map->toEvent($course, $year);
        })->toJson();
        return view('landing', compact('assessments'));
    }
}
