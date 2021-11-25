<?php

namespace App\Console\Commands;

use App\TODB\TODBImporter;
use Illuminate\Console\Command;

class TODBImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:todbimport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from the Teaching Office DB';

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
        app(TODBImporter::class)->run();
    }
}
