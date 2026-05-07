<?php

namespace Tests\Concerns;

use App\Services\ResultsImport\ResultsCsvImporter;

trait ImportsResultsCsv
{
    protected function importResultsCsv(): void
    {
        (new ResultsCsvImporter())->import(base_path('import/results.csv'));
    }
}

