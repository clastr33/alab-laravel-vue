<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
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
}
