<?php

namespace App\Livewire\User;

use App\Models\RealEstateListing as ListingModel;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public const ROOM_TYPES = [
        'duplex' => 'Duplex',
        'studio' => 'Studio',
        'loft' => 'Phòng có gác',
        'balcony' => 'Phòng ban công',
    ];

    public const POSTING_PLANS = [
        'free' => ['name' => 'Free', 'limit' => 10, 'price' => 0],
        'daily_20' => ['name' => 'Gói 20 tin/ngày', 'limit' => 20, 'price' => 399000],
        'daily_40' => ['name' => 'Gói 40 tin/ngày', 'limit' => 40, 'price' => 599000],
    ];

    public $tab = 'all';       // all | pending | active | rejected | hidden | boosting
    public $search = '';
    public $priceFilter = '';
    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'pending', 'active', 'rejected', 'hidden', 'boosting'], true)) {
            return;
        }
        $this->tab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPriceFilter(): void
    {
        $this->resetPage();
    }

    public function toggleListing(int $id): void
    {
        $listing = $this->ownQuery()->findOrFail($id);
        $hidden = ! $listing->is_sold;
        $listing->update([
            'is_sold' => $hidden,
            'status' => $hidden ? 'inactive' : 'active',
        ]);
    }

    public function deleteListing(int $id): void
    {
        $this->ownQuery()->whereKey($id)->delete();
        session()->flash('message', 'Đã xóa tin đăng.');
    }

    public function render()
    {
        $base = $this->ownQuery();

        $counts = [
            'all' => (clone $base)->count(),
            'pending' => (clone $base)->where('moderation_status', 'pending')->count(),
            'active' => (clone $base)->where('is_sold', false)->where('moderation_status', 'approved')->where(function ($q) { $q->whereNull('status')->orWhere('status', 'active'); })->count(),
            'rejected' => (clone $base)->where('moderation_status', 'rejected')->count(),
            'hidden' => (clone $base)->where(function ($q) { $q->where('is_sold', true)->orWhere('status', 'inactive'); })->count(),
            'boosting' => (clone $base)->where('boost_tier', '<>', 'normal')->where('boost_expires_at', '>', now())->count(),
        ];

        $query = $this->ownQuery()
            ->when($this->tab === 'pending', fn ($q) => $q->where('moderation_status', 'pending'))
            ->when($this->tab === 'active', fn ($q) => $q->where('is_sold', false)->where('moderation_status', 'approved')->where(function ($sub) { $sub->whereNull('status')->orWhere('status', 'active'); }))
            ->when($this->tab === 'rejected', fn ($q) => $q->where('moderation_status', 'rejected'))
            ->when($this->tab === 'hidden', fn ($q) => $q->where(function ($sub) { $sub->where('is_sold', true)->orWhere('status', 'inactive'); }))
            ->when($this->tab === 'boosting', fn ($q) => $q->where('boost_tier', '<>', 'normal')->where('boost_expires_at', '>', now()))
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(fn ($s) => $s->where('title', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('district_name', 'like', $term)
                    ->orWhere('ward_name', 'like', $term));
            });

        $expr = "(case when price >= 1000000 then price when price_unit in ('Triệu','trieu','2') then price * 1000000 else price end)";
        match ($this->priceFilter) {
            'under_3' => $query->whereRaw("{$expr} < ?", [3000000]),
            '3_4' => $query->whereRaw("{$expr} >= ? and {$expr} <= ?", [3000000, 4000000]),
            '4_5' => $query->whereRaw("{$expr} >= ? and {$expr} <= ?", [4000000, 5000000]),
            '5_6' => $query->whereRaw("{$expr} >= ? and {$expr} <= ?", [5000000, 6000000]),
            'over_6' => $query->whereRaw("{$expr} > ?", [6000000]),
            default => $query,
        };

        $revenue = 0; // placeholder cho thông số liên quan

        return view('livewire.user.dashboard', [
            'listings' => $query->orderByDesc('id')->paginate(8),
            'counts' => $counts,
            'roomTypes' => self::ROOM_TYPES,
            'user' => auth()->user(),
            'postingPlans' => self::POSTING_PLANS,
        ])->layout('site.layout');
    }

    private function ownQuery(): Builder
    {
        return ListingModel::query()->where('user_id', auth()->id());
    }
}
