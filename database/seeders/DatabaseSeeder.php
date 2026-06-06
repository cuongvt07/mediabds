<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RealEstateListing;
use App\Models\Customer;
use App\Models\CtvRank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PublicWebsiteSeeder::class);

        // 1. Create Admin
        $admin = User::updateOrCreate(
            ['phone' => '0981847977'],
            [
                'name' => 'Antigravity Admin',
                'email' => 'admin@antigravity.vn',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create some CTVs
        $ctvs = [];
        for ($i = 1; $i <= 5; $i++) {
            $ctvs[] = User::create([
                'name' => "CTV " . fake()->name(),
                'phone' => '090000000' . $i,
                'email' => "ctv{$i}@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        // 3. Create Ctv Ranks if needed
        $ranks = ['Bạc', 'Vàng', 'Kim Cương'];
        foreach ($ranks as $rankName) {
            CtvRank::updateOrCreate(['name' => $rankName]);
        }

        // 4. Create Real Estate Listings
        $provinces = ['Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng'];
        $types = ['Cần bán', 'Cho thuê'];
        $propertyTypes = ['Nhà phố', 'Căn hộ', 'Đất nền'];

        foreach (range(1, 20) as $i) {
            $user = $i <= 10 ? $admin : $ctvs[array_rand($ctvs)];
            
            RealEstateListing::create([
                'title' => fake()->sentence(6),
                'type' => $types[array_rand($types)],
                'property_type' => $propertyTypes[array_rand($propertyTypes)],
                'address' => fake()->address(),
                'area' => fake()->randomFloat(2, 50, 500),
                'price' => fake()->randomFloat(2, 1, 50),
                'price_unit' => 'Tỷ',
                'description' => fake()->paragraphs(3, true),
                'user_id' => $user->id,
                'is_sold' => fake()->boolean(20),
                'images' => ['https://images.unsplash.com/photo-1564013799919-ab600027ffc6'],
            ]);
        }

        // 5. Create Customers
        $customerStatuses = ['khach_mua_o', 'dau_tu', 'mua', 'ban', 'dich_vu'];
        foreach (range(1, 15) as $i) {
            $user = $ctvs[array_rand($ctvs)];
            Customer::create([
                'code' => 'KH' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'status' => $customerStatuses[array_rand($customerStatuses)],
                'assigned_user_id' => $user->id,
                'budget_from' => fake()->numberBetween(1000000000, 5000000000),
                'budget_to' => fake()->numberBetween(5000000000, 15000000000),
                'description' => fake()->sentence(),
            ]);
        }

        echo "✅ Seeding completed! \n";
        echo "Admin Login: 0981847977 / 12345678 \n";
    }
}
