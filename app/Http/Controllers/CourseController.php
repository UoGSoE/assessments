<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Course;

class CourseController extends Controller
{
    public function show($id)
    {
        $course = Course::findOrFail($id);
        if (Gate::denies('see_course', $course)) {
            return redirect('/');
        }
        return view('course.show', compact('course'));
    }
}
