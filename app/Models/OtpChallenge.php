<?php

namespace App\Models;

use App\Enums\OtpChallengeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpChallenge extends Model
{
    protected $fillable = [
        'email',
        'user_id',
        'type',
        'payload',
        'code_hash',
        'expires_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'type' => OtpChallengeType::class,
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
