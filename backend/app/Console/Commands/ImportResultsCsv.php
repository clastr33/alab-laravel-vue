<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Result;

#[Signature('app:import-results-csv {path=../import/results.csv : Path to results.csv}')]
#[Description('Import patients, orders and test results from results.csv')]
class ImportResultsCsv extends Command
{
    private function openNormalizedCsvStream(string $path)
    {
        $source = fopen($path, 'rb');
        if ($source === false) {
            return false;
        }

        $dest = fopen('php://temp', 'w+b');
        if ($dest === false) {
            fclose($source);
            return false;
        }

        // Normalize line endings so fgetcsv() works with \n, \r\n, \r, or mixed files.
        $tail = '';
        while (!feof($source)) {
            $chunk = fread($source, 1024 * 1024); // 1MB
            if ($chunk === false) {
                fclose($source);
                fclose($dest);
                return false;
            }

            $data = $tail . $chunk;
            $tail = '';
            if ($data !== '' && substr($data, -1) === "\r") {
                $tail = "\r";
                $data = substr($data, 0, -1);
            }

            $data = str_replace("\r\n", "\n", $data);
            $data = str_replace("\r", "\n", $data);

            fwrite($dest, $data);
        }

        fclose($source);
        rewind($dest);
        return $dest;
    }

    private function parseAndValidateHeader(array $row, int $line, $importLog): array
    {
        $header = array_map(fn ($h) => trim((string) $h), $row);
        $expected = [
            'patientId',
            'patientName',
            'patientSurname',
            'patientSex',
            'patientBirthDate',
            'orderId',
            'testName',
            'testValue',
            'testReference',
        ];

        if ($header !== $expected) {
            $msg = 'Invalid CSV header. Expected: ' . implode(';', $expected) . ' Got: ' . implode(';', $header);
            $importLog->error($msg, ['line' => $line]);
            throw new \RuntimeException($msg);
        }

        return $header;
    }

    private function mapRowToHeader(array $header, array $row, int $line, $importLog): ?array
    {
        if (count($row) !== count($header)) {
            $importLog->error('Invalid column count', ['line' => $line, 'columns' => count($row)]);
            return null;
        }

        $data = array_combine($header, $row);
        if ($data === false) {
            $importLog->error('Unable to map row to header', ['line' => $line]);
            return null;
        }

        return $data;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = (string) $this->argument('path');

        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $importLog = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/import-results.log'),
            'level' => 'info',
        ]);

        $fp = $this->openNormalizedCsvStream($path);
        if ($fp === false) {
            $this->error("Unable to open file: {$path}");
            return self::FAILURE;
        }

        $header = null;
        $line = 0;
        $ok = 0;
        $errors = 0;

        DB::beginTransaction();
        $importLog->info('Begin import transaction');

        try {
            while (($row = fgetcsv($fp, 0, ';')) !== false) {
                $line++;

                if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                if ($header === null) {
                    try {
                        $header = $this->parseAndValidateHeader($row, $line, $importLog);
                    } catch (\Throwable $e) {
                        $this->error($e->getMessage());
                        return self::FAILURE;
                    }

                    continue;
                }

                $data = $this->mapRowToHeader($header, $row, $line, $importLog);
                if ($data === null) {
                    $errors++;
                    continue;
                }

                // Feed DB
                try {
                    $patientId = (int) trim((string) $data['patientId']);
                    $name =            trim((string) $data['patientName']);
                    $surname =         trim((string) $data['patientSurname']);
                    $sex =  strtolower(trim((string) $data['patientSex']));
                    $birthDate =       trim((string) $data['patientBirthDate']);
                    $orderId =         trim((string) $data['orderId']);
                    $testName =        trim((string) $data['testName']);

                    $fields = [$name, $surname, $birthDate, $orderId, $testName];
                    if ($patientId <= 0 || in_array('', $fields, true)) {
                        throw new \RuntimeException('Missing required fields');
                    }

                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
                        throw new \RuntimeException("Invalid birth date format: {$birthDate}");
                    }

                    $patient = Patient::updateOrCreate(
                        ['id' => $patientId],
                        ['name' => $name, 'surname' => $surname, 'sex' => $sex, 'birth_date' => $birthDate]
                    );

                    $order = Order::firstOrCreate(
                        ['patient_id' => $patient->id, 'order_id' => $orderId],
                    );

                    Result::updateOrCreate(
                        ['order_id' => $order->id, 'name' => $testName],
                        [
                            'value' => $data['testValue'] !== null ? trim((string) $data['testValue']) : null,
                            'reference' => $data['testReference'] !== null ? trim((string) $data['testReference']) : null,
                        ]
                    );

                    $ok++;
                    $importLog->info('Imported row', compact('line', 'patientId', 'orderId', 'testName'));
                } catch (\Throwable $e) {
                    $errors++;
                    $importLog->error('Row import failed', ['line' => $line, 'error' => $e->getMessage(), 'row' => $data]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $importLog->error('Import failed', ['error' => $e->getMessage()]);
            $this->error($e->getMessage());
            return self::FAILURE;
        } finally {
            fclose($fp);
        }

        $this->info("Import finished. OK={$ok} ERRORS={$errors}. Log: " . storage_path('logs/import-results.log'));
        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
