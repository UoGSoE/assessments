<?php

namespace App\Console\Commands;

use App\Assessment;
use Illuminate\Console\Command;

class AutoSignoffAssessments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:autosignoff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sign off any applicable assessments';

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
        Assessment::notSignedOff()->get()->each(function ($assessment) {
            if ($assessment->canBeAutoSignedOff()) {
                $assessment->autoSignOff();
            }
        });
    }
}
