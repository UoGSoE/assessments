<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Spreadsheet\SheetToDatabase;
use Carbon\Carbon;
use App\Course;
use App\Assessment;
use Auth;

class CourseworkController extends Controller
{
    protected $importer;

    public function __construct(SheetToDatabase $importer)
    {
        $this->importer = $importer;
    }

    public function edit()
    {
        return view('coursework.import');
    }

    public function update(Request $request)
    {
        if (!$request->hasFile('sheet')) {
            return redirect()->back()->withErrors(['sheet' => 'No spreadsheet given']);
        }
        $tempFile = $request->file('sheet')->getPathName();
        $this->importer->importSheetData($tempFile);
        return redirect()->route('report.feedback')->withErrors($this->importer->errors)->with('success_message', 'Imported data');
    }
}
