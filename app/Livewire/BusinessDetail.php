<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\CustomerWork;
use App\Models\UserInvite;
use App\Models\RealEstateListingSale;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class BusinessDetail extends Component
{
    use WithPagination;

    public $userId;
    public $user;
    public $filterYear;
    public $filterQuarter = 'all';
    
    // Stats for summary
    public $stats = [
        'total_revenue' => 0,
        'total_bonus' => 0,
        'total_deals' => 0,
        'total_invites' => 0,
    ];

    public function mount($id)
    {
        // Authorization check: User can only view their own detail unless they are admin
        if (!auth()->user()->isAdmin() && (int)auth()->id() !== (int)$id) {
            return redirect()->route('listings')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $this->userId = $id;
        $this->user = User::findOrFail($id);
        $this->filterYear = date('Y');
        $this->loadStats();
    }

    public function updatedFilterYear() { $this->loadStats(); $this->resetPage(); }
    public function updatedFilterQuarter() { $this->loadStats(); $this->resetPage(); }

    public function loadStats()
    {
        $query = RealEstateListingSale::where('sold_by_user_id', $this->userId)
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

        $this->stats['total_revenue'] = (clone $query)->sum('revenue_amount');
        $this->stats['total_bonus'] = (clone $query)->sum('bonus_amount');
        $this->stats['total_deals'] = (clone $query)->count();

        $inviteQuery = UserInvite::where('inviter_user_id', $this->userId)
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
        $this->stats['total_invites'] = $inviteQuery->count();
    }

    public function render()
    {
        $sales = RealEstateListingSale::where('sold_by_user_id', $this->userId)
            ->with('listing')
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
            })
            ->latest('sold_at')
            ->paginate(10, ['*'], 'salesPage');

        $invites = UserInvite::where('inviter_user_id', $this->userId)
            ->with('invitedUser')
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
            })
            ->latest()
            ->paginate(10, ['*'], 'invitesPage');

        return view('livewire.business-detail', [
            'sales' => $sales,
            'invites' => $invites,
        ])->layout('components.layouts.app', ['title' => 'Chi tiết kinh doanh - ' . $this->user->name]);
    }
}
