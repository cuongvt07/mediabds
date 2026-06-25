<?php

namespace App\Livewire;

use App\Models\VehicleListing as VehicleListingModel;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Quản lý tin đăng XE CỘ (ô tô / xe máy) trong CMS.
 *
 * Tự chứa: list + tìm kiếm + lọc + phân trang + CRUD qua modal. Ảnh nhập bằng
 * URL (mỗi dòng một URL — dán từ Media Manager) để không phụ thuộc media picker.
 */
class VehicleListing extends Component
{
    use WithPagination;

    // ----- Bộ lọc / tìm kiếm -----
    public string $search = '';
    public string $filterVehicleType = '';
    public string $filterStatus = '';

    // ----- Trạng thái modal -----
    public bool $showModal = false;
    public ?int $editingId = null;

    // ----- Trường form -----
    public string $title = '';
    public string $type = 'Cần bán';
    public string $vehicleType = 'car';
    public string $brand = '';
    public string $modelName = '';
    public ?string $year = null;
    public ?string $mileage = null;
    public string $transmission = '';
    public string $fuelType = '';
    public string $engineCapacity = '';
    public string $color = '';
    public ?string $seats = null;
    public string $condition = 'used';
    public string $origin = '';
    public ?string $price = null;
    public string $priceUnit = 'Triệu';
    public string $provinceName = '';
    public string $districtName = '';
    public string $address = '';
    public string $contactName = '';
    public string $contactPhone = '';
    public string $contactZalo = '';
    public string $description = '';
    public string $youtubeLink = '';
    public string $imagesText = '';
    public string $statusValue = 'active';
    public string $vipTier = 'normal';
    public bool $isSold = false;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterVehicleType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'type' => 'required|string|max:50',
            'vehicleType' => 'required|in:car,motorbike',
            'brand' => 'nullable|string|max:80',
            'modelName' => 'nullable|string|max:120',
            'year' => 'nullable|integer|min:1950|max:2100',
            'mileage' => 'nullable|integer|min:0',
            'transmission' => 'nullable|string|max:40',
            'fuelType' => 'nullable|string|max:40',
            'engineCapacity' => 'nullable|string|max:40',
            'color' => 'nullable|string|max:40',
            'seats' => 'nullable|integer|min:1|max:64',
            'condition' => 'nullable|in:new,used',
            'origin' => 'nullable|string|max:40',
            'price' => 'nullable|numeric|min:0',
            'priceUnit' => 'required|string|max:30',
            'provinceName' => 'nullable|string|max:120',
            'districtName' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:500',
            'contactName' => 'nullable|string|max:120',
            'contactPhone' => 'nullable|string|max:50',
            'contactZalo' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'youtubeLink' => 'nullable|string|max:2048',
            'imagesText' => 'nullable|string',
            'statusValue' => 'required|in:active,pending,expired,sold',
            'vipTier' => 'required|in:normal,vip1,vip2,vip3',
            'isSold' => 'boolean',
        ];
    }

    public function createVehicle(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editVehicle(int $id): void
    {
        $v = VehicleListingModel::findOrFail($id);

        $this->editingId = $v->id;
        $this->title = (string) $v->title;
        $this->type = (string) ($v->type ?: 'Cần bán');
        $this->vehicleType = (string) ($v->vehicle_type ?: 'car');
        $this->brand = (string) $v->brand;
        $this->modelName = (string) $v->model_name;
        $this->year = $v->year !== null ? (string) $v->year : null;
        $this->mileage = $v->mileage !== null ? (string) $v->mileage : null;
        $this->transmission = (string) $v->transmission;
        $this->fuelType = (string) $v->fuel_type;
        $this->engineCapacity = (string) $v->engine_capacity;
        $this->color = (string) $v->color;
        $this->seats = $v->seats !== null ? (string) $v->seats : null;
        $this->condition = (string) ($v->condition ?: 'used');
        $this->origin = (string) $v->origin;
        $this->price = $v->price !== null ? (string) $v->price : null;
        $this->priceUnit = (string) ($v->price_unit ?: 'Triệu');
        $this->provinceName = (string) $v->province_name;
        $this->districtName = (string) $v->district_name;
        $this->address = (string) $v->address;
        $this->contactName = (string) $v->contact_name;
        $this->contactPhone = (string) $v->contact_phone;
        $this->contactZalo = (string) $v->contact_zalo;
        $this->description = (string) $v->description;
        $this->youtubeLink = (string) $v->youtube_link;
        $this->imagesText = implode("\n", is_array($v->images) ? $v->images : []);
        $this->statusValue = (string) ($v->status ?: 'active');
        $this->vipTier = (string) ($v->vip_tier ?: 'normal');
        $this->isSold = (bool) $v->is_sold;

        $this->showModal = true;
    }

    public function saveVehicle(): void
    {
        $this->validate();

        $images = collect(preg_split('/\r\n|\r|\n/', $this->imagesText))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all();

        $data = [
            'title' => $this->title,
            'type' => $this->type,
            'vehicle_type' => $this->vehicleType,
            'brand' => $this->brand ?: null,
            'model_name' => $this->modelName ?: null,
            'year' => $this->year !== null && $this->year !== '' ? (int) $this->year : null,
            'mileage' => $this->mileage !== null && $this->mileage !== '' ? (int) $this->mileage : null,
            'transmission' => $this->transmission ?: null,
            'fuel_type' => $this->fuelType ?: null,
            'engine_capacity' => $this->engineCapacity ?: null,
            'color' => $this->color ?: null,
            'seats' => $this->seats !== null && $this->seats !== '' ? (int) $this->seats : null,
            'condition' => $this->condition ?: null,
            'origin' => $this->origin ?: null,
            'price' => $this->price !== null && $this->price !== '' ? (float) $this->price : null,
            'price_unit' => $this->priceUnit,
            'province_name' => $this->provinceName ?: null,
            'district_name' => $this->districtName ?: null,
            'address' => $this->address ?: null,
            'contact_name' => $this->contactName ?: null,
            'contact_phone' => $this->contactPhone ?: null,
            'contact_zalo' => $this->contactZalo ?: null,
            'description' => $this->description ?: null,
            'youtube_link' => $this->youtubeLink ?: null,
            'images' => $images,
            'avatar' => $images[0] ?? null,
            'status' => $this->statusValue,
            'vip_tier' => $this->vipTier,
            'is_sold' => $this->isSold,
        ];

        if ($this->editingId) {
            $v = VehicleListingModel::findOrFail($this->editingId);
            $v->update($data);
        } else {
            $data['user_id'] = auth()->id();
            $data['code'] = $this->makeCode($this->vehicleType);
            $data['published_at'] = now();
            $data['expires_at'] = now()->addDays(60);
            $v = VehicleListingModel::create($data);
        }

        // Slug ổn định dựa trên id (giống module BĐS).
        $v->slug = Str::slug($v->title) . '-' . $v->id;
        $v->save();

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', ['message' => 'Đã lưu tin xe.', 'type' => 'success']);
    }

    public function deleteVehicle(int $id): void
    {
        VehicleListingModel::findOrFail($id)->delete();
        $this->dispatch('toast', ['message' => 'Đã xóa tin xe.', 'type' => 'success']);
    }

    public function toggleSold(int $id): void
    {
        $v = VehicleListingModel::findOrFail($id);
        $v->is_sold = ! $v->is_sold;
        $v->save();
        $this->dispatch('toast', ['message' => $v->is_sold ? 'Đã đánh dấu Đã bán.' : 'Đã mở bán lại.', 'type' => 'success']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'brand', 'modelName', 'year', 'mileage', 'transmission',
            'fuelType', 'engineCapacity', 'color', 'seats', 'origin', 'price',
            'provinceName', 'districtName', 'address', 'contactName', 'contactPhone',
            'contactZalo', 'description', 'youtubeLink', 'imagesText', 'isSold',
        ]);
        $this->type = 'Cần bán';
        $this->vehicleType = 'car';
        $this->condition = 'used';
        $this->priceUnit = 'Triệu';
        $this->statusValue = 'active';
        $this->vipTier = 'normal';
        $this->resetValidation();
    }

    private function makeCode(string $vehicleType): string
    {
        $prefix = $vehicleType === 'motorbike' ? 'XM' : 'OT';
        do {
            $code = $prefix . '-' . strtoupper(Str::random(7));
        } while (VehicleListingModel::where('code', $code)->exists());

        return $code;
    }

    public function render()
    {
        $query = VehicleListingModel::query()->latest();

        if ($this->search !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('model_name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }
        if ($this->filterVehicleType !== '') {
            $query->where('vehicle_type', $this->filterVehicleType);
        }
        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.vehicle-listing', [
            'vehicles' => $query->paginate(12),
            'vehicleTypes' => VehicleListingModel::VEHICLE_TYPES,
            'transmissions' => VehicleListingModel::TRANSMISSIONS,
            'fuelTypes' => VehicleListingModel::FUEL_TYPES,
            'conditions' => VehicleListingModel::CONDITIONS,
            'origins' => VehicleListingModel::ORIGINS,
            'brandOptions' => $this->vehicleType === 'motorbike'
                ? VehicleListingModel::MOTORBIKE_BRANDS
                : VehicleListingModel::CAR_BRANDS,
        ])->layout('components.layouts.blog', ['title' => 'Quản lý tin xe']);
    }
}
