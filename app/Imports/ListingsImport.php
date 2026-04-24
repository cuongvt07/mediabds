<?php

namespace App\Imports;

use App\Models\RealEstateListing;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class ListingsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Simple mapping
        return new RealEstateListing([
            'title'         => $row['tieu_de'] ?? $row['title'] ?? 'Tin đăng chưa có tiêu đề',
            'type'          => $row['loai'] ?? $row['type'] ?? 'Cần bán',
            'property_type' => $row['loai_bds'] ?? $row['property_type'] ?? 110,
            'address'       => $row['dia_chi'] ?? $row['address'] ?? null,
            'area'          => $row['dien_tich'] ?? $row['area'] ?? 0,
            'price'         => $row['gia'] ?? $row['price'] ?? 0,
            'price_unit'    => $row['don_vi_gia'] ?? $row['price_unit'] ?? 'Tỷ',
            'description'   => $row['mo_ta'] ?? $row['description'] ?? null,
            'contact_phone' => $row['phone'] ?? $row['contact_phone'] ?? null,
            'user_id'       => auth()->id(),
            'code'          => '#IM' . strtoupper(Str::random(5)),
            'province_id'   => $row['ma_tinh'] ?? $row['province_id'] ?? null,
        ]);
    }
}
