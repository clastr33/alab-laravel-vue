<?php

namespace App\Services\ResultsImport;

final class ResultsCsvSchema
{
    public const HEADER = [
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
}

