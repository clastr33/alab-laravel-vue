<?php

namespace App\Services\ResultsImport;

final class ResultsCsvReader
{
    /**
     * @return array{0: string, 1: resource} delimiter + stream
     */
    public function open(string $path): array
    {
        $source = fopen($path, 'rb');
        if ($source === false) {
            throw new \RuntimeException("Unable to open file: {$path}");
        }

        $normalized = $this->normalizeLineEndings($source);
        $delimiter = $this->detectDelimiterFromHeaderLine($normalized);

        return [$delimiter, $normalized];
    }

    /**
     * @param resource $source
     * @return resource
     */
    private function normalizeLineEndings($source)
    {
        $dest = fopen('php://temp', 'w+b');
        if ($dest === false) {
            fclose($source);
            throw new \RuntimeException('Unable to allocate temp stream');
        }

        $tail = '';
        while (!feof($source)) {
            $chunk = fread($source, 1024 * 1024); // 1MB
            if ($chunk === false) {
                fclose($source);
                fclose($dest);
                throw new \RuntimeException('Unable to read input stream');
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

    /**
     * @param resource $stream
     */
    private function detectDelimiterFromHeaderLine($stream): string
    {
        $pos = ftell($stream);
        $line = fgets($stream);
        fseek($stream, $pos);

        if ($line === false) {
            throw new \RuntimeException('CSV appears to be empty');
        }

        return str_contains($line, ';') ? ';' : ',';
    }
}

