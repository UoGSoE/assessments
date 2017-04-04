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
