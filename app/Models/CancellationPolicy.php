<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CancellationPolicy extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'is_active',
        'send_reminder_d3',
        'send_reminder_d1',
        'send_reminder_h6',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'send_reminder_d3' => 'boolean',
            'send_reminder_d1' => 'boolean',
            'send_reminder_h6' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(CancellationPolicyTier::class)->orderByDesc('min_hours_before');
    }
}
