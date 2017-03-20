<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Spreadsheet\Spreadsheet;
use App\Assessment;

class ExportController extends Controller
{
    protected $sheet;

    public function __construct(Spreadsheet $sheet)
    {
        $this->sheet = $sheet;
    }

    public function assessments()
    {
        $filename = $this->sheet->generate($this->generateAssessmentsData());
        return response()->download($filename, 'assessments.xlsx');
    }

    protected function generateAssessmentsData()
    {
        $assessments = Assessment::with('course', 'feedbacks')->orderBy('staff_id')->get();
        $rows = [];
        foreach ($assessments as $assessment) {
            $row = [
                $assessment->course->code,
                $assessment->type,
                $assessment->staff->fullName(),
                $assessment->deadline->format('d/m/Y H:i'),
                $assessment->reportFeedbackLeft(),
                $assessment->totalNegativeFeedbacks()
            ];
            $rows[] = $row;
        }
        return $rows;
    }
}
