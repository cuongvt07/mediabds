<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Validation\Rule;

class AccountManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreatePopup = false;
    public $confirmingUserDeletion = false;
    public $selectedUserId = null;

    // Form Fields
    public $name;
    public $phone;
    public $password;

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', Rule::unique('users', 'phone')->ignore($this->selectedUserId)],
        ];
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.account-management', [
            'users' => $users
        ])->layout('components.layouts.app', ['title' => 'Account Management']);
    }

    public function openCreatePopup()
    {
        $this->resetForm();
        $this->showCreatePopup = true;
    }

    public function closeCreatePopup()
    {
        $this->showCreatePopup = false;
        $this->resetForm();
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $this->selectedUserId = $id;
        $this->name = $user->name;
        $this->phone = $user->phone;
        // Password field removed
        $this->showCreatePopup = true;
    }

    public function saveUser()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
        ];

        // Password not handled here as it's not in the form.
        // If DB requires password, we might need a default for creation.
        // Assuming standard Laravel user table...
        if (!$this->selectedUserId) {
             $data['password'] = bcrypt(\Illuminate\Support\Str::random(16));
        }

        if ($this->selectedUserId) {
            User::where('id', $this->selectedUserId)->update($data);
            $message = 'Đã cập nhật tài khoản thành công!';
        } else {
            User::create($data);
            $message = 'Đã tạo tài khoản thành công!';
        }

        $this->dispatch('toast', ['message' => $message, 'type' => 'success']);
        $this->closeCreatePopup();
    }

    public function confirmDelete($id)
    {
        $this->selectedUserId = $id;
        $this->confirmingUserDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingUserDeletion = false;
        $this->selectedUserId = null;
    }

    public function deleteUser()
    {
        if ($this->selectedUserId) {
            User::destroy($this->selectedUserId);
            $this->dispatch('toast', ['message' => 'Đã xóa tài khoản!', 'type' => 'success']);
            $this->confirmingUserDeletion = false;
            $this->selectedUserId = null;
        }
    }

    public function resetForm()
    {
        $this->selectedUserId = null;
        $this->name = '';
        $this->phone = '';
        $this->password = '';
        $this->resetValidation();
    }
}
