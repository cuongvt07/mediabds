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

    public $tab = 'all';       // all | active | hidden
    public $search = '';
    public $priceFilter = '';

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'active', 'hidden'], true)) {
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
            'active' => (clone $base)->where('is_sold', false)->count(),
            'hidden' => (clone $base)->where('is_sold', true)->count(),
        ];

        $query = $this->ownQuery()
            ->when($this->tab === 'active', fn ($q) => $q->where('is_sold', false))
            ->when($this->tab === 'hidden', fn ($q) => $q->where('is_sold', true))
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
        ])->layout('site.layout');
    }

    private function ownQuery(): Builder
    {
        return ListingModel::query()->where('user_id', auth()->id());
    }
}
