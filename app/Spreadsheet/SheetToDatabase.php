<?php

namespace App\Spreadsheet;

use App\Course;
use App\Assessment;
use App\User;
use Carbon\Carbon;

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
        if (! $row[0] instanceof \DateTime) {
            $this->addError('Invalid Date');
            return false;
        }

        if ($this->isInThePast($row[0])) {
            return false;
        }

        $courseCode = strtoupper($row[2]);
        $courseTitle = $row[3];
        $course = Course::findByCode($courseCode);
        if (!$course) {
            $course = Course::create(['code' => $courseCode, 'title' => $courseTitle]);
        }

        $staff = User::findByUsername(strtolower($row[6]));
        if (!$staff) {
            $this->addError('Invalid GUID');
            return false;
        }

        $data = [
            'deadline' => $row[0],
            'type' => $row[4],
            'course_id' => $course->id,
            'staff_id' => $staff->id,
        ];
        $assessment = Assessment::create($data);
        return $assessment;
    }

    protected function addError($message)
    {
        $this->errors->add('errors', "Row {$this->currentRow}: {$message}");
    }

    protected function isInThePast($date)
    {
        if ($date->lt(Carbon::now())) {
            return true;
        }
        return false;
    }
}
