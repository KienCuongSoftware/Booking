<?php

namespace App\Enums;

enum BookingPaymentProvider: string
{
    case Momo = 'momo';
    case Paypal = 'paypal';

    public function labelVi(): string
    {
        return match ($this) {
            self::Momo => 'MoMo',
            self::Paypal => 'PayPal',
        };
    }
}
