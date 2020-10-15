<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function show($id)
    {
        $course = Course::with('assessments.course')->findOrFail($id);

        return view('course.show', compact('course'));
    }
}
