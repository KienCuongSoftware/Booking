<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'hotel_id',
        'room_type_id',
        'valid_from',
        'valid_to',
        'discount_type',
        'discount_value',
        'max_uses',
        'uses_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'discount_value' => 'decimal:2',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function normalizedCode(): string
    {
        return strtoupper(trim($this->code));
    }

    /**
     * @return array{valid: bool, message?: string}
     */
    public function validateFor(Hotel $hotel, RoomType $roomType, Carbon $checkIn, Carbon $checkOut): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => __('Mã không còn hiệu lực.')];
        }

        $today = Carbon::today();
        if ($today->lt($this->valid_from) || $today->gt($this->valid_to)) {
            return ['valid' => false, 'message' => __('Mã không áp dụng trong khoảng ngày này.')];
        }

        if ($this->hotel_id !== null && (int) $this->hotel_id !== (int) $hotel->id) {
            return ['valid' => false, 'message' => __('Mã không áp dụng cho khách sạn này.')];
        }

        if ($this->room_type_id !== null && (int) $this->room_type_id !== (int) $roomType->id) {
            return ['valid' => false, 'message' => __('Mã không áp dụng cho loại phòng này.')];
        }

        $stayOverlapsPromo = ! ($checkOut->toDateString() <= $this->valid_from->toDateString()
            || $checkIn->toDateString() > $this->valid_to->toDateString());
        if (! $stayOverlapsPromo) {
            return ['valid' => false, 'message' => __('Mã không áp dụng cho lịch đã chọn.')];
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return ['valid' => false, 'message' => __('Mã đã hết lượt sử dụng.')];
        }

        return ['valid' => true];
    }

    public function discountAmountForSubtotal(float $subtotal): float
    {
        if ($this->discount_type === 'percent') {
            return round($subtotal * ((float) $this->discount_value / 100), 2);
        }

        return min((float) $this->discount_value, $subtotal);
    }
}
