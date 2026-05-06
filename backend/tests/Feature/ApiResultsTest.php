<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\ImportsResultsCsv;
use Tests\TestCase;

class ApiResultsTest extends TestCase
{
    use LazilyRefreshDatabase;
    use ImportsResultsCsv;

    public function test_results_requires_authentication(): void
    {
        $response = $this->getJson('/api/results');

        $response->assertStatus(401);
    }

    public function test_results_returns_patient_orders_and_results(): void
    {
        $this->importResultsCsv();

        $login = $this->postJson('/api/login', [
            'login' => 'PiotrKowalski',
            'password' => '1983-04-12',
        ])->assertOk()->json('token');

        $response = $this->getJson('/api/results', [
            'Authorization' => "Bearer {$login}",
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'patient' => ['id', 'name', 'surname', 'sex', 'birthDate'],
                'orders' => [
                    ['orderId', 'results' => [['name', 'value', 'reference']]],
                ],
            ]);
    }
}
