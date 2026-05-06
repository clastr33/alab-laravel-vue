<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\ImportsResultsCsv;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    use LazilyRefreshDatabase;
    use ImportsResultsCsv;

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $this->importResultsCsv();

        $response = $this->postJson('/api/login', [
            'login' => 'PiotrKowalski',
            'password' => '1983-04-12',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_returns_401_for_invalid_credentials(): void
    {
        $this->importResultsCsv();

        $response = $this->postJson('/api/login', [
            'login' => 'PiotrKowalski',
            'password' => '2000-01-01',
        ]);

        $response->assertStatus(401);
    }
}
