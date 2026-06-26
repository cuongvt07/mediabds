<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use App\Models\VehicleListing;
use Illuminate\Database\Seeder;

class VehicleBrandSeeder extends Seeder
{
    public function run(): void
    {
        $seed = function (array $names, string $type) {
            foreach ($names as $i => $name) {
                VehicleBrand::updateOrCreate(
                    ['name' => $name, 'vehicle_type' => $type],
                    ['sort_order' => $i, 'is_active' => true]
                );
            }
        };

        $seed(VehicleListing::CAR_BRANDS, 'car');
        $seed(VehicleListing::MOTORBIKE_BRANDS, 'motorbike');

        echo "✅ Đã seed hãng xe (ô tô + xe máy).\n";
    }
}
