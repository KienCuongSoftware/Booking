<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
    ];

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'province_code', 'code');
    }
}
