<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\CustomerWork;
use App\Models\User;

class CustomerManagement extends Component
{
    use WithPagination;

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
    public string $name = '';
    public string $phone = '';
    public string $status = 'khach_mua_o';
    public ?int $assignedUserId = null;
    public ?string $budgetFrom = null;
    public ?string $budgetTo = null;
    public string $description = '';

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
            'status' => 'required|in:khach_mua_o,dau_tu,mua,ban,dich_vu',
            'assignedUserId' => 'nullable|exists:users,id',
            'budgetFrom' => 'nullable|numeric|min:0',
            'budgetTo' => 'nullable|numeric|min:0',
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
        $this->status = $customer->status;
        $this->assignedUserId = $customer->assigned_user_id;
        $this->budgetFrom = $customer->budget_from;
        $this->budgetTo = $customer->budget_to;
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
            'status' => $this->status,
            'assigned_user_id' => $this->assignedUserId,
            'budget_from' => $this->budgetFrom ? (float) str_replace(['.', ','], '', $this->budgetFrom) : null,
            'budget_to' => $this->budgetTo ? (float) str_replace(['.', ','], '', $this->budgetTo) : null,
            'description' => $this->description ?: null,
        ];

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
        $this->code = '';
        $this->name = '';
        $this->phone = '';
        $this->status = 'khach_mua_o';
        $this->assignedUserId = null;
        $this->budgetFrom = null;
        $this->budgetTo = null;
        $this->description = '';
        $this->resetValidation();
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
}
