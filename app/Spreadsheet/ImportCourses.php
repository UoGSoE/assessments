<?php

namespace App\Spreadsheet;

use App\Course;
use App\User;
use Illuminate\Support\Str;

class ImportCourses
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

    public function importCourses($filename)
    {
        $rows = $this->sheet->import($filename);
        $this->currentRow = 1;
        foreach ($rows as $row) {
            $this->rowToCourse($row);
            $this->currentRow++;
        }
    }

    public function importStudentAllocations($filename)
    {
        $rows = $this->sheet->import($filename);
        $this->currentRow = 1;
        foreach ($rows as $row) {
            $this->rowToStudentAllocation($row);
            $this->currentRow++;
        }
    }

    public function importStaffAllocations($filename)
    {
        $rows = $this->sheet->import($filename);
        $this->currentRow = 1;
        foreach ($rows as $row) {
            $this->rowToStaffAllocation($row);
            $this->currentRow++;
        }
    }

    public function rowToCourse($row)
    {
        if ($row[0] == 'Course Title') {
            return;
        }

        //Course title
        if (!$row[0]) {
            $this->errors->add('title', "Row {$this->currentRow}: Course title is required");
            return false;
        }

        //Course code
        if (!$row[1]) {
            $this->errors->add('code', "Row {$this->currentRow}: Course code is required");
            return false;
        }

        //Course discipline
        if (!$row[2]) {
            $this->errors->add('discipline', "Row {$this->currentRow}: Course discipline is required");
            return false;
        }

        //Course active? (Yes/No)
        if ($row[3] != 'Yes' && $row[3] != 'No') {
            $this->errors->add('discipline', "Row {$this->currentRow}: Course active must be Yes or No");
            return false;
        }

        $course = Course::firstOrCreate([
            'title' => $row[0],
            'code' => $row[1],
            'discipline' => $row[2],
            'is_active' => $row[3] == 'Yes' ? 1 : 0,
        ]);
    }

    public function rowToStudentAllocation($row)
    {
        if ($row[0] == 'Forenames') {
            return;
        }

        //Student forenames
        if (!$row[0]) {
            $this->errors->add('forenames', "Row {$this->currentRow}: Student forenames is required");
            return false;
        }

        //Student surname
        if (!$row[1]) {
            $this->errors->add('surname', "Row {$this->currentRow}: Student surname is required");
            return false;
        }

        //Student GUID
        if (!$row[2]) {
            $this->errors->add('guid', "Row {$this->currentRow}: Student GUID is required");
            return false;
        }

        //Course code
        if (!$row[3]) {
            $this->errors->add('course', "Row {$this->currentRow}: Course code is required");
            return false;
        }

        $student = User::firstOrCreate([
            'username' => $row[2],
        ], [
            'is_student' => true,
            'forenames' => $row[0],
            'surname' => $row[1],
            'email' => $row[2] . '@student.gla.ac.uk',
            'password' => bcrypt(Str::random(64))
        ]);

        $course = Course::where('code', $row[3])->first();

        if (!$course) {
            $this->errors->add('course', "Row {$this->currentRow}: Course {$row[3]} not found");
            return false;
        }

        $student->courses()->syncWithoutDetaching([$course->id]);
    }

    public function rowToStaffAllocation($row)
    {
        if ($row[0] == 'Forenames') {
            return;
        }

        //Staff forenames
        if (!$row[0]) {
            $this->errors->add('forenames', "Row {$this->currentRow}: Staff forenames is required");
            return false;
        }

        //Staff surname
        if (!$row[1]) {
            $this->errors->add('surname', "Row {$this->currentRow}: Staff surname is required");
            return false;
        }

        //Staff GUID
        if (!$row[2]) {
            $this->errors->add('guid', "Row {$this->currentRow}: Staff GUID is required");
            return false;
        }

        //Staff email
        if (!$row[3]) {
            $this->errors->add('email', "Row {$this->currentRow}: Staff email is required");
            return false;
        }

        //Course code
        if (!$row[4]) {
            $this->errors->add('course', "Row {$this->currentRow}: Course code is required");
            return false;
        }

        $staff = User::firstOrCreate([
            'username' => $row[2],
        ], [
            'is_student' => false,
            'forenames' => $row[0],
            'surname' => $row[1],
            'email' => $row[3],
            'password' => bcrypt(Str::random(64))
        ]);

        $course = Course::where('code', $row[4])->first();

        if (!$course) {
            $this->errors->add('course', "Row {$this->currentRow}: Course {$row[4]} not found");
            return false;
        }

        $staff->courses()->syncWithoutDetaching([$course->id]);
    }
}
