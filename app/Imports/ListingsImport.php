<?php

namespace App\Imports;

use App\Models\RealEstateListing;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ListingsImport implements ToModel, WithHeadingRow
{
    protected $locationData = null;

    protected function getLocationData()
    {
        if ($this->locationData === null) {
            $path = 'private/locations/all_vietnam.json';
            if (Storage::disk('local')->exists($path)) {
                $this->locationData = json_decode(Storage::disk('local')->get($path), true);
            } else {
                $this->locationData = [];
            }
        }
        return $this->locationData;
    }

    protected function normalizeName($name)
    {
        if (!$name) return '';
        $name = Str::lower(trim($name));
        $remove = ['thành phố', 'tỉnh', 'quận', 'huyện', 'thị xã', 'phường', 'xã', 'thị trấn', 'tp.', 'q.', 'h.', 'p.', 'x.'];
        foreach ($remove as $r) {
            $name = str_replace($r, '', $name);
        }
        return trim($name);
    }

    protected function mapLocation($provinceName, $districtName = null, $wardName = null)
    {
        $data = $this->getLocationData();
        $normProvince = $this->normalizeName($provinceName);
        
        $result = ['province_id' => null, 'district_id' => null, 'ward_id' => null];

        foreach ($data as $pId => $pInfo) {
            if ($this->normalizeName($pInfo['name']) === $normProvince) {
                $result['province_id'] = $pId;
                
                if ($districtName && isset($pInfo['districts'])) {
                    $normDistrict = $this->normalizeName($districtName);
                    foreach ($pInfo['districts'] as $dId => $dInfo) {
                        if ($this->normalizeName($dInfo['name']) === $normDistrict) {
                            $result['district_id'] = $dId;
                            
                            if ($wardName && isset($dInfo['wards'])) {
                                $normWard = $this->normalizeName($wardName);
                                foreach ($dInfo['wards'] as $wId => $wName) {
                                    if ($this->normalizeName($wName) === $normWard) {
                                        $result['ward_id'] = $wId;
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
                break;
            }
        }
        return $result;
    }

    protected function mapPropertyType($typeName)
    {
        $types = [
            102 => ['biệt thự', 'villa'],
            103 => ['căn hộ', 'chung cư', 'apartment'],
            104 => ['đất', 'land'],
            105 => ['đất nền', 'đất dự án'],
            106 => ['mặt tiền'],
            107 => ['nhà mặt phố'],
            108 => ['nhà riêng', 'nhà phố'],
            109 => ['trang trại'],
            112 => ['khách sạn', 'hotel'],
            113 => ['nhà nghỉ'],
            114 => ['homestay'],
            115 => ['nhà trọ', 'phòng trọ'],
            110 => ['khác']
        ];

        $norm = Str::lower(trim($typeName));
        foreach ($types as $id => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($norm, $kw)) return $id;
            }
        }
        return 110;
    }

    protected function mapReporter($name)
    {
        if (!$name) return null;
        $user = \App\Models\User::where('name', 'like', '%' . trim($name) . '%')->first();
        return $user ? $user->id : null;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $locations = $this->mapLocation(
            $row['tinh_thanh_pho'] ?? null,
            $row['quan_huyen'] ?? null,
            $row['phuong_xa'] ?? null
        );

        $isSold = isset($row['trang_thai']) && Str::contains(Str::lower($row['trang_thai']), 'đã bán');

        return new RealEstateListing([
            'title'               => $row['tieu_de'] ?? 'Tin đăng chưa có tiêu đề',
            'type'                => $row['loai_giao_dich'] ?? 'Cần bán',
            'property_type'       => $this->mapPropertyType($row['loai_bat_dong_san'] ?? ''),
            'address'             => $row['dia_chi_chi_tiet'] ?? null,
            'area'                => $row['dien_tich_m2'] ?? 0,
            'price'               => (float)str_replace(['.', ','], '', $row['gia'] ?? 0),
            'price_unit'          => $row['don_vi_gia'] ?? 'Tỷ',
            'province_id'         => $locations['province_id'],
            'district_id'         => $locations['district_id'],
            'ward_id'             => $locations['ward_id'],
            'front_width'         => $row['mat_tien_m'] ?? 0,
            'road_width'          => $row['duong_truoc_nha_m'] ?? 0,
            'direction'           => $row['huong'] ?? null,
            'floors'              => $row['so_tang'] ?? 0,
            'bedrooms'            => $row['phong_ngu'] ?? 0,
            'toilets'             => $row['so_toilet'] ?? 0,
            'contact_phone'       => $row['so_dien_thoai'] ?? null,
            'contact_type'        => $row['loai_lien_he'] ?? 'Chính chủ',
            'description'         => $row['mo_ta_chi_ tiet'] ?? null,
            'house_password'      => $row['mat_khau_xem_nha'] ?? null,
            'google_map_link'     => $row['link_google_map'] ?? null,
            'youtube_link'        => $row['link_youtube'] ?? null,
            'youtube_link_short'  => $row['link_youtube_short'] ?? null,
            'facebook_link'       => $row['link_facebook'] ?? null,
            'facebook_video_link' => $row['link_facebook_video'] ?? null,
            'tiktok_link'         => $row['link_tiktok'] ?? null,
            'is_sold'             => $isSold,
            'reporter_id'         => $this->mapReporter($row['nguoi_bao_tin'] ?? null),
            'user_id'             => auth()->id(),
            'code'                => '#IM' . strtoupper(Str::random(5)),
        ]);
    }
}
