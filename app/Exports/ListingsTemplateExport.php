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
                'Biệt thự mini hẻm 6m Lê Văn Sỹ', // Tiêu đề
                'Cần bán',                       // Loại giao dịch
                'Nhà phố',                       // Loại bất động sản
                '123 Lê Văn Sỹ, P.1, Q.Tân Bình', // Địa chỉ chi tiết
                '100',                           // Diện tích (m2)
                '15.5',                          // Giá
                'Tỷ',                            // Đơn vị giá
                'TP. Hồ Chí Minh',               // Tỉnh/Thành phố
                'Quận Tân Bình',                 // Quận/Huyện
                'Phường 1',                      // Phường/Xã
                '8.5',                           // Mặt tiền (m)
                '6',                             // Đường trước nhà (m)
                'Đông Nam',                      // Hướng
                '3',                             // Số tầng
                '4',                             // Số phòng ngủ
                '4',                             // Số toilet
                '0909123456',                    // Số điện thoại
                'Chính chủ',                     // Loại liên hệ
                'Nhà mới xây, kiến trúc hiện đại, khu an ninh...', // Mô tả chi tiết
                '123456',                        // Mật khẩu xem nhà
                'https://goo.gl/maps/...',       // Link Google Map
                'https://youtube.com/watch?v=...', // Link Youtube
                'https://youtube.com/shorts/...', // Link Youtube Short
                'https://facebook.com/...',      // Link Facebook
                'https://facebook.com/watch/...', // Link Facebook Video
                'https://tiktok.com/@...',       // Link Tiktok
                'Chưa bán',                      // Trạng thái (Chưa bán/Đã bán)
                'Nguyễn Văn A'                   // Người báo tin (Tên nhân viên)
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Tiêu đề',
            'Loại giao dịch',
            'Loại bất động sản',
            'Địa chỉ chi tiết',
            'Diện tích (m2)',
            'Giá',
            'Đơn vị giá',
            'Tỉnh/Thành phố',
            'Quận/Huyện',
            'Phường/Xã',
            'Mặt tiền (m)',
            'Đường trước nhà (m)',
            'Hướng',
            'Số tầng',
            'Số phòng ngủ',
            'Số toilet',
            'Số điện thoại',
            'Loại liên hệ',
            'Mô tả chi tiết',
            'Mật khẩu xem nhà',
            'Link Google Map',
            'Link Youtube',
            'Link Youtube Short',
            'Link Facebook',
            'Link Facebook Video',
            'Link Tiktok',
            'Trạng thái',
            'Người báo tin'
        ];
    }

    public function title(): string
    {
        return 'Template Import Tin Đăng';
    }
}
