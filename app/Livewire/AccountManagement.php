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
    public $name = '';
    public $phone = '';
    public $password = '';
    public $property_types = [];
    public $inviterUserId = null;
    public $rootInviteCode = null;
    public $existingInviteCode = null;

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
            'inviterUserId' => 'nullable|exists:users,id',
            'rootInviteCode' => [
                Rule::requiredIf(fn() => $this->shouldRequireRootInviteCode()),
                'nullable',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('users', 'invite_code')->ignore($this->selectedUserId),
            ],
        ];

        return $rules;
    }

    protected function shouldRequireRootInviteCode(): bool
    {
        // Already has code => cannot edit code anymore.
        if (!blank($this->existingInviteCode)) {
            return false;
        }

        // No inviter selected => this is root account, must input root code.
        return blank($this->inviterUserId);
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

        $inviters = User::query()
            ->whereNotNull('invite_code')
            ->when($this->selectedUserId, fn($q) => $q->where('id', '!=', $this->selectedUserId))
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'invite_code']);

        return view('livewire.account-management', [
            'users' => $users,
            'inviters' => $inviters,
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
        $this->inviterUserId = $user->invited_by_user_id;
        $this->existingInviteCode = $user->invite_code;
        $this->rootInviteCode = blank($user->invite_code) ? '' : $user->invite_code;
        $this->showCreatePopup = true;
    }

    public function saveUser()
    {
        $this->rootInviteCode = Str::upper(trim((string) $this->rootInviteCode));
        if ($this->rootInviteCode === '') {
            $this->rootInviteCode = null;
        }

        $this->validate();

        $inviter = null;
        if (!blank($this->inviterUserId)) {
            $inviter = User::select('id', 'invite_code')->find($this->inviterUserId);
            if (!$inviter || blank($inviter->invite_code)) {
                $this->addError('inviterUserId', 'Nguoi moi duoc chon chua co ma moi hop le.');
                return;
            }
        }

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'property_types' => $this->property_types,
        ];

        if ($this->selectedUserId) {
            DB::transaction(function () use ($data, $inviter) {
                $user = User::findOrFail($this->selectedUserId);
                $oldInviterId = $user->invited_by_user_id;

                $updates = array_merge($data, [
                    'invited_by_user_id' => $inviter?->id,
                ]);

                // If account has no code yet, allow setting code one-time.
                if (blank($user->invite_code)) {
                    $updates['invite_code'] = $inviter
                        ? ($inviter->invite_code . $user->id)
                        : $this->rootInviteCode;
                }

                $user->update($updates);

                // Save invitation relation history when inviter is set/changed.
                if ($inviter && $oldInviterId !== $inviter->id) {
                    UserInvite::create([
                        'inviter_user_id' => $inviter->id,
                        'invited_user_id' => $user->id,
                        'inviter_code' => $inviter->invite_code,
                    ]);
                }
            });

            $message = 'Da cap nhat tai khoan thanh cong!';
        } else {
            DB::transaction(function () use ($data, $inviter) {
                $user = User::create(array_merge($data, [
                    'password' => bcrypt(Str::random(16)),
                    'invited_by_user_id' => $inviter?->id,
                ]));

                $generatedInviteCode = $inviter
                    ? ($inviter->invite_code . $user->id)
                    : $this->rootInviteCode;

                $user->update([
                    'invite_code' => $generatedInviteCode,
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
        $this->inviterUserId = null;
        $this->rootInviteCode = null;
        $this->existingInviteCode = null;
        $this->resetValidation();
    }
}
