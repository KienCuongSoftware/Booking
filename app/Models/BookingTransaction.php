<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTransaction extends Model
{
    protected $fillable = [
        'booking_id',
        'type',
        'status',
        'amount',
        'currency',
        'event_key',
        'reference',
        'metadata',
        'performed_by',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
