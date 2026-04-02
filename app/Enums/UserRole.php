<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Host = 'host';
    case Staff = 'staff';
    case Customer = 'customer';

    /** Tên vai trò ngắn (hiển thị giao diện). */
    public function shortLabelVi(): string
    {
        return match ($this) {
            self::Admin => 'Quản trị viên',
            self::Host => 'Chủ khách sạn',
            self::Staff => 'Nhân viên',
            self::Customer => 'Khách hàng',
        };
    }

    /** Mô tả ngắn quyền hạn (tuỳ chọn). */
    public function labelVi(): string
    {
        return match ($this) {
            self::Admin => 'Quản lý toàn bộ hệ thống',
            self::Host => 'Đăng và quản lý khách sạn',
            self::Staff => 'Xử lý đơn đặt phòng',
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
