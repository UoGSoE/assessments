<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Wlm\WlmImporter;

class WlmImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:wlmimport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import/sync data from the WLM';

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
        app(WlmImporter::class)->run();
    }
}
