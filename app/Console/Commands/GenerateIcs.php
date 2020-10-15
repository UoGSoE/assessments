<?php

namespace App\Console\Commands;

use App\Calendar\Calendar;
use App\Models\Course;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateIcs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:generateics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates ICS calendar files for assessments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // generate calendar for every course and one for each year
        $courses = Course::active()->with('assessments')->get();
        $allCal = resolve(Calendar::class);
        $cals = [];
        foreach ($courses as $course) {
            $year = $course->getYear();
            $assessments = $course->assessments;
            if (! array_key_exists($year, $cals)) {
                $cals[$year] = resolve(Calendar::class);
            }
            $cals[$year]->addAssessments($assessments);
            $allCal->addAssessments($assessments);
        }
        $allCal->save('eng/all.ics');
        foreach ($cals as $year => $cal) {
            $cal->save("eng/year{$year}.ics");
        }

        // generate calendar for every staffmember in the system
        $users = User::staff()->with('assessments')->get();
        foreach ($users as $user) {
            $userCal = resolve(Calendar::class);
            $userCal->addAssessments($user->assessments);
            $userCal->save($user->icsPath());
        }

        // generate a calendar for every student in the system
        $users = User::student()->get();
        foreach ($users as $user) {
            $userCal = resolve(Calendar::class);
            foreach ($user->courses->filter->isActive() as $course) {
                $userCal->addAssessments($course->assessments);
            }
            $userCal->save($user->icsPath());
        }
    }
}
