<?php

namespace Database\Seeders;

use App\Models\RoomAmenity;
use Illuminate\Database\Seeder;

class RoomAmenitySeeder extends Seeder
{
    /**
     * Tiện nghi trong phòng (bảng room_amenities — tách khỏi amenities khách sạn).
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'WiFi miễn phí', 'icon_key' => 'rm-wifi', 'category_key' => 'chung', 'sort_order' => 1],
            ['name' => 'Phòng gia đình', 'icon_key' => 'rm-family', 'category_key' => 'chung', 'sort_order' => 2],
            ['name' => 'Phòng không hút thuốc', 'icon_key' => 'rm-non-smoking', 'category_key' => 'chinh_sach', 'sort_order' => 3],

            ['name' => 'Đồ vệ sinh miễn phí', 'icon_key' => 'rm-toiletries', 'category_key' => 'phong_tam', 'sort_order' => 40],
            ['name' => 'Vòi xịt vệ sinh', 'icon_key' => 'rm-bidet', 'category_key' => 'phong_tam', 'sort_order' => 41],
            ['name' => 'Toilet', 'icon_key' => 'rm-toilet', 'category_key' => 'phong_tam', 'sort_order' => 42],
            ['name' => 'Bồn tắm hoặc vòi sen', 'icon_key' => 'rm-bath-shower', 'category_key' => 'phong_tam', 'sort_order' => 43],
            ['name' => 'Khăn tắm / khăn tay', 'icon_key' => 'rm-towels', 'category_key' => 'phong_tam', 'sort_order' => 44],
            ['name' => 'Dép đi trong phòng', 'icon_key' => 'rm-slippers', 'category_key' => 'phong_tam', 'sort_order' => 45],
            ['name' => 'Máy sấy tóc', 'icon_key' => 'rm-hairdryer', 'category_key' => 'phong_tam', 'sort_order' => 46],
            ['name' => 'Phòng tắm riêng trong phòng', 'icon_key' => 'rm-private-bath', 'category_key' => 'phong_tam', 'sort_order' => 47],

            ['name' => 'Điều hòa không khí', 'icon_key' => 'rm-ac', 'category_key' => 'tien_nghi_phong', 'sort_order' => 60],
            ['name' => 'Sưởi', 'icon_key' => 'rm-heating', 'category_key' => 'tien_nghi_phong', 'sort_order' => 61],
            ['name' => 'TV màn phẳng', 'icon_key' => 'rm-tv', 'category_key' => 'tien_nghi_phong', 'sort_order' => 62],
            ['name' => 'Minibar', 'icon_key' => 'rm-minibar', 'category_key' => 'tien_nghi_phong', 'sort_order' => 63],
            ['name' => 'Ấm điện', 'icon_key' => 'rm-kettle', 'category_key' => 'tien_nghi_phong', 'sort_order' => 64],
            ['name' => 'Bàn làm việc', 'icon_key' => 'rm-desk', 'category_key' => 'tien_nghi_phong', 'sort_order' => 65],
            ['name' => 'Tủ quần áo / tủ đồ', 'icon_key' => 'rm-wardrobe', 'category_key' => 'tien_nghi_phong', 'sort_order' => 66],
            ['name' => 'Ghế sofa', 'icon_key' => 'rm-sofa', 'category_key' => 'tien_nghi_phong', 'sort_order' => 67],
            ['name' => 'Giường sofa', 'icon_key' => 'rm-sofa-bed', 'category_key' => 'tien_nghi_phong', 'sort_order' => 68],
            ['name' => 'Ổ cắm điện gần giường', 'icon_key' => 'rm-sockets', 'category_key' => 'tien_nghi_phong', 'sort_order' => 69],
            ['name' => 'Bàn ủi', 'icon_key' => 'rm-iron', 'category_key' => 'tien_nghi_phong', 'sort_order' => 70],
            ['name' => 'Báo thức / dịch vụ báo thức', 'icon_key' => 'rm-alarm', 'category_key' => 'tien_nghi_phong', 'sort_order' => 71],
            ['name' => 'Ga trải giường / drap', 'icon_key' => 'rm-linens', 'category_key' => 'tien_nghi_phong', 'sort_order' => 72],
            ['name' => 'Sàn lát gạch / đá hoa', 'icon_key' => 'rm-floor', 'category_key' => 'tien_nghi_phong', 'sort_order' => 73],
        ];

        foreach ($rows as $row) {
            RoomAmenity::query()->updateOrCreate(
                ['icon_key' => $row['icon_key']],
                $row,
            );
        }
    }
}
