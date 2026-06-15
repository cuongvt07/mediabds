<?php

namespace Database\Seeders;

use App\Models\RealEstateListing;
use App\Models\SiteBanner;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DockerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['phone' => User::ADMIN_PHONE],
            [
                'name' => 'Admin Nhà Trọ',
                'email' => 'admin@nhatrosaigon.local',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        SiteBanner::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Banner site nhà trọ',
                'subtitle' => 'Slider quản lý riêng, không lấy từ tin đăng',
                'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1600&q=85',
                'link_url' => null,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        SiteSetting::firstOrCreate(['key' => 'site_name'], ['value' => 'NHÀ TRỌ SÀI GÒN']);
        SiteSetting::firstOrCreate(['key' => 'logo_url'], ['value' => null]);

        $rooms = [
            ['Studio cửa sổ lớn gần trung tâm', 'studio', 'full', 'Quận 1', 'Phường Bến Nghé', 4800000, 'photo-1505693416388-ac5ce068fe85'],
            ['Phòng có gác thoáng, giờ giấc tự do', 'loft', 'basic', 'Quận 3', 'Phường Võ Thị Sáu', 3900000, 'photo-1522708323590-d24dbb6b0267'],
            ['Duplex nội thất mới, khu dân cư yên tĩnh', 'duplex', 'full', 'Quận 7', 'Phường Tân Phong', 6200000, 'photo-1560448204-e02f11c3d0e2'],
            ['Studio ban công riêng gần sân bay', 'balcony', 'full', 'Quận Tân Bình', 'Phường 2', 5200000, 'photo-1560185007-c5ca9d2c014d'],
            ['Phòng có gác gần Đại học Văn Lang', 'loft', 'basic', 'Quận Bình Thạnh', 'Phường 25', 3600000, 'photo-1560448075-bb485b067938'],
            ['Studio tối giản gần Landmark 81', 'studio', 'empty', 'Quận Bình Thạnh', 'Phường 22', 4500000, 'photo-1493809842364-78817add7ffb'],
            ['Duplex rộng rãi cho hai người', 'duplex', 'full', 'Thành phố Thủ Đức', 'Phường Thảo Điền', 6800000, 'photo-1600607687920-4e2a09cf159d'],
            ['Phòng ban công đón nắng, có máy lạnh', 'balcony', 'basic', 'Quận Gò Vấp', 'Phường 10', 4100000, 'photo-1600566753086-00f18fb6b3ea'],
            ['Studio mới xây gần chợ Tân Định', 'studio', 'full', 'Quận 1', 'Phường Tân Định', 5000000, 'photo-1600210492486-724fe5c67fb0'],
            ['Phòng có gác gần khu Etown', 'loft', 'empty', 'Quận Tân Bình', 'Phường 13', 3300000, 'photo-1600566753190-17f0baa2a6c3'],
            ['Studio đầy đủ nội thất gần Lotte Mart', 'studio', 'full', 'Quận 7', 'Phường Tân Quy', 4700000, 'photo-1600210491892-03d54c0aaf87'],
            ['Phòng ban công riêng, có bếp', 'balcony', 'basic', 'Quận Phú Nhuận', 'Phường 9', 4400000, 'photo-1616486338812-3dadae4b4ace'],
            ['Duplex phong cách hiện đại', 'duplex', 'full', 'Quận 10', 'Phường 12', 5900000, 'photo-1615874694520-474822394e73'],
            ['Studio giá tốt gần công viên Gia Định', 'studio', 'basic', 'Quận Gò Vấp', 'Phường 3', 3200000, 'photo-1618221195710-dd6b41faaea6'],
        ];

        $amenitySets = [
            ['bed', 'mattress', 'wardrobe', 'wifi', 'air_conditioner', 'fridge'],
            ['bed', 'mattress', 'wifi', 'loft'],
            ['bed', 'mattress', 'wardrobe', 'elevator', 'wifi', 'air_conditioner', 'kitchen', 'water_heater', 'fridge'],
            ['bed', 'mattress', 'wifi', 'air_conditioner', 'kitchen', 'fridge'],
            ['bed', 'mattress', 'wifi', 'loft', 'water_heater'],
            ['wifi', 'kitchen'],
            ['bed', 'mattress', 'wardrobe', 'elevator', 'wifi', 'air_conditioner', 'kitchen', 'water_heater'],
            ['bed', 'mattress', 'wifi', 'air_conditioner'],
            ['bed', 'mattress', 'wardrobe', 'wifi', 'air_conditioner', 'fridge'],
            ['wifi', 'loft'],
            ['bed', 'mattress', 'wardrobe', 'elevator', 'wifi', 'air_conditioner', 'kitchen'],
            ['bed', 'mattress', 'wifi', 'kitchen', 'water_heater'],
            ['bed', 'mattress', 'wardrobe', 'wifi', 'air_conditioner', 'water_heater'],
            ['bed', 'mattress', 'wifi'],
        ];

        foreach ($rooms as $index => [$title, $roomType, $furnish, $district, $ward, $price, $photo]) {
            RealEstateListing::updateOrCreate(
                ['code' => 'NT-DK-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'title' => $title,
                    'type' => 'Cho thuê',
                    'property_type' => 115,
                    'room_type' => $roomType,
                    'furnish' => $furnish,
                    'province_id' => '79',
                    'province_name' => 'Hồ Chí Minh',
                    'district_id' => 'docker-district-' . $index,
                    'district_name' => $district,
                    'ward_id' => 'docker-ward-' . $index,
                    'ward_name' => $ward,
                    'price' => $price,
                    'price_unit' => '2',
                    'bedrooms' => 1,
                    'toilets' => 1,
                    'description' => 'Phòng sạch sẽ, khu vực an ninh, giờ giấc tự do. Liên hệ quản lý để xác nhận phòng trống và đặt lịch xem phòng.',
                    'images' => ["https://images.unsplash.com/{$photo}?auto=format&fit=crop&w=1200&q=85"],
                    'amenities' => $amenitySets[$index] ?? ['wifi'],
                    'contact_type' => 'Quản lý',
                    'contact_phone' => '0981847977',
                    'user_id' => $admin->id,
                    'is_sold' => false,
                    'status' => 'active',
                    'published_at' => now()->subHours($index),
                ]
            );
        }
    }
}
