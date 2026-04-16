<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RoomType extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'max_occupancy',
        'quantity',
        'area_sqm',
        'base_price',
        'old_price',
        'new_price',
        'weekend_multiplier',
        'holiday_multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_occupancy' => 'integer',
            'quantity' => 'integer',
            'area_sqm' => 'decimal:2',
            'base_price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'new_price' => 'decimal:2',
            'weekend_multiplier' => 'decimal:4',
            'holiday_multiplier' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $roomType): void {
            if (! $roomType->slug) {
                $roomType->slug = Str::slug($roomType->name).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bedLines(): HasMany
    {
        return $this->hasMany(RoomTypeBedLine::class)->orderBy('sort_order');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(RoomAmenity::class)->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomTypeImage::class)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->latest('id');
    }
}
