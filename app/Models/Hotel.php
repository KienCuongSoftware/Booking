<?php

namespace App\Models;

use App\Support\PublicDisk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Hotel extends Model
{
    protected $fillable = [
        'host_id',
        'name',
        'slug',
        'city',
        'province_code',
        'address',
        'star_rating',
        'base_price',
        'old_price',
        'new_price',
        'weekend_multiplier',
        'holiday_multiplier',
        'last_minute_hours',
        'last_minute_discount_percent',
        'email_templates',
        'thumbnail',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'new_price' => 'decimal:2',
            'weekend_multiplier' => 'decimal:4',
            'holiday_multiplier' => 'decimal:4',
            'last_minute_hours' => 'integer',
            'last_minute_discount_percent' => 'decimal:2',
            'email_templates' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $hotel): void {
            if (! $hotel->slug) {
                $hotel->slug = Str::slug($hotel->name).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class)->withTimestamps();
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class)->orderBy('name')->orderBy('id');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(HotelImage::class)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->latest('id');
    }

    public function cancellationPolicy(): HasOne
    {
        return $this->hasOne(CancellationPolicy::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Booking::class, 'hotel_id', 'booking_id', 'id', 'id');
    }

    public function thumbnailUrl(): string
    {
        if (! $this->thumbnail) {
            return 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80&auto=format&fit=crop';
        }

        if (Str::startsWith($this->thumbnail, ['http://', 'https://'])) {
            return $this->thumbnail;
        }

        return PublicDisk::url($this->thumbnail);
    }
}
