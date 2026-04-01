<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed realistic sample users (password for all: "password").
     */
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'name' => 'Nguyễn Minh Tuấn',
                'email' => 'admin@booking-demo.local',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=256&h=256&fit=crop',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => $now,
            ],
            [
                'name' => 'Trần Thị Mai Anh',
                'email' => 'mai.tran@sunrisehotel.vn',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=256&h=256&fit=crop',
                'password' => Hash::make('password'),
                'role' => UserRole::Host,
                'email_verified_at' => $now,
            ],
            [
                'name' => 'Lê Hoàng Nam',
                'email' => 'nam.le@booking-demo.local',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=256&h=256&fit=crop',
                'password' => Hash::make('password'),
                'role' => UserRole::Staff,
                'email_verified_at' => $now,
            ],
            [
                'name' => 'Phạm Đức Huy',
                'email' => 'huy.pham@gmail.com',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=256&h=256&fit=crop',
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
                'email_verified_at' => $now,
            ],
            [
                'name' => 'Hoàng Thị Lan',
                'email' => 'lan.hoang@outlook.com',
                'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=256&h=256&fit=crop',
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
                'email_verified_at' => $now,
            ],
        ];

        foreach ($rows as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
