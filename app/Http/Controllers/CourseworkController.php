<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Spreadsheet\Spreadsheet;
use Carbon\Carbon;
use App\Course;
use App\Assessment;
use Auth;

class CourseworkController extends Controller
{
    public function update(Request $request)
    {
        if (!$request->hasFile('sheet')) {
            return redirect()->back()->withErrors(['sheet' => 'No spreadsheet given']);
        }
        $tempFile = $request->file('sheet')->getPathName();
        $rows = (new Spreadsheet)->import($tempFile);
        $header = array_shift($rows);
        $count = 0;
        foreach ($rows as $row) {
            fwrite(STDERR, $count++);
            $this->rowToAssessment($row);
        }
    }

    protected function rowToAssessment($row)
    {
        $date = $row[0];
        $time = $row[1];
        $code = $row[2];
        $title = $row[3];
        $type = $row[4];
        $staff = $row[5];
        $amPm = preg_match('/[0-9]+\s*(am|pm)/i', $time, $matches);
        if ($amPm === 1) {
            $time = $matches[0];
            $date = Carbon::createFromFormat('d/m/Y HA', $date->format('d/m/Y') . ' ' . $time);
        } else {
            $date = Carbon::createFromFormat('d/m/Y', $date->format('d/m/Y'))->hour(16)->minute(0)->second(0);
        }
        if (!$date) {
            dd($row[0]);
        }
        $course = Course::where('code', '=', $code)->first();
        if (!$course) {
            $course = Course::create(['code' => $code, 'title' => $title]);
        }
        $assessment = Assessment::create([
            'course_id' => $course->id,
            'deadline' => $date,
            'type' => $type,
            'user_id' => Auth::user()->id
        ]);
        return $assessment;
    }
}
