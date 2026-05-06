<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        /** @var Patient|null $patient */
        $patient = auth('api')->user();

        if (!$patient) {
            if (!$request->expectsJson()) {
                return redirect('/');
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $patient->load(['orders.results']);

        if ($patient->orders->isEmpty()) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'surname' => $patient->surname,
                'sex' => $patient->sex,
                'birthDate' => $patient->birth_date->format('Y-m-d'),
            ],
            'orders' => $patient->orders
                ->sortBy('order_id')
                ->values()
                ->map(fn ($order) => [
                    'orderId' => (string) $order->order_id,
                    'results' => $order->results
                        ->sortBy('name')
                        ->values()
                        ->map(fn ($result) => [
                            'name' => $result->name,
                            'value' => $result->value,
                            'reference' => $result->reference,
                        ])->all(),
                ])->all(),
        ]);
    }
}
