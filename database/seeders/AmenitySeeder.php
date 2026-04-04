<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Tiện nghi cấp khách sạn (bảng amenities — không dùng cho loại phòng).
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'WiFi miễn phí', 'icon_key' => 'wifi', 'sort_order' => 1],
            ['name' => 'Phòng gia đình', 'icon_key' => 'family-room', 'sort_order' => 2],
            ['name' => 'Phòng không hút thuốc', 'icon_key' => 'non-smoking', 'sort_order' => 3],
            ['name' => 'Lễ tân 24 giờ', 'icon_key' => 'reception-24h', 'sort_order' => 4],
            ['name' => 'Sân thượng / hiên', 'icon_key' => 'terrace', 'sort_order' => 5],
            ['name' => 'Sân vườn', 'icon_key' => 'garden', 'sort_order' => 6],
            ['name' => 'Dịch vụ phòng', 'icon_key' => 'room-service', 'sort_order' => 7],
            ['name' => 'Tiện nghi cho khách khuyết tật', 'icon_key' => 'accessibility', 'sort_order' => 8],
            ['name' => 'Thang máy', 'icon_key' => 'elevator', 'sort_order' => 9],
            ['name' => 'Giặt ủi', 'icon_key' => 'laundry', 'sort_order' => 10],
        ];

        foreach ($rows as $row) {
            Amenity::query()->updateOrCreate(
                ['icon_key' => $row['icon_key']],
                $row,
            );
        }
    }
}
