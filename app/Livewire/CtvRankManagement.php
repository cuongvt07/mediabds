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
        'min_price' => 'required|numeric|min:0',
        'max_price' => 'nullable|numeric|gt:min_price',
    ];

    protected $messages = [
        'name.required' => 'Tên hạng CTV không được để trống.',
        'min_invites.required' => 'Số lượng mời tối thiểu không được để trống.',
        'min_invites.integer' => 'Số lượng mời phải là một số nguyên.',
        'min_invites.min' => 'Số lượng mời không được nhỏ hơn 0.',
        'min_price.required' => 'Mức giá tối thiểu không được để trống.',
        'min_price.numeric' => 'Mức giá phải là một số.',
        'min_price.min' => 'Mức giá không được nhỏ hơn 0.',
        'max_price.numeric' => 'Mức giá tối đa phải là một số.',
        'max_price.gt' => 'Mức giá tối đa phải lớn hơn mức giá tối thiểu.',
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
        $this->min_price = 0;
        $this->max_price = null;
    }

    public function store()
    {
        $this->validate();

        CtvRank::updateOrCreate(['id' => $this->rankId], [
            'name' => $this->name,
            'min_invites' => $this->min_invites,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price === '' ? null : $this->max_price,
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
        $this->min_price = $rank->min_price;
        $this->max_price = $rank->max_price;

        $this->openModal();
    }

    public function delete($id)
    {
        CtvRank::find($id)->delete();
        session()->flash('message', 'Xóa hạng CTV thành công.');
    }
}
