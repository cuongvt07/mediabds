<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/**
 * Seed 3 bài Bất động sản + 3 bài Xe cho block "Tin tức" trang chủ.
 * Idempotent theo slug (chạy lại không tạo trùng). Chạy:
 *   php artisan db:seed --class=Database\\Seeders\\BlogPostSeeder
 */
class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->posts() as $i => $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                array_merge($post, [
                    'status'       => 'published',
                    'published_at' => now()->subDays(count($this->posts()) - $i),
                ]),
            );
        }
    }

    private function posts(): array
    {
        $cover = fn (string $id) => "https://images.unsplash.com/photo-{$id}?w=1200&q=80&auto=format&fit=crop";

        return [
            // ───────────── BẤT ĐỘNG SẢN ─────────────
            [
                'slug'            => 'kinh-nghiem-thue-nha-tphcm-nhung-luu-y-truoc-khi-dat-coc',
                'title'           => 'Kinh nghiệm thuê nhà: những lưu ý quan trọng trước khi đặt cọc',
                'excerpt'         => 'Thuê nhà không khó nếu bạn nắm rõ các lưu ý về hợp đồng, đặt cọc, kiểm tra nội thất và pháp lý chủ nhà. Tổng hợp kinh nghiệm thực chiến giúp bạn tránh mất tiền oan.',
                'cover_image'     => $cover('1560448204-e02f11c3d0e2'),
                'author_name'     => 'Phú Thịnh Land',
                'category_tag'    => 'Kinh nghiệm',
                'type'            => 'bds',
                'tags'            => ['thuê nhà', 'kinh nghiệm', 'hợp đồng'],
                'reading_minutes' => 6,
                'content'         => <<<'MD'
## Mở đầu

Thị trường thuê nhà rất sôi động nhưng cũng tiềm ẩn rủi ro nếu người thuê thiếu kinh nghiệm. Trước khi đặt cọc, hãy kiểm tra đủ những điều dưới đây.

## 1. Kiểm tra giấy tờ chủ nhà

Yêu cầu chủ nhà cung cấp **sổ hồng/sổ đỏ** hoặc giấy uỷ quyền cho thuê. Đối chiếu CCCD với tên trên sổ để chắc chắn bạn làm việc với đúng người.

## 2. Đọc kỹ hợp đồng

Hợp đồng nên có đủ: thời hạn, giá thuê, phương thức thanh toán, điều khoản tăng giá, điều khoản chấm dứt sớm và trách nhiệm sửa chữa.

## 3. Đặt cọc hợp lý

Mức cọc thường 1–2 tháng tiền thuê. **Không đặt cọc khi chưa xem nhà thật** hoặc qua trung gian không rõ ràng.

## 4. Kiểm tra nội thất chi tiết

Liệt kê toàn bộ đồ đạc kèm tình trạng và ký xác nhận hai bên. Chụp ảnh làm bằng chứng khi trả nhà.

## Kết luận

Chuẩn bị kỹ giúp bạn thuê được nơi ở ưng ý và tránh tranh chấp về sau.
MD,
            ],
            [
                'slug'            => 'so-hong-so-do-khac-nhau-the-nao',
                'title'           => 'Sổ hồng và sổ đỏ khác nhau thế nào? Điều người mua nhà cần biết',
                'excerpt'         => 'Sổ hồng, sổ đỏ là cách gọi dân gian của các loại giấy chứng nhận quyền sử dụng đất và tài sản. Hiểu đúng bản chất giúp bạn giao dịch an toàn.',
                'cover_image'     => $cover('1600585154340-be6161a56a0c'),
                'author_name'     => 'Phú Thịnh Land',
                'category_tag'    => 'Pháp lý',
                'type'            => 'bds',
                'tags'            => ['pháp lý', 'sổ hồng', 'sổ đỏ'],
                'reading_minutes' => 5,
                'content'         => <<<'MD'
## Bản chất của "sổ đỏ" và "sổ hồng"

Đây là cách gọi theo màu bìa. Hiện nay Nhà nước cấp chung một mẫu **Giấy chứng nhận quyền sử dụng đất, quyền sở hữu nhà ở và tài sản khác gắn liền với đất** (bìa hồng).

## Vì sao cần phân biệt

- **Sổ đỏ** (mẫu cũ): thiên về quyền sử dụng đất.
- **Sổ hồng** (mẫu cũ): thiên về quyền sở hữu nhà ở.

Khi mua bán, điều quan trọng không phải màu bìa mà là **thông tin pháp lý bên trong**: đúng chủ, đúng thửa, không tranh chấp, không thế chấp.

## Lời khuyên

Luôn kiểm tra thông tin quy hoạch và tình trạng thế chấp tại văn phòng đăng ký đất đai trước khi xuống tiền.
MD,
            ],
            [
                'slug'            => 'cach-dinh-gia-bat-dong-san-truoc-khi-mua-ban',
                'title'           => 'Cách định giá bất động sản hợp lý trước khi mua bán',
                'excerpt'         => 'Định giá đúng giúp bạn không mua hớ, không bán hụt. Bốn phương pháp tham chiếu đơn giản ai cũng áp dụng được.',
                'cover_image'     => $cover('1568605114967-8130f3a36994'),
                'author_name'     => 'Phú Thịnh Land',
                'category_tag'    => 'Thị trường',
                'type'            => 'bds',
                'tags'            => ['định giá', 'thị trường', 'đầu tư'],
                'reading_minutes' => 5,
                'content'         => <<<'MD'
## Vì sao định giá lại quan trọng

Giá bất động sản phụ thuộc vị trí, pháp lý, tiện ích và thời điểm. Định giá sai khiến bạn mua hớ hoặc bán hụt hàng trăm triệu.

## 1. So sánh giao dịch tương đồng

Tham chiếu 3–5 bất động sản cùng khu vực, cùng loại, mới giao dịch gần đây.

## 2. Theo giá thuê

Ước tính giá trị dựa trên dòng tiền cho thuê hằng năm và tỷ suất lợi nhuận khu vực.

## 3. Theo chi phí xây dựng

Giá đất + chi phí xây dựng còn lại sau khấu hao — phù hợp với nhà riêng lẻ.

## 4. Tham khảo môi giới uy tín

Người am hiểu khu vực cho bạn khoảng giá sát thực tế nhất.
MD,
            ],

            // ───────────── XE ─────────────
            [
                'slug'            => 'kinh-nghiem-mua-o-to-cu-tranh-mua-phai-xe-ngap-nuoc',
                'title'           => 'Kinh nghiệm mua ô tô cũ: tránh mua phải xe ngập nước, tua công-tơ-mét',
                'excerpt'         => 'Mua ô tô cũ tiết kiệm nhưng nhiều rủi ro. Những dấu hiệu nhận biết xe ngập nước, xe tua đồng hồ và mẹo kiểm tra nhanh trước khi xuống tiền.',
                'cover_image'     => $cover('1503376780353-7e6692767b70'),
                'author_name'     => 'Phú Thịnh Land',
                'category_tag'    => 'Kinh nghiệm xe',
                'type'            => 'xe',
                'tags'            => ['ô tô cũ', 'kinh nghiệm', 'mua xe'],
                'reading_minutes' => 6,
                'content'         => <<<'MD'
## Vì sao cần cẩn trọng với xe cũ

Xe cũ giá tốt nhưng có thể giấu lỗi nặng. Nắm vài dấu hiệu dưới đây giúp bạn tránh "tiền mất tật mang".

## 1. Dấu hiệu xe ngập nước

- Mùi ẩm mốc trong khoang nội thất.
- Vết bùn/gỉ sét ở gầm ghế, dây điện, ốc vít.
- Đèn pha, taplo có hơi nước.

## 2. Kiểm tra tua công-tơ-mét

Đối chiếu số km với độ mòn vô-lăng, bàn đạp, ghế da và lịch sử bảo dưỡng của hãng.

## 3. Kiểm tra khung gầm và máy

Xem có vết hàn lại, sơn lại bất thường không. Nổ máy nghe tiếng động lạ, kiểm tra khói xả.

## Lời khuyên

Nên mang theo thợ tin cậy hoặc mang xe tới garage kiểm tra tổng thể trước khi đặt cọc.
MD,
            ],
            [
                'slug'            => 'bao-duong-o-to-dinh-ky-cac-moc-km-quan-trong',
                'title'           => 'Bảo dưỡng ô tô định kỳ: các mốc km quan trọng đừng bỏ lỡ',
                'excerpt'         => 'Bảo dưỡng đúng mốc giúp xe bền, tiết kiệm nhiên liệu và giữ giá khi bán lại. Bảng mốc km và hạng mục cần làm cho xe phổ thông.',
                'cover_image'     => $cover('1486262715619-67b85e0b08d3'),
                'author_name'     => 'Phú Thịnh Land',
                'category_tag'    => 'Bảo dưỡng',
                'type'            => 'xe',
                'tags'            => ['bảo dưỡng', 'ô tô', 'chăm sóc xe'],
                'reading_minutes' => 5,
                'content'         => <<<'MD'
## Vì sao bảo dưỡng định kỳ lại quan trọng

Bảo dưỡng đúng lịch giúp động cơ bền, tiết kiệm nhiên liệu và **giữ giá** khi bán lại.

## Các mốc km cơ bản

- **5.000 km**: thay dầu máy, kiểm tra lốp, phanh.
- **10.000 km**: thay lọc gió động cơ, lọc gió điều hoà.
- **20.000 km**: thay dầu hộp số (tuỳ loại), kiểm tra hệ thống treo.
- **40.000 km**: thay dầu phanh, nước làm mát, bugi.

## Mẹo giữ xe bền

Chạy đúng loại nhiên liệu, không để bình xăng quá cạn, và rửa gầm định kỳ để chống gỉ.
MD,
            ],
            [
                'slug'            => 'xe-may-hay-o-to-nen-mua-loai-nao-cho-gia-dinh',
                'title'           => 'Xe máy hay ô tô: nên mua loại nào cho gia đình?',
                'excerpt'         => 'Bài toán ngân sách, nhu cầu di chuyển và chi phí vận hành. Gợi ý lựa chọn phương tiện phù hợp cho từng hoàn cảnh gia đình.',
                'cover_image'     => $cover('1449965408869-eaa3f722e40d'),
                'author_name'     => 'Phú Thịnh Land',
                'category_tag'    => 'Tư vấn',
                'type'            => 'xe',
                'tags'            => ['ô tô', 'xe máy', 'tư vấn'],
                'reading_minutes' => 4,
                'content'         => <<<'MD'
## Cân nhắc theo nhu cầu

Không có lựa chọn "tốt nhất" cho mọi người — chỉ có lựa chọn **phù hợp nhất** với hoàn cảnh của bạn.

## Khi nào nên chọn xe máy

- Di chuyển nội thành, quãng đường ngắn.
- Ngân sách hạn chế, ưu tiên linh hoạt, dễ gửi xe.

## Khi nào nên chọn ô tô

- Gia đình có trẻ nhỏ, người lớn tuổi.
- Thường đi xa, cần che mưa nắng và an toàn hơn.

## Đừng quên chi phí vận hành

Ô tô kéo theo phí gửi xe, bảo hiểm, bảo dưỡng và nhiên liệu cao hơn. Hãy tính tổng chi phí sở hữu trước khi quyết định.
MD,
            ],
        ];
    }
}
