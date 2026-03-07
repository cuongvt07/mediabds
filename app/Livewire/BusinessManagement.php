<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\CustomerWork;
use App\Models\UserInvite;
use App\Models\RealEstateListingSale;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showDetailPopup = false;
    public $selectedUserId = null;
    public $activeTab = 'info';

    // Staff details
    public $selectedUser = null;
    public $workLogs = [];
    public $inviteLogs = [];
    public $saleLogs = [];
    public $salesTotal = 0;
    public $revenueTotal = 0;
    public $bonusTotal = 0;

    // Filters for Detail
    public $filterYear;
    public $filterQuarter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            // Only show users with phone numbers (staff/users)
            ->whereNotNull('phone')
            ->latest()
            ->paginate(15);

        // Pre-calculate statistics for the list if needed, or do it per row in the view
        // For performance, we'll do basic counts here
        $users->getCollection()->transform(function ($user) {
            $user->sales_count = RealEstateListingSale::where('sold_by_user_id', $user->id)->count();
            $user->total_revenue = RealEstateListingSale::where('sold_by_user_id', $user->id)->sum('revenue_amount');
            return $user;
        });

        return view('livewire.business-management', [
            'users' => $users,
        ])->layout('components.layouts.app', ['title' => 'Quản Lý Kinh Doanh']);
    }

    public function showDetail($userId)
    {
        $this->selectedUserId = $userId;
        $this->selectedUser = User::find($userId);
        $this->showDetailPopup = true;
        $this->activeTab = 'info';
        $this->filterYear = date('Y');
        $this->filterQuarter = 'all';
        $this->loadTabData();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadTabData();
    }

    public function loadTabData()
    {
        if (!$this->selectedUserId) return;

        switch ($this->activeTab) {
            case 'work':
                $this->workLogs = CustomerWork::where('user_id', $this->selectedUserId)
                    ->with('customer')
                    ->latest()
                    ->get();
                break;
            case 'invites':
                $this->inviteLogs = UserInvite::where('inviter_user_id', $this->selectedUserId)
                    ->with('invitedUser')
                    ->latest()
                    ->get();
                break;
            case 'sales':
                $query = RealEstateListingSale::where('sold_by_user_id', $this->selectedUserId)
                    ->with('listing')
                    ->latest();

                if ($this->filterYear) {
                    $query->whereYear('sold_at', $this->filterYear);
                }

                if ($this->filterQuarter !== 'all') {
                    $months = match ((int)$this->filterQuarter) {
                        1 => [1, 2, 3],
                        2 => [4, 5, 6],
                        3 => [7, 8, 9],
                        4 => [10, 11, 12],
                        default => []
                    };
                    if (!empty($months)) {
                        $query->whereIn(\DB::raw('MONTH(sold_at)'), $months);
                    }
                }

                $this->saleLogs = $query->get();
                $this->salesTotal = $this->saleLogs->sum('actual_price');
                $this->revenueTotal = $this->saleLogs->sum('revenue_amount');
                $this->bonusTotal = $this->saleLogs->sum('bonus_amount');
                break;
        }
    }

    public function closeDetail()
    {
        $this->showDetailPopup = false;
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->workLogs = [];
        $this->inviteLogs = [];
        $this->saleLogs = [];
    }
}
