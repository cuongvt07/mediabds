<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CtvRank;

class CtvRankManagement extends Component
{
    use WithPagination;

    // Form state
    public $rankId;
    public $name;
    public $min_invites;
    public $min_price;
    public $max_price;

    public $isModalOpen = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'min_invites' => 'required|integer|min:0',
        'min_price' => 'required|string',
        'max_price' => 'nullable|string',
    ];

    protected $messages = [
        'name.required' => 'Tên hạng CTV không được để trống.',
        'min_invites.required' => 'Số lượng mời tối thiểu không được để trống.',
        'min_invites.integer' => 'Số lượng mời phải là một số nguyên.',
        'min_invites.min' => 'Số lượng mời không được nhỏ hơn 0.',
        'min_price.required' => 'Mức giá tối thiểu không được để trống.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $ranks = CtvRank::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('min_invites', 'asc')
            ->paginate(10);

        return view('livewire.ctv-rank-management', [
            'ranks' => $ranks,
        ]);
    }

    public function create()
    {
        $this->resetCreateForm();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetCreateForm()
    {
        $this->rankId = '';
        $this->name = '';
        $this->min_invites = 0;
        $this->min_price = '';
        $this->max_price = '';
    }

    public function store()
    {
        $this->validate();

        $minPriceClean = (float) str_replace('.', '', $this->min_price);
        $maxPriceClean = $this->max_price ? (float) str_replace('.', '', $this->max_price) : null;

        if ($maxPriceClean !== null && $maxPriceClean <= $minPriceClean) {
            $this->addError('max_price', 'Mức giá tối đa phải lớn hơn mức giá tối thiểu.');
            return;
        }

        CtvRank::updateOrCreate(['id' => $this->rankId], [
            'name' => $this->name,
            'min_invites' => $this->min_invites,
            'min_price' => $minPriceClean,
            'max_price' => $maxPriceClean,
        ]);

        $this->closeModal();
        $this->resetCreateForm();
        session()->flash('message', $this->rankId ? 'Cập nhật hạng CTV thành công.' : 'Thêm mới hạng CTV thành công.');
    }

    public function edit($id)
    {
        $rank = CtvRank::findOrFail($id);
        $this->rankId = $id;
        $this->name = $rank->name;
        $this->min_invites = $rank->min_invites;
        $this->min_price = number_format($rank->min_price, 0, ',', '.');
        $this->max_price = $rank->max_price ? number_format($rank->max_price, 0, ',', '.') : '';

        $this->openModal();
    }

    public function delete($id)
    {
        CtvRank::find($id)->delete();
        session()->flash('message', 'Xóa hạng CTV thành công.');
    }
}
