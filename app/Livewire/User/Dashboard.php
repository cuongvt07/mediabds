<?php

namespace App\Livewire\User;

use App\Models\RealEstateListing as ListingModel;
use App\Services\ListingImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination, WithFileUploads;

    public const ROOM_TYPES = [
        'duplex' => 'Duplex',
        'studio' => 'Studio',
        'loft' => 'Phòng có gác',
        'balcony' => 'Phòng ban công',
    ];

    public const POSTING_PLANS = [
        'free'     => ['name' => 'Free',           'limit' => 10,  'price' => 0],
        'daily_20' => ['name' => 'Gói 20 tin/ngày','limit' => 20,  'price' => 399000],
        'daily_40' => ['name' => 'Gói 40 tin/ngày','limit' => 40,  'price' => 599000],
    ];

    // Listing tabs
    public $tab         = 'all';
    public $search      = '';
    public $priceFilter = '';

    // Account settings section
    public $settingsTab = 'profile'; // profile | password | delete

    // Profile fields
    public $profileName      = '';
    public $profileEmail     = '';
    public $profilePhone     = '';
    public $profileBirthYear = '';
    public $profileAvatar    = '';   // current avatar URL
    public $profileAvatarFile;       // new upload

    // Password fields
    public $currentPassword = '';
    public $newPassword     = '';
    public $newPasswordConfirm = '';

    // Delete account
    public $deleteConfirmPassword = '';
    public $showDeleteConfirm = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->profileName      = $user->name      ?? '';
        $this->profileEmail     = $user->email     ?? '';
        $this->profilePhone     = $user->phone     ?? '';
        $this->profileBirthYear = $user->birth_year ?? '';
        $this->profileAvatar    = $user->avatar    ?? '';
    }

    // ── Listing tabs ─────────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'pending', 'active', 'rejected', 'hidden', 'boosting'], true)) {
            return;
        }
        $this->tab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void     { $this->resetPage(); }
    public function updatedPriceFilter(): void { $this->resetPage(); }

    public function toggleListing(int $id): void
    {
        $listing = $this->ownQuery()->findOrFail($id);
        $hidden  = ! $listing->is_sold;
        $listing->update([
            'is_sold' => $hidden,
            'status'  => $hidden ? 'inactive' : 'active',
        ]);
    }

    public function deleteListing(int $id): void
    {
        $this->ownQuery()->whereKey($id)->delete();
        session()->flash('message', 'Đã xóa tin đăng.');
    }

    // ── Account settings ─────────────────────────────────────────────────────

    public function setSettingsTab(string $tab): void
    {
        if (in_array($tab, ['profile', 'password', 'delete'], true)) {
            $this->settingsTab = $tab;
            $this->resetValidation();
        }
    }

    public function saveProfile(): void
    {
        $user = auth()->user();

        $data = $this->validate([
            'profileName'      => 'required|string|max:100',
            'profileEmail'     => ['nullable', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'profilePhone'     => ['required', 'regex:/^0\d{9}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'profileBirthYear' => 'nullable|integer|min:1900|max:' . (date('Y') - 5),
            'profileAvatarFile'=> 'nullable|image|max:2048',
        ], [
            'profileName.required'   => 'Vui lòng nhập tên.',
            'profilePhone.regex'     => 'Số điện thoại phải gồm 10 số, bắt đầu bằng 0.',
            'profilePhone.unique'    => 'Số điện thoại này đã được dùng bởi tài khoản khác.',
            'profileEmail.unique'    => 'Email này đã được dùng bởi tài khoản khác.',
        ]);

        // Upload avatar nếu có
        if ($this->profileAvatarFile) {
            $path = $this->profileAvatarFile->store('avatars', 'public');
            $this->profileAvatar = Storage::disk('public')->url($path);
        }

        $user->update([
            'name'       => $data['profileName'],
            'email'      => $data['profileEmail'] ?: null,
            'phone'      => $data['profilePhone'],
            'birth_year' => $data['profileBirthYear'] ?: null,
            'avatar'     => $this->profileAvatar ?: null,
        ]);

        $this->profileAvatarFile = null;
        session()->flash('message', 'Đã cập nhật thông tin tài khoản.');
    }

    public function savePassword(): void
    {
        $this->validate([
            'currentPassword'    => 'required|string',
            'newPassword'        => 'required|string|min:6|max:100',
            'newPasswordConfirm' => 'required|same:newPassword',
        ], [
            'newPassword.min'          => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'newPasswordConfirm.same'  => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = auth()->user();

        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Mật khẩu hiện tại không đúng.');
            return;
        }

        $user->update(['password' => Hash::make($this->newPassword)]);

        $this->currentPassword     = '';
        $this->newPassword         = '';
        $this->newPasswordConfirm  = '';
        session()->flash('message', 'Đã đổi mật khẩu thành công.');
    }

    public function removeAvatar(): void
    {
        $this->profileAvatar    = '';
        $this->profileAvatarFile = null;
        auth()->user()->update(['avatar' => null]);
    }

    public function confirmDeleteAccount(): void
    {
        $this->showDeleteConfirm = true;
    }

    public function deleteAccount()
    {
        $this->validate([
            'deleteConfirmPassword' => 'required|string',
        ], [
            'deleteConfirmPassword.required' => 'Vui lòng nhập mật khẩu để xác nhận.',
        ]);

        $user = auth()->user();

        if (! Hash::check($this->deleteConfirmPassword, $user->password)) {
            $this->addError('deleteConfirmPassword', 'Mật khẩu không đúng.');
            return;
        }

        Auth::logout();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();
        session()->flash('message', 'Tài khoản của bạn đã được xóa.');

        return redirect()->route('site.home');
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm    = false;
        $this->deleteConfirmPassword = '';
        $this->resetValidation('deleteConfirmPassword');
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $base = $this->ownQuery();

        $counts = [
            'all'      => (clone $base)->count(),
            'pending'  => (clone $base)->where('moderation_status', 'pending')->count(),
            'active'   => (clone $base)->where('is_sold', false)->where('moderation_status', 'approved')
                                       ->where(fn ($q) => $q->whereNull('status')->orWhere('status', 'active'))->count(),
            'rejected' => (clone $base)->where('moderation_status', 'rejected')->count(),
            'hidden'   => (clone $base)->where(fn ($q) => $q->where('is_sold', true)->orWhere('status', 'inactive'))->count(),
            'boosting' => (clone $base)->where('boost_tier', '<>', 'normal')->where('boost_expires_at', '>', now())->count(),
        ];

        $query = $this->ownQuery()
            ->when($this->tab === 'pending',  fn ($q) => $q->where('moderation_status', 'pending'))
            ->when($this->tab === 'active',   fn ($q) => $q->where('is_sold', false)->where('moderation_status', 'approved')
                                                           ->where(fn ($s) => $s->whereNull('status')->orWhere('status', 'active')))
            ->when($this->tab === 'rejected', fn ($q) => $q->where('moderation_status', 'rejected'))
            ->when($this->tab === 'hidden',   fn ($q) => $q->where(fn ($s) => $s->where('is_sold', true)->orWhere('status', 'inactive')))
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
            '3_4'     => $query->whereRaw("{$expr} >= ? and {$expr} <= ?", [3000000, 4000000]),
            '4_5'     => $query->whereRaw("{$expr} >= ? and {$expr} <= ?", [4000000, 5000000]),
            '5_6'     => $query->whereRaw("{$expr} >= ? and {$expr} <= ?", [5000000, 6000000]),
            'over_6'  => $query->whereRaw("{$expr} > ?", [6000000]),
            default   => $query,
        };

        return view('livewire.user.dashboard', [
            'listings'     => $query->orderByDesc('id')->paginate(8),
            'counts'       => $counts,
            'roomTypes'    => self::ROOM_TYPES,
            'user'         => auth()->user(),
            'postingPlans' => self::POSTING_PLANS,
        ])->layout('site.layout');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function ownQuery(): Builder
    {
        return ListingModel::query()->where('user_id', auth()->id());
    }
}
