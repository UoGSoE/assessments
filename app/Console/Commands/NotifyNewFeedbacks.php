<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class NotifyNewFeedbacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:notifystaff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications to staff about new feedbacks';

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
     */
    public function handle(): void
    {
        $staff = User::staff()->get();
        foreach ($staff as $user) {
            $user->notifyAboutNewFeedback();
        }
    }
}
