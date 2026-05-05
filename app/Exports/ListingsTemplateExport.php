<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ListingsTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            [
                'Biệt thự mini hẻm 6m Lê Văn Sỹ',
                'Cần bán',
                '110',
                '123 Lê Văn Sỹ, P.1, Q.Tân Bình',
                '100',
                '15',
                'Tỷ',
                'Nhà đẹp mới xây, dọn vào ở ngay...',
                '0909123456',
                '79'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'tieu_de',
            'loai',
            'loai_bds',
            'dia_chi',
            'dien_tich',
            'gia',
            'don_vi_gia',
            'mo_ta',
            'phone',
            'ma_tinh'
        ];
    }

    public function title(): string
    {
        return 'Template Import Tin Đăng';
    }
}
