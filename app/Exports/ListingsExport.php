<?php

namespace App\Exports;

use App\Models\RealEstateListing;
use App\Models\CtvRank;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ListingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $filters;

    private const PROPERTY_TYPES = [
        110 => 'Bất động sản khác',
        102 => 'Biệt thự',
        103 => 'Căn hộ – chung cư',
        104 => 'Đất',
        105 => 'Đất nền dự án',
        106 => 'Mặt tiền',
        107 => 'Nhà mặt phố',
        111 => 'Nhà mặt phố (LG 4M-5M)',
        108 => 'Nhà riêng',
        109 => 'Trang trại',
        112 => 'Khách sạn',
        113 => 'Nhà nghỉ',
        114 => 'Homestay',
    ];

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = RealEstateListing::orderBy('created_at', 'desc')->orderBy('id', 'desc');

        // Apply filters (similar to RealEstateListing livewire component)
        $user = auth()->user();
        if ($user && !$user->isAdmin()) {
            if (!empty($user->property_types)) {
                $query->whereIn('property_type', $user->property_types);
            }

            $invitesCount = $user->sentInviteLogs()->count();
            $ctvRank = CtvRank::where('min_invites', '<=', $invitesCount)
                ->orderBy('min_invites', 'desc')
                ->first();

            if ($ctvRank) {
                if (!empty($ctvRank->min_price)) {
                    $query->whereRaw("(CASE WHEN price_unit = 'Triệu' THEN CAST(price AS DECIMAL(15,2)) * 1000000 ELSE CAST(price AS DECIMAL(15,2)) * 1000000000 END) >= ?", [$ctvRank->min_price]);
                }
                if (!empty($ctvRank->max_price)) {
                    $query->whereRaw("(CASE WHEN price_unit = 'Triệu' THEN CAST(price AS DECIMAL(15,2)) * 1000000 ELSE CAST(price AS DECIMAL(15,2)) * 1000000000 END) <= ?", [$ctvRank->max_price]);
                }
            } else {
                $query->whereId(0);
            }
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('address', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        if (!empty($this->filters['price_min'])) {
            $query->where('price', '>=', str_replace('.', '', $this->filters['price_min']));
        }
        if (!empty($this->filters['price_max'])) {
            $query->where('price', '<=', str_replace('.', '', $this->filters['price_max']));
        }

        if (!empty($this->filters['province'])) {
            $query->where('province_id', $this->filters['province']);
        }
        if (!empty($this->filters['district'])) {
            $query->where('district_id', $this->filters['district']);
        }
        if (!empty($this->filters['ward'])) {
            $query->where('ward_id', $this->filters['ward']);
        }
        if (!empty($this->filters['property_type'])) {
            $query->where('property_type', $this->filters['property_type']);
        }
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if ($this->filters['is_sold'] !== null && $this->filters['is_sold'] !== '') {
            $query->where('is_sold', $this->filters['is_sold']);
        }

        if (!empty($this->filters['month'])) {
            $query->whereMonth('created_at', $this->filters['month']);
        }
        if (!empty($this->filters['year'])) {
            $query->whereYear('created_at', $this->filters['year']);
        }

        if (!empty($this->filters['phone'])) {
            $phones = array_filter(explode(',', $this->filters['phone']));
            if (!empty($phones)) {
                $query->where(function ($q) use ($phones) {
                    foreach ($phones as $p) {
                        $normalizedPhone = preg_replace('/[^0-9]/', '', $p);
                        if (!empty($normalizedPhone)) {
                            $q->orWhereRaw("REPLACE(REPLACE(REPLACE(contact_phone, '.', ''), '-', ''), ' ', '') LIKE ?", ['%' . $normalizedPhone . '%']);
                        }
                    }
                });
            }
        }

        return $query->with('user:id,name')->get();
    }

    public function headings(): array
    {
        return [
            ['BÁO CÁO TỔNG HỢP TIN ĐĂNG'], // Row 1: Title
            [
                'Mã tin',
                'Tiêu đề',
                'Loại tin',
                'Loại bất động sản',
                'Tỉnh',
                'Quận',
                'Phường',
                'Địa chỉ',
                'Diện tích (m²)',
                'Giá tiền',
                'Đơn vị giá',
                'Số tầng',
                'Phòng ngủ',
                'Toilet',
                'Hướng',
                'Mặt tiền (m)',
                'Đường (m)',
                'Chủ/Môi giới',
                'Số điện thoại',
                'Mã mở khoá',
                'Link YouTube',
                'Link Facebook',
                'Link TikTok',
                'Link Google Map',
                'Ảnh đại diện',
                'Danh sách ảnh',
                'Trạng thái',
                'Ngày tin',
                'Người tạo (ID)',
                'Người tạo',
            ] // Row 2: Headers
        ];
    }

    public function map($listing): array
    {
        $addressParts = array_filter([$listing->address, $listing->ward_name, $listing->district_name, $listing->province_name]);
        $fullAddress = implode(', ', $addressParts);

        $priceStr = '';
        if ($listing->price !== null && is_numeric($listing->price)) {
            $priceStr = number_format((float) $listing->price, 0, ',', '.');
        }

        $formatDecimal = function ($value) {
            if ($value === null || $value === '') {
                return '';
            }
            if (!is_numeric($value)) {
                return (string) $value;
            }
            return number_format((float) $value, 2, ',', '.');
        };

        $propertyTypeLabel = self::PROPERTY_TYPES[$listing->property_type] ?? 'Khác';

        $images = $listing->images;
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = $decoded ?: [];
        }
        if (!is_array($images)) {
            $images = [];
        }
        $imageList = implode(' | ', array_filter($images));

        $publishedAt = Carbon::parse($listing->created_at)->format('d/m/Y');

        $status = $listing->is_sold ? 'Đã bán' : 'Chưa bán';

        return [
            $listing->code,
            $listing->title,
            $listing->type,
            $propertyTypeLabel,
            $listing->province_name,
            $listing->district_name,
            $listing->ward_name,
            $listing->address,
            $formatDecimal($listing->area),
            $priceStr,
            $listing->price_unit,
            $listing->floors,
            $listing->bedrooms,
            $listing->toilets,
            $listing->direction,
            $formatDecimal($listing->front_width),
            $formatDecimal($listing->road_width),
            $listing->contact_type,
            $listing->contact_phone,
            $listing->house_password,
            $listing->youtube_link,
            $listing->facebook_link,
            $listing->tiktok_link,
            $listing->google_map_link,
            $listing->avatar,
            $imageList,
            $status,
            $publishedAt,
            $listing->user_id,
            optional($listing->user)->name,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge title cells
        $sheet->mergeCells('A1:AD1');

        $styleArray = [
            // Style for Title (Row 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F81BD'], // Blue-ish background
                ],
            ],
            // Style for Headers (Row 2)
            2 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF808080'], // Gray background
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ]
            ],
        ];

        // Apply borders to all data cells
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        if ($highestRow > 2) {
            $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Wrap text for address column
            $sheet->getStyle('B3:B' . $highestRow)->getAlignment()->setWrapText(true);
        }

        // Set explicit row heights
        $sheet->getRowDimension(1)->setRowHeight(30);

        return $styleArray;
    }

    public function title(): string
    {
        return 'Tổng hợp tin đăng';
    }
}
