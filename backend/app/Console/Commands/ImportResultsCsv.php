<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\ResultsImport\ResultsCsvImporter;

#[Signature('app:import-results-csv {path=import/results.csv : Path to results.csv}')]
#[Description('Import patients, orders and test results from results.csv')]
class ImportResultsCsv extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = (string) $this->argument('path');

        try {
            $summary = (new ResultsCsvImporter())->import($path);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info("Import finished. OK={$summary->ok} ERRORS={$summary->errors}. Log: " . storage_path('logs/import-results.log'));
        return $summary->errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
