<?php

namespace App\Spreadsheet;

use App\Course;
use App\Assessment;
use App\User;
use Carbon\Carbon;

/**
    0 => "Term"
    1 => "Session"
    2 => "Subject"
    3 => "Catalog"
    4 => "Career"
    5 => "Descr"
    6 => "course"
    7 => "Class Stat"
    8 => "Cap Enrl"
    9 => "ID"
    10 => "Last"
    11 => "First Name"
    12 => "email"
    13 => "assignment 1 - title"
    14 => "assignment 1 - method of submission"
    15 => "assignment 1 - date of submission"
    16 => "assignment 1 - date feedback due"
    17 => "Graded feedback_1"
    18 => "Method of feedback_1"
    19 => "assignment 2 - title"
    20 => "assignment 2 - method of submission"
    21 => "assignment 2 - date of submission"
    22 => "assignment 2 - date feedback due"
    23 => "Graded feedback_2"
    24 => "Method of feedback_2"
    25 => "assignment 3 - title"
    26 => "assignment 3 - method of submission"
    27 => "assignment 3 - date of submission"
    28 => "assignment 3 - date feedback due"
    29 => "Graded feedback_3"
    30 => "Method of feedback_3"
    31 => "assignment 4 - title"
    32 => "assignment 4 - method of submission"
    33 => "assignment 4 - date of submission"
    34 => "assignment 4 - date feedback due"
    35 => "Graded"
    36 => "feedback_4"
    37 => "Method of feedback_4"
    38 => "Additional comments"
*/
class SheetToDatabase
{
    public $errors;
    protected $sheet;
    protected $currentRow = 1;

    public function __construct(Spreadsheet $sheet)
    {
        $this->sheet = $sheet;
        $this->errors = app('Illuminate\Support\MessageBag');
        $this->lastError = null;
    }

    public function importSheetData($filename)
    {
        $this->rowsToAssessments($this->sheet->import($filename));
    }

    public function rowsToAssessments($rows)
    {
        $this->currentRow = 1;
        foreach ($rows as $row) {
            $this->rowToAssessment($row);
            $this->currentRow++;
        }
    }

    public function rowToAssessment($row)
    {
        $course = $row[6];
        $staffEmail = strtolower($row[12]);
        $ass1 = [
            'title' => $row[13],
            'submission_method' => $row[14],
            'submission_date' => $row[15],
            'feedback_method' => $row[18],
            'is_graded' => $row[17],
        ];
        if (!$date) {
            return false;
        }
        dd($ass1);
        $deadline = $row[0];
        if (!$deadline) {
            return false;
        }
        if (! $deadline instanceof \DateTime) {
            try {
                $deadline = Carbon::parse($deadline);
            } catch (\Exception $e) {
                $this->addError('Invalid Date');
                return false;
            }
        } else {
            $deadline = Carbon::instance($deadline);
        }

        if ($this->assessmentIsInThePast($deadline)) {
            $this->addError('Date is in the past');
            return false;
        }

        $deadline = $this->addTime($deadline, trim($row[1]));

        $course = $this->getCourseFromRow($row);

        $staff = User::findByUsername(strtolower(trim($row[6])));
        if (!$staff) {
            $this->addError('Invalid GUID');
            return false;
        }

        return Assessment::create([
            'deadline' => $deadline,
            'type' => trim($row[4]),
            'course_id' => $course->id,
            'staff_id' => $staff->id,
            'feedback_type' => trim($row[7]),
        ]);
    }

    protected function addError($message)
    {
        $this->errors->add('errors', "Row {$this->currentRow}: {$message}");
    }

    protected function addTime($deadline, $timeString)
    {
        if (preg_match('/[0-9][0-9]:[0-9][0-9]/', $timeString, $matches)) {
            list($hour, $minute) = explode(':', $matches[0]);
            $deadline->hour($hour)->minute($minute);
        } else {
            $deadline->hour(16)->minute(0);
        }
        return $deadline;
    }

    protected function assessmentIsInThePast($date)
    {
        if ($date->lt(Carbon::now())) {
            return true;
        }
        return false;
    }

    protected function getCourseFromRow($row)
    {
        $courseCode = strtoupper($row[2]);
        $courseTitle = $row[3];
        $course = Course::findByCode($courseCode);
        if (!$course) {
            $course = Course::create(['code' => $courseCode, 'title' => $courseTitle]);
        }
        return $course;
    }
}
