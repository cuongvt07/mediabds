<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\RealEstateListingSaleMember;
use App\Models\RealEstateListingSale;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AccountDetail extends Component
{
    use WithPagination;

    public $userId;
    public $user;
    public $activeTab = 'transactions'; // transactions, referrals, overview

    public function mount($id)
    {
        $this->userId = $id;
        $this->user = User::withCount(['invitees', 'sentInviteLogs'])->findOrFail($id);
    }

    public function render()
    {
        // 1. Transaction History (Primary Commissions)
        $transactions = DB::table('real_estate_listing_sale_members')
            ->join('real_estate_listing_sales', 'real_estate_listing_sale_members.sale_id', '=', 'real_estate_listing_sales.id')
            ->join('real_estate_listings', 'real_estate_listing_sales.listing_id', '=', 'real_estate_listings.id')
            ->where('real_estate_listing_sale_members.user_id', $this->userId)
            ->select(
                'real_estate_listings.title as listing_title',
                'real_estate_listing_sales.project_name',
                'real_estate_listing_sales.actual_price',
                'real_estate_listing_sales.revenue_amount',
                'real_estate_listing_sale_members.received_amount',
                'real_estate_listing_sales.sold_at'
            )
            ->unionAll(
                // Legacy data for this user
                DB::table('real_estate_listing_sales')
                    ->join('real_estate_listings', 'real_estate_listing_sales.listing_id', '=', 'real_estate_listings.id')
                    ->where('real_estate_listing_sales.sold_by_user_id', $this->userId)
                    ->whereNotExists(function($query) {
                        $query->select(DB::raw(1))
                            ->from('real_estate_listing_sale_members')
                            ->whereColumn('real_estate_listing_sale_members.sale_id', 'real_estate_listing_sales.id');
                    })
                    ->select(
                        'real_estate_listings.title as listing_title',
                        'real_estate_listing_sales.project_name',
                        'real_estate_listing_sales.actual_price',
                        'real_estate_listing_sales.revenue_amount',
                        'real_estate_listing_sales.revenue_amount as received_amount', // 100% share for legacy
                        'real_estate_listing_sales.sold_at'
                    )
            )
            ->orderBy('sold_at', 'desc')
            ->paginate(10, ['*'], 'txPage');

        // 2. Referral History
        $referrals = $this->user->invitees()->latest()->paginate(10, ['*'], 'refPage');

        return view('livewire.account-detail', [
            'transactions' => $transactions,
            'referrals' => $referrals,
        ])->layout('components.layouts.app', ['title' => 'Chi tiết đối tác: ' . $this->user->name]);
    }
}
