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
        $salesQuery = RealEstateListingSale::query()
            ->when($this->filterYear, fn($q) => $q->whereYear('sold_at', $this->filterYear))
            ->when($this->filterQuarter !== 'all', function($q) {
                $months = match((int)$this->filterQuarter) {
                    1 => [1,2,3],
                    2 => [4,5,6],
                    3 => [7,8,9],
                    4 => [10,11,12],
                    default => []
                };
                return $q->whereIn(DB::raw('MONTH(sold_at)'), $months);
            });

        $this->overallStats['total_revenue'] = (clone $salesQuery)->sum('revenue_amount');
        $this->overallStats['total_bonus'] = (clone $salesQuery)->sum('bonus_amount');
        $this->overallStats['total_deals'] = (clone $salesQuery)->count();

        $inviteQuery = UserInvite::query()
            ->when($this->filterYear, fn($q) => $q->whereYear('created_at', $this->filterYear))
            ->when($this->filterQuarter !== 'all', function($q) {
                $months = match((int)$this->filterQuarter) {
                    1 => [1,2,3],
                    2 => [4,5,6],
                    3 => [7,8,9],
                    4 => [10,11,12],
                    default => []
                };
                return $q->whereIn(DB::raw('MONTH(created_at)'), $months);
            });
        $this->overallStats['new_ctvs'] = $inviteQuery->count();
    }

    public function render()
    {
        // Monthly trend for chart (dummy data or real logic)
        $monthlyRevenue = RealEstateListingSale::query()
            ->whereYear('sold_at', $this->filterYear)
            ->select(DB::raw('MONTH(sold_at) as month'), DB::raw('SUM(revenue_amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Top 10 CTVs this period
        $topCtvs = User::query()
            ->join('real_estate_listing_sales', 'users.id', '=', 'real_estate_listing_sales.sold_by_user_id')
            ->select('users.id', 'users.name', DB::raw('SUM(real_estate_listing_sales.revenue_amount) as revenue'))
            ->when($this->filterYear, fn($q) => $q->whereYear('real_estate_listing_sales.sold_at', $this->filterYear))
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
