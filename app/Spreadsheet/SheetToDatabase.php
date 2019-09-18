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
        $columns = [
            'course' => 0,
            'level' => 1,
            'assessment_type' => 2,
            'feedback_type' => 3,
            'staff' => 4,
            'staff_email' => 5,
            'submission_deadline' => 6,
            'feedback_deadline' => 7,
            'given' => 8,
            'student_complaints' => 9,
            'comments' => 10,
        ];

        $course = Course::findByCode($row[$columns['course']]);
        if (!$course) {
            $this->addError('Unknown course code : ' . $row[$columns['course']]);
            return false;
        }

        $staff = User::findByEmail($row[$columns['staff_email']]);
        if (!$staff) {
            $this->addError('Unknown staff email : ' . $row[$columns['staff_email']]);
            return false;
        }

        try {
            // depeding on what Excel has done - 'date' columns are parsed differently...
            if ($row[$columns['submission_deadline']] instanceof \DateTime) {
                $submissionDate = Carbon::instance($row[$columns['submission_deadline']]);
            } else {
                $submissionDate = Carbon::createFromFormat('d/m/Y H:i', $row[$columns['submission_deadline']]);
            }
            $submissionDate->hour(16)
                ->minute(0);
        } catch (\Exception $e) {
            $this->addError('Could not parse date : ' . $row[$columns['submission_deadline']]);
            return false;
        }
        if ($this->assessmentIsInThePast($submissionDate)) {
            $this->addError('Assessment date is in the past : ' . $submissionDate->format('d/M/Y'));
            return false;
        }

        $assessment = Assessment::updateOrCreate(
            [
                'course_id' => $course->id,
                'staff_id' => $staff->id,
                'deadline' => $submissionDate,
            ],
            [
                'type' => $row[$columns['assessment_type']],
                'feedback_type' => $row[$columns['feedback_type']],
                'comment' => $row[$columns['comments']],
            ]
        );

        return $assessment;
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
