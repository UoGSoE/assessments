<?php

namespace App\Http\Controllers;

use App\Course;
use App\Spreadsheet\ImportCourses;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected $importer;

    public function __construct(ImportCourses $importer)
    {
        $this->importer = $importer;
    }

    public function show($id)
    {
        $course = Course::with('assessments.course')->findOrFail($id);
        return view('course.show', compact('course'));
    }

    public function import()
    {
        return view('course.import');
    }

    public function importStudents()
    {
        return view('course.students.import');
    }

    public function importStaff()
    {
        return view('course.staff.import');
    }

    public function importSave(Request $request)
    {
        if (!$request->hasFile('sheet')) {
            return redirect()->back()->withErrors(['sheet' => 'No spreadsheet given']);
        }
        $tempFile = $request->file('sheet')->getPathName();
        $this->importer->importCourses($tempFile);
        return redirect()->route('report.feedback')
                ->withErrors($this->importer->errors)
                ->with('success_message', 'Imported data');
    }

    public function importStudentsSave(Request $request)
    {
        if (!$request->hasFile('sheet')) {
            return redirect()->back()->withErrors(['sheet' => 'No spreadsheet given']);
        }
        $tempFile = $request->file('sheet')->getPathName();
        $this->importer->importStudentAllocations($tempFile);
        return redirect()->route('report.feedback')
                ->withErrors($this->importer->errors)
                ->with('success_message', 'Imported data');
    }

    public function importStaffSave(Request $request)
    {
        if (!$request->hasFile('sheet')) {
            return redirect()->back()->withErrors(['sheet' => 'No spreadsheet given']);
        }
        $tempFile = $request->file('sheet')->getPathName();
        $this->importer->importStaffAllocations($tempFile);
        return redirect()->route('report.feedback')
                ->withErrors($this->importer->errors)
                ->with('success_message', 'Imported data');
    }
}
