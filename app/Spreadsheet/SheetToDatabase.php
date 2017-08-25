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
        if (!array_key_exists(13, $row)) {
            return false;
        }
        foreach (range(1, 4) as $assessmentNumber) {
            $ass = $this->extractAssessment($assessmentNumber, $row);
            if (!$ass) {
                return false;
            }

            if (!$ass['submission_date'] instanceof \DateTime) {
                return false;
            }

            $ass['submission_date'] = Carbon::instance($ass['submission_date'])->hour(16)->minute(0);
            if ($this->assessmentIsInThePast($ass['submission_date'])) {
                $this->addError('Assessment ' . $assessmentNumber . ' date is in the past : ' . $ass['submission_date']->format('d/M/Y'));
                return false;
            }

            $staff = User::findByEmail($ass['staff_email']);
            if (!$staff) {
                $this->addError('Unknown staff email : ' . $ass['staff_email']);
                return false;
            }

            $course = Course::findByCode($ass['course_code']);
            if (!$course) {
                $this->addError('Unknown course code : ' . $ass['course_code']);
                return false;
            }

            $assessment = Assessment::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'staff_id' => $staff->id,
                    'deadline' => $ass['submission_date']
                ],
                [
                    'type' => $ass['submission_title'] . ' / ' . $ass['submission_method'],
                    'feedback_type' => $ass['feedback_method'] . '. ' . $ass['is_graded'],
                    'comment' => $ass['comments'],
                ]
            );
        }

        return true;
    }

    protected function extractAssessment($assessmentNumber, $row)
    {
        $spreadsheetColumnOffsets = [
            1 => 13,
            2 => 19,
            3 => 25,
            4 => 31,
        ];
        if (!array_key_exists($assessmentNumber, $spreadsheetColumnOffsets)) {
            return false;
        }
        $offset = $spreadsheetColumnOffsets[$assessmentNumber];
        if (!array_key_exists($offset, $row)) {
            return false;
        }
        return [
            'course_code' => $row[2] . $row[3],
            'course_title' => $row[5],
            'submission_title' => $row[$offset + 0],
            'submission_method' => $row[$offset + 1],
            'submission_date' => $row[$offset + 2],
            'feedback_method' => $row[$offset + 5],
            'is_graded' => preg_match('/y/i', $row[$offset + 4]) ? 'Graded' : 'Not Graded',
            'staff_email' => strtolower($row[12]),
            'staff_surname' => $row[10],
            'staff_forenames' => $row[11],
            'comments' => array_key_exists(38, $row) ? $row[38] : '',
        ];
    }

    protected function addError($message)
    {
        $this->errors->add('errors', "Row {$this->currentRow}: {$message}");
    }

    protected function assessmentIsInThePast($date)
    {
        if ($date->lt(Carbon::now())) {
            return true;
        }
        return false;
    }
}
