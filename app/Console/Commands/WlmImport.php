<?php

namespace App\Console\Commands;

use App\Wlm\WlmImporter;
use Illuminate\Console\Command;

class WlmImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:wlmimport {--sync : Also remove data not in the WLM}';

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
     */
    public function handle(): void
    {
        if ($this->option('sync')) {
            app(WlmImporter::class)->sync();
        } else {
            app(WlmImporter::class)->run();
        }
    }
}
