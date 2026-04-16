<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PublicHoliday extends Model
{
    protected $fillable = [
        'holiday_date',
        'name',
        'country',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
        ];
    }

    public static function isHoliday(Carbon $date, string $country = 'VN'): bool
    {
        return self::query()
            ->where('country', $country)
            ->whereDate('holiday_date', $date->toDateString())
            ->exists();
    }
}
