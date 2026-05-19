<?php

namespace App\Services;

use App\Models\User;
use App\Models\RealEstateListing;
use App\Models\Customer;
use App\Models\CustomerWork;
use App\Models\RealEstateListingSale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChatbotUserProfileService
{
    /**
     * Snapshot của user — dùng để inject vào system prompt
     * và áp dụng scope cho các tool call.
     *
     * Cache 60s để không truy vấn lặp khi user gửi nhiều tin nhanh.
     */
    public function snapshot(User $user): array
    {
        return Cache::remember("chatbot_profile_{$user->id}", 60, function () use ($user) {
            $isAdmin = $user->isAdmin();

            $myListings = RealEstateListing::where('user_id', $user->id);
            $myListingsTotal = (clone $myListings)->count();
            $myListingsActive = (clone $myListings)->where('is_sold', false)->count();
            $myListingsSold = (clone $myListings)->where('is_sold', true)->count();

            $myCustomers = Customer::where('assigned_user_id', $user->id);
            $myCustomersTotal = (clone $myCustomers)->count();
            $myCustomersStale = (clone $myCustomers)
                ->whereDoesntHave('works', fn($q) => $q->where('work_date', '>=', now()->subDays(14)))
                ->count();

            $myPendingTasks = CustomerWork::where('user_id', $user->id)
                ->where('progress', '!=', 'Hoàn thành')->count();

            // Doanh thu tháng này (dùng cùng logic với User::getTotalRevenueAttribute)
            $monthStart = now()->startOfMonth();
            $monthEnd   = now()->endOfMonth();

            $revFromMembers = DB::table('real_estate_listing_sale_members as m')
                ->join('real_estate_listing_sales as s', 's.id', '=', 'm.sale_id')
                ->where('m.user_id', $user->id)
                ->whereBetween('s.sold_at', [$monthStart, $monthEnd])
                ->sum('m.received_amount');

            $revLegacy = RealEstateListingSale::where('sold_by_user_id', $user->id)
                ->whereBetween('sold_at', [$monthStart, $monthEnd])
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))->from('real_estate_listing_sale_members as m')
                        ->whereColumn('m.sale_id', 'real_estate_listing_sales.id');
                })
                ->sum('revenue_amount');

            $monthRevenue = (float) ($revFromMembers + $revLegacy);

            $rank = $user->rank;
            $invitesCount = $user->invitees()->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'is_admin' => $isAdmin,
                'role' => $isAdmin ? 'Admin' : 'CTV',
                'property_types_allowed' => $user->property_types ?? [],
                'mine_listings_total' => $myListingsTotal,
                'mine_listings_active' => $myListingsActive,
                'mine_listings_sold' => $myListingsSold,
                'mine_customers_total' => $myCustomersTotal,
                'mine_customers_stale_14d' => $myCustomersStale,
                'mine_pending_tasks' => $myPendingTasks,
                'mine_revenue_this_month' => $monthRevenue,
                'mine_revenue_total' => (float) $user->total_revenue,
                'rank_name' => $rank?->name,
                'invites_count' => $invitesCount,
            ];
        });
    }

    /**
     * Convert snapshot thành block văn bản inject vào system prompt.
     * Bot phải dùng các fact này để cá nhân hoá câu trả lời và áp scope đúng.
     */
    public function buildPromptBlock(array $snapshot): string
    {
        $role = $snapshot['role'];
        $isAdmin = $snapshot['is_admin'];

        $revMonth = $this->fmtVnd($snapshot['mine_revenue_this_month']);
        $revTotal = $this->fmtVnd($snapshot['mine_revenue_total']);

        $rank = $snapshot['rank_name'] ?? 'Chưa xếp hạng';
        $allowedTypes = !empty($snapshot['property_types_allowed'])
            ? implode(', ', $snapshot['property_types_allowed']) : 'Tất cả';

        $scopeRule = $isAdmin
            ? "Bạn là Admin → có quyền xem TOÀN HỆ THỐNG. Khi user hỏi 'doanh thu', 'thống kê', không cần scope user."
            : "Bạn là CTV → mặc định mọi câu hỏi không nói rõ phạm vi sẽ ĐƯỢC HIỂU LÀ HỎI VỀ DỮ LIỆU CỦA HỌ. VD: 'doanh thu tháng' → doanh thu của họ, không phải toàn hệ thống. 'khách của tôi' → assigned_user_id = #{$snapshot['id']}. 'tin của tôi' → user_id = #{$snapshot['id']}.";

        return <<<TXT
[HỒ SƠ NGƯỜI ĐANG HỎI]
- Họ tên: {$snapshot['name']} (User ID #{$snapshot['id']})
- Vai trò: {$role}
- Loại BĐS được phép quản lý: {$allowedTypes}
- Tin đang quản lý: {$snapshot['mine_listings_total']} (còn trống: {$snapshot['mine_listings_active']}, đã bán: {$snapshot['mine_listings_sold']})
- Khách hàng đang chăm: {$snapshot['mine_customers_total']} (bỏ quên >14 ngày: {$snapshot['mine_customers_stale_14d']})
- Task tồn đọng: {$snapshot['mine_pending_tasks']}
- Doanh thu tháng này: {$revMonth}
- Tổng doanh thu: {$revTotal}
- Rank: {$rank} · Đã mời: {$snapshot['invites_count']} người

[QUY TẮC SCOPE — RẤT QUAN TRỌNG]
{$scopeRule}
TXT;
    }

    /**
     * Áp scope user vào args của tool call (chỉ cho CTV — Admin xem được tất cả).
     * Gọi TRƯỚC khi executeTool — đảm bảo CTV không vô tình thấy data người khác.
     */
    public function applyScope(User $user, string $toolName, array $args): array
    {
        if ($user->isAdmin()) return $args;

        switch ($toolName) {
            case 'revenue_report':
                // CTV chỉ xem được doanh thu của họ — luôn override
                $args['user_id'] = $user->id;
                break;

            case 'top_performers':
                // CTV không cần xem rank toàn hệ thống — cho phép xem nhưng đánh dấu scope
                // (không override vì đây là xếp hạng, không phải data riêng tư)
                break;

            case 'customer_funnel':
                // CTV chỉ thấy phễu của họ
                $args['user_id'] = $user->id;
                break;

            case 'search_customers':
                // Nếu CTV không phải Admin → giới hạn khách họ phụ trách
                // (tạm thời comment: nếu cần, uncomment dòng này)
                // $args['_force_assigned_user_id'] = $user->id;
                break;

            case 'get_user_performance':
                // CTV chỉ xem performance của chính họ
                if (!empty($args['user_id']) && (int)$args['user_id'] !== $user->id) {
                    $args['user_id'] = $user->id;
                    $args['_scope_forced'] = true;
                }
                break;
        }
        return $args;
    }

    protected function fmtVnd(float $vnd): string
    {
        if ($vnd >= 1e9) return number_format($vnd / 1e9, 2) . ' Tỷ';
        if ($vnd >= 1e6) return number_format($vnd / 1e6, 0) . ' Triệu';
        return number_format($vnd, 0, ',', '.') . ' VNĐ';
    }
}
