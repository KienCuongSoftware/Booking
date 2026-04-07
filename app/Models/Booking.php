<?php

namespace App\Models;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentProvider;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'customer_id',
        'hotel_id',
        'room_type_id',
        'check_in_date',
        'check_out_date',
        'guest_count',
        'nights',
        'unit_price',
        'total_price',
        'currency',
        'status',
        'payment_method',
        'payment_provider',
        'payment_status',
        'payment_reference',
        'customer_note',
        'host_note',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'guest_count' => 'integer',
            'nights' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => BookingStatus::class,
            'payment_method' => BookingPaymentMethod::class,
            'payment_provider' => BookingPaymentProvider::class,
            'payment_status' => BookingPaymentStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
