<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show($id): View
    {
        $course = Course::with('assessments.course')->findOrFail($id);

        return view('course.show', compact('course'));
    }
}
