<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusEvent extends Model
{
    protected $fillable = [
        'booking_id',
        'from_status',
        'to_status',
        'changed_by',
        'changed_at',
        'note',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
            'context' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
