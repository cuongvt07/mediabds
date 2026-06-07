<?php

namespace App\Http\Controllers\Api;

use App\Livewire\RealEstateListing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocationApiController extends BaseApiController
{
    public function index()
    {
        $locationData = $this->locationData();

        $locations = collect(RealEstateListing::PROVINCES)
            ->map(function (string $name, string $code) use ($locationData) {
                $districts = collect($locationData[$code]['districts'] ?? $this->fallbackDistricts($code))
                    ->map(function ($district, string $districtCode) use ($code) {
                        $districtName = is_array($district) ? ($district['name'] ?? '') : (string) $district;
                        $wards = is_array($district) ? ($district['wards'] ?? []) : [];

                        return [
                            'code' => $districtCode,
                            'name' => $districtName,
                            'slug' => Str::slug($districtName),
                            'cityCode' => $code,
                            'wards' => collect($wards)
                                ->map(function ($wardName, $wardCode) use ($code, $districtCode) {
                                    return [
                                        'code' => (string) $wardCode,
                                        'name' => (string) $wardName,
                                        'slug' => Str::slug((string) $wardName),
                                        'cityCode' => $code,
                                        'districtCode' => $districtCode,
                                    ];
                                })
                                ->values()
                                ->all(),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'code' => $code,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'districts' => $districts,
                ];
            })
            ->sortBy(function ($province) {
                return $province['code'] === '52' ? '00' : $province['name'];
            })
            ->values()
            ->all();

        return $this->ok($locations);
    }

    private function locationData(): array
    {
        $path = 'locations/all_vietnam.json';
        if (! Storage::disk('local')->exists($path)) {
            return [];
        }

        $data = json_decode(Storage::disk('local')->get($path), true);
        return is_array($data) ? $data : [];
    }

    private function fallbackDistricts(string $provinceCode): array
    {
        if ($provinceCode !== '52') {
            return [];
        }

        return [
            '540' => ['name' => 'Thành phố Quy Nhơn', 'wards' => []],
            '542' => ['name' => 'Huyện An Lão', 'wards' => []],
            '543' => ['name' => 'Thị xã Hoài Nhơn', 'wards' => []],
            '544' => ['name' => 'Huyện Hoài Ân', 'wards' => []],
            '545' => ['name' => 'Huyện Phù Mỹ', 'wards' => []],
            '546' => ['name' => 'Huyện Vĩnh Thạnh', 'wards' => []],
            '547' => ['name' => 'Huyện Tây Sơn', 'wards' => []],
            '548' => ['name' => 'Huyện Phù Cát', 'wards' => []],
            '549' => ['name' => 'Thị xã An Nhơn', 'wards' => []],
            '550' => ['name' => 'Huyện Tuy Phước', 'wards' => []],
            '551' => ['name' => 'Huyện Vân Canh', 'wards' => []],
        ];
    }
}
