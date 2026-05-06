<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Patient extends Authenticatable implements JWTSubject
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'login',
        'name',
        'surname',
        'sex',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
