<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancellationPolicyTier extends Model
{
    protected $fillable = [
        'cancellation_policy_id',
        'min_hours_before',
        'max_hours_before',
        'fee_percent',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_hours_before' => 'integer',
            'max_hours_before' => 'integer',
            'fee_percent' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class, 'cancellation_policy_id');
    }
}
