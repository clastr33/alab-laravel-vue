<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\Patient;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $login = Str::ascii($validated['login']);
        $login = preg_replace('/\s+/', '', $login) ?? $login;
        $patient = Patient::query()->where('login', $login)->first();

        if (!$patient || $patient->birth_date->format('Y-m-d') !== $validated['password']) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = JWTAuth::fromUser($patient);

        return response()->json(['token' => $token]);
    }
}
