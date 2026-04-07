<?php

namespace App\Enums;

enum BookingPaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';

    public function labelVi(): string
    {
        return match ($this) {
            self::Unpaid => 'Chưa thanh toán',
            self::Pending => 'Chờ thanh toán',
            self::Paid => 'Đã thanh toán',
            self::Failed => 'Thanh toán lỗi',
        };
    }
}
