<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Spreadsheet\Spreadsheet;
use App\Assessment;
use App\User;

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

    public function staff()
    {
        $filename = $this->sheet->generate($this->generateStaffData());
        return response()->download($filename, 'staff.xlsx');
    }

    protected function generateAssessmentsData()
    {
        $assessments = Assessment::with('course', 'feedbacks')->orderBy('staff_id')->get();
        $rows = [
            [
                'Course',
                'Assessment Type',
                'Staff',
                'Feedback Deadline',
                'Given',
                'Student Complaints'
            ]
        ];
        foreach ($assessments as $assessment) {
            $row = [
                $assessment->course->code,
                $assessment->type,
                $assessment->staff->fullName(),
                $assessment->deadline->format('d/m/Y H:i'),
                $assessment->reportSignedOff(),
                $assessment->totalNegativeFeedbacks()
            ];
            $rows[] = $row;
        }
        return $rows;
    }

    protected function generateStaffData()
    {
        $rows = [
            [
                'Staff',
                'No. Assessments',
                'No. Student Feedbacks',
                'Missed Deadlines',
            ]
        ];
        $staff = User::staff()->with('assessments.feedbacks')->orderBy('surname')->get();
        foreach ($staff as $user) {
            $row = [
                $user->fullName() . ($user->is_admin ? ' (Admin)' : ''),
                $user->numberOfAssessments(),
                $user->totalStudentFeedbacks(),
                $user->numberOfMissedDeadlines(),
            ];
            $rows[] = $row;
        }

        return $rows;
    }
}
