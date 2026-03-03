<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

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
    public $property_types = [];
    public $inviterCode = null;
    public $rootInviteCode = null;

    protected function rules()
    {
        $rules = [
            'name' => 'required|min:3',
            'phone' => [
                'required',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                Rule::unique('users', 'phone')->ignore($this->selectedUserId),
            ],
            'property_types' => 'nullable|array',
        ];

        if (!$this->selectedUserId) {
            $rules['inviterCode'] = 'nullable|string|exists:users,invite_code';
            $rules['rootInviteCode'] = [
                Rule::requiredIf(fn() => blank($this->inviterCode)),
                'nullable',
                'regex:/^[A-Z]+$/',
                Rule::unique('users', 'invite_code'),
            ];
        }

        return $rules;
    }

    public function render()
    {
        $users = User::query()
            ->with('inviter')
            ->withCount('sentInviteLogs')
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('invite_code', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.account-management', [
            'users' => $users,
            'propertyTypeOptions' => RealEstateListing::PROPERTY_TYPES,
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
        $this->property_types = $user->property_types ?? [];
        $this->showCreatePopup = true;
    }

    public function saveUser()
    {
        $this->inviterCode = Str::upper(trim((string) $this->inviterCode));
        $this->rootInviteCode = Str::upper(trim((string) $this->rootInviteCode));

        if ($this->inviterCode === '') {
            $this->inviterCode = null;
        }

        if ($this->rootInviteCode === '') {
            $this->rootInviteCode = null;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'property_types' => $this->property_types,
        ];

        if ($this->selectedUserId) {
            User::where('id', $this->selectedUserId)->update($data);
            $message = 'Da cap nhat tai khoan thanh cong!';
        } else {
            DB::transaction(function () use ($data) {
                $inviter = null;
                if (!blank($this->inviterCode)) {
                    $inviter = User::where('invite_code', $this->inviterCode)->first();
                }

                $user = User::create(array_merge($data, [
                    'password' => bcrypt(Str::random(16)),
                ]));

                $generatedInviteCode = $inviter
                    ? $inviter->invite_code . $user->id
                    : $this->rootInviteCode;

                $user->update([
                    'invite_code' => $generatedInviteCode,
                    'invited_by_user_id' => $inviter?->id,
                ]);

                if ($inviter) {
                    UserInvite::create([
                        'inviter_user_id' => $inviter->id,
                        'invited_user_id' => $user->id,
                        'inviter_code' => $inviter->invite_code,
                    ]);
                }
            });
            $message = 'Da tao tai khoan thanh cong!';
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
            $this->dispatch('toast', ['message' => 'Da xoa tai khoan!', 'type' => 'success']);
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
        $this->property_types = [];
        $this->inviterCode = null;
        $this->rootInviteCode = null;
        $this->resetValidation();
    }
}
