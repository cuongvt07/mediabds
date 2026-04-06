<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\RealEstateListingSale;
use App\Models\UserInvite;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CtvStatistics extends Component
{
    public $filterYear;
    public $filterQuarter = 'all';

    public $overallStats = [
        'total_revenue' => 0,
        'total_bonus' => 0,
        'total_deals' => 0,
        'new_ctvs' => 0,
    ];

    public function mount()
    {
        $this->filterYear = date('Y');
        $this->loadData();
    }

    public function updatedFilterYear() { $this->loadData(); }
    public function updatedFilterQuarter() { $this->loadData(); }

    public function loadData()
    {
        // 1. Total Revenue from ALL sale members (The unified model)
        $memberRevenue = \DB::table('real_estate_listing_sale_members')
            ->join('real_estate_listing_sales', 'real_estate_listing_sale_members.sale_id', '=', 'real_estate_listing_sales.id')
            ->when($this->filterYear, fn($q) => $q->whereYear('real_estate_listing_sales.sold_at', $this->filterYear))
            ->when($this->filterQuarter !== 'all', function($q) {
                $months = match((int)$this->filterQuarter) {
                    1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12], default => []
                };
                return $q->whereIn(DB::raw('MONTH(real_estate_listing_sales.sold_at)'), $months);
            })
            ->sum('received_amount');

        // 2. Legacy Revenue (Only for sales that have NO members)
        $legacyRevenue = \DB::table('real_estate_listing_sales')
            ->when($this->filterYear, fn($q) => $q->whereYear('sold_at', $this->filterYear))
            ->when($this->filterQuarter !== 'all', function($q) {
                $months = match((int)$this->filterQuarter) {
                    1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12], default => []
                };
                return $q->whereIn(DB::raw('MONTH(sold_at)'), $months);
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('real_estate_listing_sale_members')
                    ->whereColumn('real_estate_listing_sale_members.sale_id', 'real_estate_listing_sales.id');
            })
            ->sum('revenue_amount');

        $this->overallStats['total_revenue'] = (float)($memberRevenue + $legacyRevenue);

        // Sub-queries for other stats
        $salesBaseQuery = RealEstateListingSale::query()
            ->when($this->filterYear, fn($q) => $q->whereYear('sold_at', $this->filterYear))
            ->when($this->filterQuarter !== 'all', function($q) {
                $months = match((int)$this->filterQuarter) {
                    1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12], default => []
                };
                return $q->whereIn(DB::raw('MONTH(sold_at)'), $months);
            });

        $this->overallStats['total_bonus'] = (clone $salesBaseQuery)->sum('bonus_amount');
        $this->overallStats['total_deals'] = (clone $salesBaseQuery)->count();

        // Recruitment Stats
        $this->overallStats['new_ctvs'] = UserInvite::query()
            ->when($this->filterYear, fn($q) => $q->whereYear('created_at', $this->filterYear))
            ->when($this->filterQuarter !== 'all', function($q) {
                $months = match((int)$this->filterQuarter) {
                    1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12], default => []
                };
                return $q->whereIn(DB::raw('MONTH(created_at)'), $months);
            })
            ->count();
    }

    public function render()
    {
        // Monthly trend (Using simplified combined revenue query)
        $monthlyRevenue = DB::table('real_estate_listing_sales')
            ->whereYear('sold_at', $this->filterYear)
            ->select(DB::raw('MONTH(sold_at) as month'), DB::raw('SUM(revenue_amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Top 10 Leaderboard
        $topCtvs = DB::table('users')
            ->join(DB::raw('(
                SELECT m.user_id, m.received_amount as amount, s.sold_at
                FROM real_estate_listing_sale_members m
                JOIN real_estate_listing_sales s ON m.sale_id = s.id
                UNION ALL
                SELECT s.sold_by_user_id as user_id, s.revenue_amount as amount, s.sold_at 
                FROM real_estate_listing_sales s
                WHERE NOT EXISTS (SELECT 1 FROM real_estate_listing_sale_members m2 WHERE m2.sale_id = s.id)
            ) as combined'), 'users.id', '=', 'combined.user_id')
            ->select('users.id', 'users.name', DB::raw('SUM(combined.amount) as revenue'))
            ->when($this->filterYear, fn($q) => $q->whereYear('combined.sold_at', $this->filterYear))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('livewire.ctv-statistics', [
            'monthlyRevenue' => $monthlyRevenue,
            'topCtvs' => $topCtvs,
        ])->layout('components.layouts.app', ['title' => 'Thống kê tổng hợp CTV']);
    }
}
