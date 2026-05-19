<?php

namespace App\Services;

use App\Models\RealEstateListing;
use App\Models\RealEstateListingSale;
use App\Models\Customer;
use App\Models\CustomerWork;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatbotAnalyticsService
{
    // Chuẩn hoá giá về VNĐ (đơn vị nhỏ nhất) để có thể so sánh / cộng dồn
    public const UNIT_TO_VND = [
        'Tỷ' => 1_000_000_000,
        'Tỉ' => 1_000_000_000,
        'Triệu' => 1_000_000,
        'VNĐ/tháng' => 1,
    ];

    protected function priceToVnd($price, $unit): float
    {
        $multiplier = self::UNIT_TO_VND[$unit] ?? 1;
        return (float) $price * $multiplier;
    }

    protected function formatVnd(float $vnd): string
    {
        if ($vnd >= 1_000_000_000) return number_format($vnd / 1_000_000_000, 2, '.', '') . ' Tỷ';
        if ($vnd >= 1_000_000) return number_format($vnd / 1_000_000, 0, '.', '') . ' Triệu';
        return number_format($vnd, 0, ',', '.') . ' VNĐ';
    }

    // ─────────────────────────────────────────────────────────────────────
    // 1. aggregate_listings_stats — Thống kê tin đăng theo nhiều chiều
    // ─────────────────────────────────────────────────────────────────────
    public function aggregateListingsStats(array $args): array
    {
        $groupBy = $args['group_by'] ?? 'property_type';
        $metric  = $args['metric'] ?? 'count';
        $filters = $args['filters'] ?? [];

        $q = RealEstateListing::query();
        if (isset($filters['is_sold'])) $q->where('is_sold', $filters['is_sold']);
        if (!empty($filters['type'])) $q->where('type', $filters['type']);
        if (!empty($filters['province'])) $q->where('province_name', 'like', "%{$filters['province']}%");
        if (!empty($filters['district'])) $q->where('district_name', 'like', "%{$filters['district']}%");
        if (!empty($filters['date_from'])) $q->where('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $q->where('created_at', '<=', $filters['date_to']);

        // [FIX] Dùng raw SQL string thuần — luôn alias `as group_key`
        $priceCase = "price * CASE price_unit WHEN 'Tỷ' THEN 1000000000 WHEN 'Tỉ' THEN 1000000000 WHEN 'Triệu' THEN 1000000 ELSE 1 END";
        $groupFieldMap = [
            'property_type' => '`property_type`',
            'type' => '`type`',
            'province' => '`province_name`',
            'district' => '`district_name`',
            'direction' => '`direction`',
            'is_sold' => '`is_sold`',
            'month' => "DATE_FORMAT(created_at, '%Y-%m')",
            'week' => "DATE_FORMAT(created_at, '%Y-%v')",
            'year' => "DATE_FORMAT(created_at, '%Y')",
            'price_range' => "CASE
                WHEN price_unit IN ('Triệu','Triệu/tháng','VNĐ/tháng') THEN '< 1 Tỷ'
                WHEN price < 2 AND price_unit IN ('Tỷ','Tỉ') THEN '1-2 Tỷ'
                WHEN price < 5 AND price_unit IN ('Tỷ','Tỉ') THEN '2-5 Tỷ'
                WHEN price < 10 AND price_unit IN ('Tỷ','Tỉ') THEN '5-10 Tỷ'
                WHEN price >= 10 AND price_unit IN ('Tỷ','Tỉ') THEN '10+ Tỷ'
                ELSE 'Thoả thuận' END",
            'area_range' => "CASE
                WHEN area < 30 THEN '< 30 m2'
                WHEN area < 60 THEN '30-60 m2'
                WHEN area < 100 THEN '60-100 m2'
                WHEN area < 200 THEN '100-200 m2'
                ELSE '200+ m2' END",
        ];

        if (!isset($groupFieldMap[$groupBy])) {
            return ['status' => 'error', 'message' => "group_by không hợp lệ: {$groupBy}"];
        }
        $groupSql = $groupFieldMap[$groupBy];

        $selectMetric = match ($metric) {
            'count'         => 'COUNT(*) as metric_value',
            'avg_price_vnd' => "AVG({$priceCase}) as metric_value",
            'sum_price_vnd' => "SUM({$priceCase}) as metric_value",
            'avg_area'      => 'AVG(area) as metric_value',
            'sum_area'      => 'SUM(area) as metric_value',
            default         => 'COUNT(*) as metric_value',
        };

        $rows = $q->selectRaw("{$groupSql} as group_key, {$selectMetric}")
            ->groupBy(DB::raw($groupSql))
            ->orderByDesc('metric_value')
            ->limit($args['limit'] ?? 15)
            ->get();

        // Map kết quả về dạng dễ đọc
        $data = $rows->map(function ($r) use ($metric) {
            $value = (float) ($r->metric_value ?? 0);
            $key = $r->group_key ?? 'N/A';
            // Đặc biệt cho is_sold: 0/1 → "Đã bán"/"Còn trống"
            $display = match ($metric) {
                'avg_price_vnd', 'sum_price_vnd' => $this->formatVnd($value),
                'avg_area', 'sum_area' => number_format($value, 1) . ' m²',
                default => (string) (int) $value,
            };
            return ['group' => $key, 'value' => $value, 'display' => $display];
        });

        return [
            'status' => 'success',
            'group_by' => $groupBy,
            'metric' => $metric,
            'total_groups' => $data->count(),
            'data' => $data->values()->all(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2. compare_listings — So sánh nhiều tin
    // ─────────────────────────────────────────────────────────────────────
    public function compareListings(array $args): array
    {
        $ids = $args['listing_ids'] ?? [];
        if (count($ids) < 2 || count($ids) > 5) {
            return ['status' => 'error', 'message' => 'Cần 2-5 tin đăng để so sánh.'];
        }

        $listings = RealEstateListing::whereIn('id', $ids)->get();
        if ($listings->count() < 2) {
            return ['status' => 'error', 'message' => 'Không đủ tin đăng hợp lệ.'];
        }

        $rows = $listings->map(function ($l) {
            $priceVnd = $this->priceToVnd($l->price, $l->price_unit);
            $pricePerSqm = $l->area > 0 ? $priceVnd / $l->area : 0;
            return [
                'id' => $l->id,
                'code' => $l->code,
                'title' => $l->title,
                'price_display' => number_format($l->price, 2, '.', '') . ' ' . $l->price_unit,
                'price_vnd' => $priceVnd,
                'area' => $l->area,
                'price_per_sqm_vnd' => $pricePerSqm,
                'price_per_sqm_display' => $this->formatVnd($pricePerSqm) . '/m²',
                'address' => $l->address,
                'bedrooms' => $l->bedrooms,
                'toilets' => $l->toilets,
                'floors' => $l->floors,
                'direction' => $l->direction,
                'is_sold' => (bool) $l->is_sold,
            ];
        });

        // Thống kê so sánh
        $cheapest = $rows->sortBy('price_vnd')->first();
        $mostExpensive = $rows->sortByDesc('price_vnd')->first();
        $largest = $rows->sortByDesc('area')->first();
        $bestValue = $rows->where('price_per_sqm_vnd', '>', 0)->sortBy('price_per_sqm_vnd')->first();

        return [
            'status' => 'success',
            'format_hint' => 'Hiển thị mỗi tin bằng [LISTING:ID], sau đó tóm tắt insight: tin nào rẻ nhất, đắt nhất, giá/m² tốt nhất.',
            'listings' => $rows->values()->all(),
            'insights' => [
                'cheapest_id' => $cheapest['id'] ?? null,
                'most_expensive_id' => $mostExpensive['id'] ?? null,
                'largest_id' => $largest['id'] ?? null,
                'best_value_per_sqm_id' => $bestValue['id'] ?? null,
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. top_performers — Top CTV theo metric & period
    // ─────────────────────────────────────────────────────────────────────
    public function topPerformers(array $args): array
    {
        $metric = $args['metric'] ?? 'revenue';
        $period = $args['period'] ?? 'all'; // all|this_month|last_month|this_quarter|this_year
        $limit  = min((int)($args['limit'] ?? 5), 20);

        [$from, $to] = $this->resolvePeriod($period);

        switch ($metric) {
            case 'revenue':
                // Doanh thu = sum(received_amount) từ members + sum(revenue_amount) sales không có member
                $members = DB::table('real_estate_listing_sale_members as m')
                    ->join('real_estate_listing_sales as s', 's.id', '=', 'm.sale_id')
                    ->join('users as u', 'u.id', '=', 'm.user_id')
                    ->when($from, fn($q) => $q->where('s.sold_at', '>=', $from))
                    ->when($to,   fn($q) => $q->where('s.sold_at', '<=', $to))
                    ->groupBy('u.id', 'u.name')
                    ->select('u.id', 'u.name', DB::raw('SUM(m.received_amount) as total'));

                $legacy = DB::table('real_estate_listing_sales as s')
                    ->join('users as u', 'u.id', '=', 's.sold_by_user_id')
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))->from('real_estate_listing_sale_members as m')->whereColumn('m.sale_id', 's.id');
                    })
                    ->when($from, fn($q) => $q->where('s.sold_at', '>=', $from))
                    ->when($to,   fn($q) => $q->where('s.sold_at', '<=', $to))
                    ->groupBy('u.id', 'u.name')
                    ->select('u.id', 'u.name', DB::raw('SUM(s.revenue_amount) as total'));

                $rows = $members->unionAll($legacy)->get()
                    ->groupBy('id')
                    ->map(fn($g) => ['id' => $g->first()->id, 'name' => $g->first()->name, 'total' => (float) $g->sum('total')])
                    ->sortByDesc('total')->take($limit)->values()
                    ->map(fn($r) => array_merge($r, ['display' => $this->formatVnd($r['total'])]));
                break;

            case 'sales_count':
                $rows = DB::table('real_estate_listing_sales as s')
                    ->join('users as u', 'u.id', '=', 's.sold_by_user_id')
                    ->when($from, fn($q) => $q->where('s.sold_at', '>=', $from))
                    ->when($to,   fn($q) => $q->where('s.sold_at', '<=', $to))
                    ->groupBy('u.id', 'u.name')
                    ->select('u.id', 'u.name', DB::raw('COUNT(*) as total'))
                    ->orderByDesc('total')->limit($limit)->get()
                    ->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'total' => (int) $r->total, 'display' => $r->total . ' tin chốt']);
                break;

            case 'listings_posted':
                $rows = DB::table('real_estate_listings as l')
                    ->join('users as u', 'u.id', '=', 'l.user_id')
                    ->when($from, fn($q) => $q->where('l.created_at', '>=', $from))
                    ->when($to,   fn($q) => $q->where('l.created_at', '<=', $to))
                    ->groupBy('u.id', 'u.name')
                    ->select('u.id', 'u.name', DB::raw('COUNT(*) as total'))
                    ->orderByDesc('total')->limit($limit)->get()
                    ->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'total' => (int) $r->total, 'display' => $r->total . ' tin đăng']);
                break;

            case 'customers_assigned':
                $rows = DB::table('customers as c')
                    ->join('users as u', 'u.id', '=', 'c.assigned_user_id')
                    ->groupBy('u.id', 'u.name')
                    ->select('u.id', 'u.name', DB::raw('COUNT(*) as total'))
                    ->orderByDesc('total')->limit($limit)->get()
                    ->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'total' => (int) $r->total, 'display' => $r->total . ' khách']);
                break;

            case 'invites':
                $rows = DB::table('users as u')
                    ->join('users as inv', 'inv.invited_by_user_id', '=', 'u.id')
                    ->groupBy('u.id', 'u.name')
                    ->select('u.id', 'u.name', DB::raw('COUNT(inv.id) as total'))
                    ->orderByDesc('total')->limit($limit)->get()
                    ->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'total' => (int) $r->total, 'display' => $r->total . ' lời mời']);
                break;

            default:
                return ['status' => 'error', 'message' => "Metric không hợp lệ: {$metric}. Hỗ trợ: revenue, sales_count, listings_posted, customers_assigned, invites."];
        }

        return [
            'status' => 'success',
            'metric' => $metric,
            'period' => $period,
            'period_from' => $from,
            'period_to' => $to,
            'data' => $rows->values()->all(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4. revenue_report — Báo cáo doanh thu theo thời kỳ
    // ─────────────────────────────────────────────────────────────────────
    public function revenueReport(array $args): array
    {
        $granularity = $args['granularity'] ?? 'month'; // day|week|month|quarter|year
        $period = $args['period'] ?? 'this_year';
        $userId = $args['user_id'] ?? null;

        [$from, $to] = $this->resolvePeriod($period);

        $dateFormat = match ($granularity) {
            'day'     => "DATE_FORMAT(s.sold_at, '%Y-%m-%d')",
            'week'    => "DATE_FORMAT(s.sold_at, '%Y-W%v')",
            'month'   => "DATE_FORMAT(s.sold_at, '%Y-%m')",
            'quarter' => "CONCAT(YEAR(s.sold_at), '-Q', QUARTER(s.sold_at))",
            'year'    => "YEAR(s.sold_at)",
            default   => "DATE_FORMAT(s.sold_at, '%Y-%m')",
        };

        $q = DB::table('real_estate_listing_sales as s')
            ->whereNotNull('s.sold_at')
            ->when($from, fn($q) => $q->where('s.sold_at', '>=', $from))
            ->when($to,   fn($q) => $q->where('s.sold_at', '<=', $to))
            ->when($userId, fn($q) => $q->where('s.sold_by_user_id', $userId))
            ->groupBy(DB::raw($dateFormat))
            ->select([
                DB::raw("{$dateFormat} as bucket"),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(s.actual_price) as total_gmv'),
                DB::raw('SUM(s.revenue_amount) as total_revenue'),
                DB::raw('SUM(s.bonus_amount) as total_bonus'),
                DB::raw('SUM(s.net_received_amount) as total_net'),
            ])
            ->orderBy('bucket');

        $rows = $q->get()->map(fn($r) => [
            'bucket' => $r->bucket,
            'sales_count' => (int) $r->sales_count,
            'gmv' => (float) $r->total_gmv,
            'gmv_display' => $this->formatVnd((float) $r->total_gmv),
            'revenue' => (float) $r->total_revenue,
            'revenue_display' => $this->formatVnd((float) $r->total_revenue),
            'bonus' => (float) $r->total_bonus,
            'net' => (float) $r->total_net,
            'net_display' => $this->formatVnd((float) $r->total_net),
        ]);

        $totals = [
            'sales_count' => $rows->sum('sales_count'),
            'gmv' => $rows->sum('gmv'),
            'gmv_display' => $this->formatVnd($rows->sum('gmv')),
            'revenue' => $rows->sum('revenue'),
            'revenue_display' => $this->formatVnd($rows->sum('revenue')),
            'net' => $rows->sum('net'),
            'net_display' => $this->formatVnd($rows->sum('net')),
        ];

        return [
            'status' => 'success',
            'granularity' => $granularity,
            'period' => $period,
            'period_from' => $from,
            'period_to' => $to,
            'user_id' => $userId,
            'totals' => $totals,
            'data' => $rows->values()->all(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // 5. market_analysis — Phân tích giá theo khu vực
    // ─────────────────────────────────────────────────────────────────────
    public function marketAnalysis(array $args): array
    {
        $province = $args['province'] ?? null;
        $district = $args['district'] ?? null;
        $propertyType = $args['property_type'] ?? null;
        $onlyActive = $args['only_active'] ?? true;

        $q = RealEstateListing::query();
        if ($onlyActive) $q->where('is_sold', false);
        if ($province) $q->where('province_name', 'like', "%{$province}%");
        if ($district) $q->where('district_name', 'like', "%{$district}%");
        if ($propertyType) $q->where('property_type', 'like', "%{$propertyType}%");

        $listings = $q->whereNotNull('price')->whereNotNull('area')->where('area', '>', 0)
            ->get(['id', 'price', 'price_unit', 'area']);

        if ($listings->isEmpty()) {
            return ['status' => 'success', 'message' => 'Không có dữ liệu khớp tiêu chí.', 'count' => 0];
        }

        $pricesVnd = $listings->map(fn($l) => $this->priceToVnd($l->price, $l->price_unit));
        $pricePerSqm = $listings->map(fn($l) => $this->priceToVnd($l->price, $l->price_unit) / max($l->area, 1));

        $sorted = $pricesVnd->sort()->values();
        $median = $sorted->count() % 2 === 0
            ? ($sorted[intdiv($sorted->count(), 2) - 1] + $sorted[intdiv($sorted->count(), 2)]) / 2
            : $sorted[intdiv($sorted->count(), 2)];

        $sortedSqm = $pricePerSqm->sort()->values();
        $medianSqm = $sortedSqm->count() % 2 === 0
            ? ($sortedSqm[intdiv($sortedSqm->count(), 2) - 1] + $sortedSqm[intdiv($sortedSqm->count(), 2)]) / 2
            : $sortedSqm[intdiv($sortedSqm->count(), 2)];

        return [
            'status' => 'success',
            'filters' => compact('province', 'district', 'propertyType', 'onlyActive'),
            'count' => $listings->count(),
            'price' => [
                'min' => $this->formatVnd($pricesVnd->min()),
                'max' => $this->formatVnd($pricesVnd->max()),
                'avg' => $this->formatVnd($pricesVnd->avg()),
                'median' => $this->formatVnd($median),
            ],
            'price_per_sqm' => [
                'min' => $this->formatVnd($pricePerSqm->min()) . '/m²',
                'max' => $this->formatVnd($pricePerSqm->max()) . '/m²',
                'avg' => $this->formatVnd($pricePerSqm->avg()) . '/m²',
                'median' => $this->formatVnd($medianSqm) . '/m²',
            ],
            'area' => [
                'min' => number_format($listings->min('area'), 1) . ' m²',
                'max' => number_format($listings->max('area'), 1) . ' m²',
                'avg' => number_format($listings->avg('area'), 1) . ' m²',
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // 6. customer_funnel — Phễu khách hàng
    // ─────────────────────────────────────────────────────────────────────
    public function customerFunnel(array $args): array
    {
        $userId = $args['user_id'] ?? null;

        $byStatus = Customer::query()
            ->when($userId, fn($q) => $q->where('assigned_user_id', $userId))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status')->toArray();

        $worksByProgress = CustomerWork::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->selectRaw('progress, COUNT(*) as total')
            ->groupBy('progress')->pluck('total', 'progress')->toArray();

        $stale = Customer::query()
            ->when($userId, fn($q) => $q->where('assigned_user_id', $userId))
            ->whereDoesntHave('works', function ($q) {
                $q->where('work_date', '>=', now()->subDays(14));
            })
            ->count();

        $totalCustomers = array_sum($byStatus);
        $pendingTasks = CustomerWork::when($userId, fn($q) => $q->where('user_id', $userId))
            ->where('progress', '!=', 'Hoàn thành')->count();

        // Top 5 customer chưa được chăm sóc lâu nhất
        $neglected = Customer::query()
            ->when($userId, fn($q) => $q->where('assigned_user_id', $userId))
            ->select('customers.*')
            ->leftJoin('customer_works', 'customer_works.customer_id', '=', 'customers.id')
            ->groupBy('customers.id')
            ->orderByRaw('COALESCE(MAX(customer_works.work_date), customers.created_at) ASC')
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'status' => Customer::STATUS_LABELS[$c->status] ?? $c->status,
            ]);

        return [
            'status' => 'success',
            'scope_user_id' => $userId,
            'total_customers' => $totalCustomers,
            'by_status' => $byStatus,
            'works_by_progress' => $worksByProgress,
            'pending_tasks' => $pendingTasks,
            'stale_14d_count' => $stale,
            'most_neglected' => $neglected->all(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // 7. bulk_listings_summary — Lấy thông tin tối thiểu cho nhiều IDs
    // ─────────────────────────────────────────────────────────────────────
    public function bulkListingsSummary(array $args): array
    {
        $ids = $args['listing_ids'] ?? [];
        if (empty($ids)) return ['status' => 'error', 'message' => 'Thiếu listing_ids.'];

        $listings = RealEstateListing::whereIn('id', array_slice($ids, 0, 30))
            ->get(['id', 'code', 'title', 'price', 'price_unit', 'area', 'address', 'is_sold', 'type']);

        return [
            'status' => 'success',
            'format_hint' => 'Hiển thị bằng [LISTING:ID].',
            'data' => $listings->map(fn($l) => [
                'id' => $l->id,
                'code' => $l->code,
                'title' => $l->title,
                'price_display' => number_format($l->price, 2, '.', '') . ' ' . $l->price_unit,
                'area' => $l->area . ' m²',
                'address' => $l->address,
                'status' => $l->is_sold ? 'Đã bán' : 'Còn trống',
                'type' => $l->type,
            ])->all(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helper: Resolve period name → [from, to]
    // ─────────────────────────────────────────────────────────────────────
    protected function resolvePeriod(string $period): array
    {
        $now = Carbon::now();
        return match ($period) {
            'today'        => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday'    => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'this_week'    => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'last_week'    => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'this_month'   => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month'   => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'last_quarter' => [$now->copy()->subQuarter()->startOfQuarter(), $now->copy()->subQuarter()->endOfQuarter()],
            'this_year'    => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year'    => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'last_30d'     => [$now->copy()->subDays(30), $now],
            'last_90d'     => [$now->copy()->subDays(90), $now],
            'all'          => [null, null],
            default        => [null, null],
        };
    }
}
