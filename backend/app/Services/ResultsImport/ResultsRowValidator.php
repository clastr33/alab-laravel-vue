<?php

namespace App\Services\ResultsImport;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ResultsRowValidator
{
    /**
     * @param array<string, mixed> $data
     */
    public function validate(array $data): ResultsRow
    {
        $validator = Validator::make($data, [
            'patientId' => ['required', 'integer', 'min:1'],
            'patientName' => ['required', 'string'],
            'patientSurname' => ['required', 'string'],
            'patientSex' => ['required', 'string'],
            'patientBirthDate' => ['required', 'date_format:Y-m-d'],
            'orderId' => ['required', 'string'],
            'testName' => ['required', 'string'],
            'testValue' => ['nullable'],
            'testReference' => ['nullable'],
        ]);

        $validated = $validator->validate();

        $sex = strtolower(trim((string) $validated['patientSex']));
        $sex = match ($sex) {
            'male', 'female' => $sex,
            default => throw ValidationException::withMessages(['patientSex' => "Invalid value: {$sex}"]),
        };

        return new ResultsRow(
            patientId: (int) $validated['patientId'],
            patientName: trim((string) $validated['patientName']),
            patientSurname: trim((string) $validated['patientSurname']),
            patientSex: $sex,
            patientBirthDate: (string) $validated['patientBirthDate'],
            orderId: trim((string) $validated['orderId']),
            testName: trim((string) $validated['testName']),
            testValue: isset($validated['testValue']) ? trim((string) $validated['testValue']) : null,
            testReference: isset($validated['testReference']) ? trim((string) $validated['testReference']) : null,
        );
    }
}

