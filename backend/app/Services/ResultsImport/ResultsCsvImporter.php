<?php

namespace App\Services\ResultsImport;

use App\Models\Order;
use App\Models\Patient;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ResultsCsvImporter
{
    public function __construct(
        private readonly ResultsCsvReader $reader = new ResultsCsvReader(),
        private readonly ResultsRowValidator $validator = new ResultsRowValidator(),
        private readonly RowEncoding $encoding = new RowEncoding(),
    ) {
    }

    public function import(string $path): ImportSummary
    {
        if (!is_file($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $importLog = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/import-results.log'),
            'level' => 'info',
        ]);

        [$delimiter, $stream] = $this->reader->open($path);

        $summary = new ImportSummary();
        $importLog->info('Begin import transaction', ['path' => $path, 'delimiter' => $delimiter]);

        DB::beginTransaction();

        try {
            $header = null;
            $line = 0;

            while (($lineStr = fgets($stream)) !== false) {
                $line++;
                $lineStr = trim($lineStr);

                if ($lineStr === '') {
                    continue;
                }

                $row = str_getcsv($lineStr, $delimiter);

                if ($header === null) {
                    $header = $this->parseAndValidateHeader($row, $line);
                    continue;
                }

                $data = $this->mapRow($header, $row);
                if ($data === null) {
                    $summary->errors++;
                    $importLog->error('Invalid column count', ['line' => $line, 'columns' => count($row)]);
                    continue;
                }

                $data = $this->normalizeDataEncoding($data);

                try {
                    $validated = $this->validator->validate($data);

                    $patient = Patient::updateOrCreate(
                        ['id' => $validated->patientId],
                        [
                            'name' => $validated->patientName,
                            'surname' => $validated->patientSurname,
                            'sex' => $validated->patientSex,
                            'birth_date' => $validated->patientBirthDate,
                        ],
                    );

                    $order = Order::firstOrCreate([
                        'patient_id' => $patient->id,
                        'order_id' => $validated->orderId,
                    ]);

                    Result::updateOrCreate(
                        ['order_id' => $order->id, 'name' => $validated->testName],
                        ['value' => $validated->testValue, 'reference' => $validated->testReference],
                    );

                    $summary->ok++;
                    $importLog->info('Imported row', [
                        'line' => $line,
                        'patientId' => $validated->patientId,
                        'orderId' => $validated->orderId,
                        'testName' => $validated->testName,
                    ]);
                } catch (\Throwable $e) {
                    $summary->errors++;
                    $importLog->error('Row import failed', [
                        'line' => $line,
                        'error' => $e->getMessage(),
                        'row' => $data,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $importLog->error('Import failed', ['error' => $e->getMessage()]);
            throw $e;
        } finally {
            fclose($stream);
        }

        return $summary;
    }

    /**
     * @return array<int, string>
     */
    private function parseAndValidateHeader(array $row, int $line): array
    {
        $header = array_map(fn ($h) => trim((string) $h), $row);

        if ($header !== ResultsCsvSchema::HEADER) {
            throw new \RuntimeException(
                'Invalid CSV header. Expected: ' . implode(';', ResultsCsvSchema::HEADER) . ' Got: ' . implode(';', $header)
            );
        }

        return $header;
    }

    /**
     * @param array<int, string> $header
     * @param array<int, string> $row
     * @return array<string, mixed>|null
     */
    private function mapRow(array $header, array $row): ?array
    {
        if (count($row) !== count($header)) {
            return null;
        }

        $mapped = array_combine($header, $row);
        if ($mapped === false) {
            return null;
        }

        return $mapped;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDataEncoding(array $data): array
    {
        foreach (['patientName', 'patientSurname', 'testName', 'testValue', 'testReference'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = $this->encoding->toUtf8($data[$key]);
            }
        }

        return $data;
    }
}

