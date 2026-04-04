<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            ['code' => 'ha-noi', 'name' => 'Hà Nội', 'type' => 'Thành phố'],
            ['code' => 'ho-chi-minh', 'name' => 'Hồ Chí Minh', 'type' => 'Thành phố'],
            ['code' => 'hai-phong', 'name' => 'Hải Phòng', 'type' => 'Thành phố'],
            ['code' => 'hue', 'name' => 'Huế', 'type' => 'Thành phố'],
            ['code' => 'da-nang', 'name' => 'Đà Nẵng', 'type' => 'Thành phố'],
            ['code' => 'can-tho', 'name' => 'Cần Thơ', 'type' => 'Thành phố'],
            ['code' => 'tuyen-quang', 'name' => 'Tuyên Quang', 'type' => 'Tỉnh'],
            ['code' => 'lao-cai', 'name' => 'Lào Cai', 'type' => 'Tỉnh'],
            ['code' => 'thai-nguyen', 'name' => 'Thái Nguyên', 'type' => 'Tỉnh'],
            ['code' => 'phu-tho', 'name' => 'Phú Thọ', 'type' => 'Tỉnh'],
            ['code' => 'bac-ninh', 'name' => 'Bắc Ninh', 'type' => 'Tỉnh'],
            ['code' => 'hung-yen', 'name' => 'Hưng Yên', 'type' => 'Tỉnh'],
            ['code' => 'ninh-binh', 'name' => 'Ninh Bình', 'type' => 'Tỉnh'],
            ['code' => 'quang-ninh', 'name' => 'Quảng Ninh', 'type' => 'Tỉnh'],
            ['code' => 'lang-son', 'name' => 'Lạng Sơn', 'type' => 'Tỉnh'],
            ['code' => 'dien-bien', 'name' => 'Điện Biên', 'type' => 'Tỉnh'],
            ['code' => 'son-la', 'name' => 'Sơn La', 'type' => 'Tỉnh'],
            ['code' => 'lai-chau', 'name' => 'Lai Châu', 'type' => 'Tỉnh'],
            ['code' => 'quang-tri', 'name' => 'Quảng Trị', 'type' => 'Tỉnh'],
            ['code' => 'quang-ngai', 'name' => 'Quảng Ngãi', 'type' => 'Tỉnh'],
            ['code' => 'gia-lai', 'name' => 'Gia Lai', 'type' => 'Tỉnh'],
            ['code' => 'khanh-hoa', 'name' => 'Khánh Hòa', 'type' => 'Tỉnh'],
            ['code' => 'lam-dong', 'name' => 'Lâm Đồng', 'type' => 'Tỉnh'],
            ['code' => 'dak-lak', 'name' => 'Đắk Lắk', 'type' => 'Tỉnh'],
            ['code' => 'dong-nai', 'name' => 'Đồng Nai', 'type' => 'Tỉnh'],
            ['code' => 'tay-ninh', 'name' => 'Tây Ninh', 'type' => 'Tỉnh'],
            ['code' => 'vinh-long', 'name' => 'Vĩnh Long', 'type' => 'Tỉnh'],
            ['code' => 'dong-thap', 'name' => 'Đồng Tháp', 'type' => 'Tỉnh'],
            ['code' => 'ca-mau', 'name' => 'Cà Mau', 'type' => 'Tỉnh'],
            ['code' => 'an-giang', 'name' => 'An Giang', 'type' => 'Tỉnh'],
            ['code' => 'thanh-hoa', 'name' => 'Thanh Hóa', 'type' => 'Tỉnh'],
            ['code' => 'nghe-an', 'name' => 'Nghệ An', 'type' => 'Tỉnh'],
            ['code' => 'ha-tinh', 'name' => 'Hà Tĩnh', 'type' => 'Tỉnh'],
            ['code' => 'quang-binh', 'name' => 'Quảng Bình', 'type' => 'Tỉnh'],
        ];

        foreach ($provinces as $province) {
            Province::query()->updateOrCreate(
                ['code' => $province['code']],
                $province,
            );
        }
    }
}
