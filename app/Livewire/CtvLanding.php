<?php

namespace App\Livewire;

use App\Models\RealEstateListingSale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CtvLanding extends Component
{
    public $topPerformers = [];

    public $stats = [
        'total_revenue' => 0,
        'total_ctvs' => 0,
        'total_deals' => 0,
    ];

    public function mount()
    {
        $this->loadStats();
        $this->loadTopPerformers();
    }

    private function loadStats()
    {
        $this->stats['total_revenue'] = RealEstateListingSale::sum('revenue_amount');
        $this->stats['total_ctvs'] = User::whereNotNull('phone')->count();
        $this->stats['total_deals'] = RealEstateListingSale::count();
    }

    private function loadTopPerformers()
    {
        $this->topPerformers = User::query()
            ->join('real_estate_listing_sales', 'users.id', '=', 'real_estate_listing_sales.sold_by_user_id')
            ->select('users.id', 'users.name', 'users.phone', DB::raw('SUM(real_estate_listing_sales.revenue_amount) as total_revenue'))
            ->groupBy('users.id', 'users.name', 'users.phone')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.ctv-landing')
            ->layout('components.layouts.blog', ['title' => 'Landing CTV BDS | PhongPhatLand']);
    }
}
