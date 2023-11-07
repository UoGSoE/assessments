<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use Illuminate\Console\Command;

class NotifyOffice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:notifyoffice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify teaching office about problematic assessments';

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
        Assessment::all()->each->notifyIfProblematic();
    }
}
