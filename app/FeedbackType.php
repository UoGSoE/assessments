<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedbackType extends Model
{
    protected $fillable = ['name'];
/*

0   Acknowledgement of receipt of coursework by email (maybe we need to set this aside for another discussion?)
1   No Feedback
2   Generic feedback -  Post-Exam Summative Assessment
3   Verbal Feedback without a Grade
4   Verbal Feedback with a Grade (is this likely?)
5   Written Feedback without a Grade
6   Written Feedback with a Grade
7   Electronic Feedback without a grade
8   Electronic Feedback with a grade

*/

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
