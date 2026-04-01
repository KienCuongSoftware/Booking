<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Host = 'host';
    case Staff = 'staff';
    case Customer = 'customer';

    public function labelVi(): string
    {
        return match ($this) {
            self::Admin => 'Quản lý toàn bộ',
            self::Host => 'Đăng và quản lý khách sạn',
            self::Staff => 'Quản lý booking',
            self::Customer => 'Đặt phòng',
        };
    }

    public function dashboardRouteName(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Host => 'host.dashboard',
            self::Staff => 'staff.dashboard',
            self::Customer => 'customer.dashboard',
        };
    }
}
