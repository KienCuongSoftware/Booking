<?php

namespace App\Enums;

enum BookingPaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case PayPal = 'paypal';

    public function labelVi(): string
    {
        return match ($this) {
            self::Cash => 'Tiền mặt',
            self::BankTransfer => 'Chuyển khoản',
            self::PayPal => 'PayPal',
        };
    }
}
