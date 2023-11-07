<?php

namespace App\Http\Controllers;

use App\Spreadsheet\SheetToDatabase;
use Illuminate\Http\Request;

class CourseworkController extends Controller
{
    protected $importer;

    protected $hidden = ['created_at', 'updated_at'];

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
        if (! $request->hasFile('sheet')) {
            return redirect()->back()->withErrors(['sheet' => 'No spreadsheet given']);
        }
        $tempFile = $request->file('sheet')->getPathName();
        $this->importer->importSheetData($tempFile);

        return redirect()->route('report.feedback')
            ->withErrors($this->importer->errors)
            ->with('success_message', 'Imported data');
    }
}
