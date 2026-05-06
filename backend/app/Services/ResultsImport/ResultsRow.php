<?php

namespace App\Services\ResultsImport;

final readonly class ResultsRow
{
    public function __construct(
        public int $patientId,
        public string $patientName,
        public string $patientSurname,
        public string $patientSex,
        public string $patientBirthDate,
        public string $orderId,
        public string $testName,
        public ?string $testValue,
        public ?string $testReference,
    ) {
    }
}

