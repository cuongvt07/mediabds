<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;
use App\Models\CustomerWork;
use App\Models\User;

class CustomerManagement extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Search & Filter
    public string $search = '';
    public string $filterStatus = '';

    // Customer Form
    public bool $showCreatePopup = false;
    public bool $showDetailPopup = false;
    public ?int $selectedCustomerId = null;
    public ?Customer $selectedCustomer = null;

    // Form Fields
    public string $code = '';
    public $avatar; // File upload
    public $existingAvatar; // URL string for display
    public string $name = '';
    public string $phone = '';
    public string $phone2 = '';
    public string $facebook = '';
    public string $status = 'khach_mua_o';
    public ?int $assignedUserId = null;
    public ?string $budgetFrom = null; // Legacy, kept for logic but not used in UI directly
    public ?string $budgetTo = null;   // Legacy

    public ?string $description = '';

    // Budget UI properties
    public $budgetFromValue = null;
    public $budgetFromUnit = 1000000000; // Default Tỷ
    public $budgetToValue = null;
    public $budgetToUnit = 1000000000;   // Default Tỷ

    public const BUDGET_UNITS = [
        1000000 => 'Triệu',
        1000000000 => 'Tỷ',
    ];

    // Work Timeline Form
    public string $workDate = '';
    public string $workContent = '';
    public string $workProgress = '';

    // Delete Confirmation
    public bool $confirmingDeletion = false;
    public bool $editFromDetailMode = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'phone2' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
            'facebook' => 'nullable|url',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:10240', // Max 10MB, resize later
            'status' => 'required|in:khach_mua_o,dau_tu,mua,ban,dich_vu',
            'assignedUserId' => 'nullable|exists:users,id',
            'budgetFromValue' => 'nullable|numeric|min:0',
            'budgetToValue' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }

    public function mount(): void
    {
        $this->workDate = now()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Check if current user can edit the customer
     */
    public function canEdit(?Customer $customer = null): bool
    {
        $user = auth()->user();
        if (!$user)
            return false;
        if ($user->isAdmin())
            return true;
        if (!$customer)
            return false;

        return $customer->assigned_user_id === $user->id;
    }

    /**
     * Open create customer popup
     */
    public function openCreatePopup(): void
    {
        $this->resetForm();
        $this->code = Customer::generateCode();
        $this->showCreatePopup = true;
    }

    /**
     * Close create popup
     */
    public function closeCreatePopup(): void
    {
        $this->showCreatePopup = false;
        $this->resetForm();
    }

    /**
     * Open customer detail popup
     */
    public function viewCustomerDetail(int $id): void
    {
        $this->selectedCustomer = Customer::with(['assignedUser', 'works.user'])->find($id);
        if ($this->selectedCustomer) {
            $this->selectedCustomerId = $id;
            $this->showDetailPopup = true;
        }
    }

    /**
     * Close detail popup
     */
    public function closeDetailPopup(): void
    {
        $this->showDetailPopup = false;
        $this->selectedCustomer = null;
        $this->selectedCustomerId = null;
        $this->resetWorkForm();
    }

    /**
     * Edit customer from list
     */
    public function editCustomer(int $id): void
    {
        $customer = Customer::find($id);
        if (!$customer || !$this->canEdit($customer)) {
            return;
        }

        $this->selectedCustomerId = $id;
        $this->code = $customer->code;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->phone2 = $customer->phone2 ?? '';
        $this->facebook = $customer->facebook ?? '';
        $this->existingAvatar = $customer->avatar_url;
        $this->status = $customer->status;
        $this->assignedUserId = $customer->assigned_user_id;

        // Parse budget from
        if ($customer->budget_from) {
            if ($customer->budget_from >= 1000000000) {
                $this->budgetFromUnit = 1000000000;
                $this->budgetFromValue = round($customer->budget_from / 1000000000, 3);
            } else {
                $this->budgetFromUnit = 1000000;
                $this->budgetFromValue = round($customer->budget_from / 1000000, 3);
            }
        } else {
            $this->budgetFromValue = null;
            $this->budgetFromUnit = 1000000000;
        }

        // Parse budget to
        if ($customer->budget_to) {
            if ($customer->budget_to >= 1000000000) {
                $this->budgetToUnit = 1000000000;
                $this->budgetToValue = round($customer->budget_to / 1000000000, 3);
            } else {
                $this->budgetToUnit = 1000000;
                $this->budgetToValue = round($customer->budget_to / 1000000, 3);
            }
        } else {
            $this->budgetToValue = null;
            $this->budgetToUnit = 1000000000;
        }

        $this->description = $customer->description ?? '';
        $this->showCreatePopup = true;
    }

    /**
     * Edit from detail popup
     */
    public function editFromDetail(): void
    {
        if ($this->selectedCustomerId) {
            $customerId = $this->selectedCustomerId;
            $this->closeDetailPopup();
            $this->editFromDetailMode = true;
            $this->editCustomer($customerId);
        }
    }

    /**
     * Back to detail popup from edit
     */
    public function backToDetail(): void
    {
        if ($this->selectedCustomerId) {
            $customerId = $this->selectedCustomerId;
            $this->closeCreatePopup();
            $this->viewCustomerDetail($customerId);
        }
        $this->editFromDetailMode = false;
    }

    /**
     * Save customer
     */
    public function saveCustomer(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'phone2' => $this->phone2 ?: null,
            'facebook' => $this->facebook ?: null,
            'status' => $this->status,
            'assigned_user_id' => $this->assignedUserId,
            'budget_from' => $this->budgetFromValue ? (float) $this->budgetFromValue * (int) $this->budgetFromUnit : null,
            'budget_to' => $this->budgetToValue ? (float) $this->budgetToValue * (int) $this->budgetToUnit : null,
            'description' => $this->description ?: null,
        ];

        // Handle Avatar Upload
        if ($this->avatar) {
            // 1. Store temporarily to optimize
            $tempPath = $this->avatar->store('temp-avatars', 'local');
            $fullTempPath = storage_path('app/private/' . $tempPath);

            // 2. Optimize image size
            $this->optimizeImage($fullTempPath);

            // 3. Upload to S3 with unique name
            $filename = 'customers/avatars/' . time() . '_' . $this->avatar->getClientOriginalName();
            $filename = preg_replace('/[^a-zA-Z0-9._\-\/]/', '', $filename);

            $directory = dirname($filename);
            $name = basename($filename);

            $s3Path = Storage::disk('s3')->putFileAs(
                $directory,
                new \Illuminate\Http\File($fullTempPath),
                $name,
                'public'
            );

            // 4. Update data array
            $data['avatar'] = $s3Path;

            // 5. Cleanup temp file
            @unlink($fullTempPath);
        }

        if ($this->selectedCustomerId) {
            $customer = Customer::find($this->selectedCustomerId);
            if ($customer && $this->canEdit($customer)) {
                $customer->update($data);
                $message = 'Đã cập nhật khách hàng thành công!';
            } else {
                return;
            }
        } else {
            $data['code'] = $this->code;
            Customer::create($data);
            $message = 'Đã thêm khách hàng mới thành công!';
        }

        $this->dispatch('toast', ['message' => $message, 'type' => 'success']);
        $this->closeCreatePopup();
    }

    /**
     * Confirm delete
     */
    public function confirmDelete(int $id): void
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền xóa!', 'type' => 'error']);
            return;
        }

        $customer = Customer::find($id);
        if ($customer) {
            $this->selectedCustomerId = $id;
            $this->confirmingDeletion = true;
        }
    }

    /**
     * Cancel delete
     */
    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->selectedCustomerId = null;
    }

    /**
     * Delete customer
     */
    public function deleteCustomer(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền xóa!', 'type' => 'error']);
            return;
        }

        if ($this->selectedCustomerId) {
            $customer = Customer::find($this->selectedCustomerId);
            if ($customer) {
                $customer->delete();
                $this->dispatch('toast', ['message' => 'Đã xóa khách hàng!', 'type' => 'success']);
            }
        }
        $this->cancelDelete();
        $this->closeDetailPopup();
    }

    /**
     * Add work to timeline
     */
    public function addWork(): void
    {
        $this->validate([
            'workDate' => 'required|date',
            'workContent' => 'required|min:3',
            'workProgress' => 'nullable|string',
        ]);

        if (!$this->selectedCustomerId)
            return;

        $customer = Customer::find($this->selectedCustomerId);
        if (!$customer || !$this->canEdit($customer)) {
            $this->dispatch('toast', ['message' => 'Bạn không có quyền thêm công việc cho khách hàng này!', 'type' => 'error']);
            return;
        }

        CustomerWork::create([
            'customer_id' => $this->selectedCustomerId,
            'user_id' => auth()->id(),
            'work_date' => $this->workDate,
            'content' => $this->workContent,
            'progress' => $this->workProgress ?: null,
        ]);

        $this->dispatch('toast', ['message' => 'Đã thêm công việc!', 'type' => 'success']);
        $this->resetWorkForm();

        // Refresh customer data
        $this->selectedCustomer = Customer::with(['assignedUser', 'works.user'])->find($this->selectedCustomerId);
    }

    /**
     * Reset work form
     */
    public function resetWorkForm(): void
    {
        $this->workDate = now()->format('Y-m-d');
        $this->workContent = '';
        $this->workProgress = '';
    }

    /**
     * Reset form
     */
    public function resetForm(): void
    {
        $this->selectedCustomerId = null;
        $this->avatar = null;
        $this->existingAvatar = null;
        $this->code = '';
        $this->name = '';
        $this->phone = '';
        $this->phone2 = '';
        $this->facebook = '';
        $this->status = 'khach_mua_o';
        $this->assignedUserId = null;
        $this->budgetFromValue = null;
        $this->budgetFromUnit = 1000000000;
        $this->budgetToValue = null;
        $this->budgetToUnit = 1000000000;
        $this->description = '';
        $this->resetValidation();
    }

    /**
     * View customer listings
     */
    public function viewCustomerListings()
    {
        $customer = Customer::find($this->selectedCustomerId);
        if (!$customer)
            return;

        // Collect all available phones from the customer
        $phones = array_filter([$customer->phone, $customer->phone2]);

        // Navigate to listings with phone filter (comma separated)
        return redirect()->route('listings', [
            'filter_phone' => implode(',', $phones)
        ]);
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $customers = Customer::query()
            ->with('assignedUser')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(20);

        $employees = User::all();

        return view('livewire.customer-management', [
            'customers' => $customers,
            'employees' => $employees,
            'statusLabels' => Customer::STATUS_LABELS,
            'statusColors' => Customer::STATUS_COLORS,
            'isAdmin' => $user?->isAdmin() ?? false,
        ])->layout('components.layouts.app', ['title' => 'Quản Lý Khách Hàng']);
    }
    /**
     * Optimize image size to be < 1MB
     */
    private function optimizeImage(string $path): void
    {
        try {
            $info = getimagesize($path);
            if (!$info)
                return;

            $mime = $info['mime'];
            $quality = 80;

            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($path);
                    // Fix Orientation from EXIF
                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($path);
                        if (!empty($exif['Orientation'])) {
                            switch ($exif['Orientation']) {
                                case 3:
                                    $image = imagerotate($image, 180, 0);
                                    break;
                                case 6:
                                    $image = imagerotate($image, -90, 0);
                                    break;
                                case 8:
                                    $image = imagerotate($image, 90, 0);
                                    break;
                            }
                        }
                    }
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($path);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($path);
                    break;
                default:
                    // Unsupported format for optimization (e.g. HEIC), skip optimization
                    return;
            }

            if (!$image)
                return;

            // Check if file size > 1MB
            if (filesize($path) > 1048576) {
                // Resize logic: scale down to max width 1024px if larger
                $width = imagesx($image);
                $height = imagesy($image);
                $maxWidth = 1024;

                if ($width > $maxWidth) {
                    $newWidth = $maxWidth;
                    $newHeight = floor($height * ($maxWidth / $width));
                    $newImage = imagecreatetruecolor($newWidth, $newHeight);

                    // Maintain transparency for PNG
                    if ($mime == 'image/png') {
                        imagealphablending($newImage, false);
                        imagesavealpha($newImage, true);
                    }

                    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    $image = $newImage;
                }
            }

            // Save back
            if ($mime == 'image/jpeg') {
                imagejpeg($image, $path, $quality);
            } elseif ($mime == 'image/png') {
                // PNG quality is 0-9 (compression level), not 0-100
                $pngQuality = round((100 - $quality) / 10);
                imagepng($image, $path, $pngQuality);
            } elseif ($mime == 'image/webp') {
                imagewebp($image, $path, $quality);
            }

            imagedestroy($image);
        } catch (\Exception $e) {
            // Log error but continue
            \Illuminate\Support\Facades\Log::error('Image optimization failed: ' . $e->getMessage());
        }
    }
}
