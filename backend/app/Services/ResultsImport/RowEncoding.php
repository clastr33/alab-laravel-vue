<?php

namespace App\Services\ResultsImport;

final class RowEncoding
{
    public function toUtf8(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Prefer iconv — encoding alias support in mbstring varies per build.
        if (function_exists('iconv')) {
            foreach (['CP1250', 'ISO-8859-2'] as $from) {
                $converted = iconv($from, 'UTF-8//IGNORE', $value);
                if ($converted !== false && $converted !== '') {
                    return $converted;
                }
            }
        }

        return $value;
    }
}

