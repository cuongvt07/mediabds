<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class PublicWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        BlogPost::updateOrCreate(
            ['slug' => 'kinh-nghiem-thue-nha-tphcm-10-luu-y'],
            [
                'title' => 'Kinh nghiệm thuê nhà tại TP.HCM: 10 điều cần lưu ý',
                'excerpt' => 'Checklist thực tế giúp người thuê nhà kiểm tra hợp đồng, đặt cọc, chi phí phát sinh và pháp lý trước khi xuống tiền.',
                'content' => "## Kiểm tra chủ nhà và hợp đồng\n\nYêu cầu xem giấy tờ chủ nhà hoặc giấy ủy quyền cho thuê. Hợp đồng nên ghi rõ thời hạn, giá thuê, cọc, lịch thanh toán và điều khoản kết thúc sớm.\n\n## Kiểm tra nhà trước khi cọc\n\nChụp lại hiện trạng nội thất, đồng hồ điện nước, khu vực gửi xe và các chi phí quản lý. Không đặt cọc nếu chưa xem nhà thật hoặc bên nhận cọc không rõ ràng.\n\n## Tổng kết\n\nMột buổi kiểm tra kỹ có thể giúp bạn tránh nhiều tranh chấp sau khi nhận nhà.",
                'cover_image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&q=80&auto=format&fit=crop',
                'author_name' => 'BDS Việt',
                'category_tag' => 'Kinh nghiệm',
                'tags' => ['thuê nhà', 'TP.HCM', 'hợp đồng'],
                'reading_minutes' => 5,
                'status' => 'published',
                'published_at' => now()->subDays(7),
            ]
        );

        BlogPost::updateOrCreate(
            ['slug' => 'check-phap-ly-du-an-can-ho-truoc-khi-mua'],
            [
                'title' => 'Cách check pháp lý dự án căn hộ trước khi mua',
                'excerpt' => 'Các giấy tờ tối thiểu người mua căn hộ nên kiểm tra trước khi ký thỏa thuận đặt cọc hoặc hợp đồng mua bán.',
                'content' => "## Vì sao phải kiểm tra pháp lý?\n\nPháp lý dự án quyết định khả năng bàn giao, sang tên và vay ngân hàng. Người mua nên yêu cầu chủ đầu tư hoặc sàn phân phối cung cấp hồ sơ rõ ràng.\n\n## Checklist cơ bản\n\nKiểm tra quyền sử dụng đất, quy hoạch 1/500, giấy phép xây dựng, văn bản đủ điều kiện bán nhà hình thành trong tương lai và bảo lãnh ngân hàng.\n\n## Tổng kết\n\nNếu khoản mua lớn, nên nhờ luật sư hoặc chuyên viên tín dụng rà soát hồ sơ trước khi đặt cọc.",
                'cover_image' => 'https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=1200&q=80&auto=format&fit=crop',
                'author_name' => 'BDS Việt',
                'category_tag' => 'Pháp lý',
                'tags' => ['pháp lý', 'căn hộ', 'mua nhà'],
                'reading_minutes' => 6,
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ]
        );
    }
}
