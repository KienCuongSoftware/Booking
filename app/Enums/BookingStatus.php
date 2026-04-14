<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';
    case Completed = 'completed';

    public function labelVi(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xử lý',
            self::Confirmed => 'Đã xác nhận',
            self::Cancelled => 'Đã hủy',
            self::NoShow => 'Không đến',
            self::Completed => 'Hoàn tất',
        };
    }
}
