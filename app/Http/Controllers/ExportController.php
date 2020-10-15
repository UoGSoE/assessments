<?php

namespace App\Http\Controllers;

use App\Assessment;
use App\Spreadsheet\Spreadsheet;
use App\User;
use Illuminate\Http\Request;

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
                'Level',
                'Assessment Type',
                'Feedback Type',
                'Staff',
                'Staff Email',
                'Submission Date',
                'Feedback Deadline',
                'Given',
                'Student Complaints',
                'Other Comments',
            ],
        ];
        foreach ($assessments as $assessment) {
            $row = [
                $assessment->course->code,
                $assessment->course->level,
                $assessment->type,
                $assessment->feedback_type,
                $assessment->staff->fullName(),
                $assessment->staff->email,
                $assessment->deadline->format('d/m/Y H:i'),
                $assessment->deadline->format('d/m/Y H:i'),
                $assessment->reportSignedOff(),
                $assessment->totalNegativeFeedbacks(),
                $assessment->comment,
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
            ],
        ];
        $staff = User::staff()->with('assessments.feedbacks')->orderBy('surname')->get();
        foreach ($staff as $user) {
            $row = [
                $user->fullName().($user->is_admin ? ' (Admin)' : ''),
                $user->numberOfAssessments(),
                $user->totalStudentFeedbacks(),
                $user->numberOfMissedDeadlines(),
            ];
            $rows[] = $row;
        }

        return $rows;
    }
}
