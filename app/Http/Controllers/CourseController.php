<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Course;

class CourseController extends Controller
{
    public function show($id): View
    {
        $course = Course::with('assessments.course')->findOrFail($id);

        return view('course.show', compact('course'));
    }
}
