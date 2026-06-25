<?php

namespace App\Livewire;

use App\Models\VehicleListing as VehicleModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Quản lý tin đăng XE CỘ (ô tô / xe máy) trong CMS.
 *
 * Mô phỏng y theo RealEstateListing: grid card 2 cột, popup tạo/sửa, upload ảnh
 * (avatar + slider) qua Alpine + window.compressImage, location cascade.
 *
 * LƯU Ý: Model trùng tên với component nên alias là VehicleModel.
 */
class VehicleListing extends Component
{
    use WithPagination, WithFileUploads;

    protected $locationData = null;

    protected function getLocationData()
    {
        if ($this->locationData === null) {
            $this->locationData = Cache::remember('vietnam_locations_full', 86400, function () {
                $path = 'locations/all_vietnam.json';
                if (Storage::disk('local')->exists($path)) {
                    return json_decode(Storage::disk('local')->get($path), true);
                }
                return [];
            });
        }
        return $this->locationData;
    }

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- Popup state ---
    public $showCreatePopup = false;
    public $showMediaPopup = false;
    public $selectedListingId = null;

    // --- Filters ---
    public $filter_type; // Cần bán / Cho thuê / Cần mua
    public $filter_vehicle_type; // car / motorbike
    public $filter_brand;
    public $filter_price_min;
    public $filter_price_max;
    public $filter_province;
    public $filter_district;
    public $filter_is_sold;
    public $filter_month;
    public $filter_year;
    public $filter_date_from;
    public $filter_date_to;
    public $filter_districts = [];

    // --- Form fields (generic) ---
    public $title;
    public $type = 'Cần bán';
    public $contact_type;
    public $contact_phone;
    public $code;
    public $is_sold = false;
    public $province_id;
    public $district_id;
    public $ward_id;
    public $address;
    public $description;
    public $status = 'active';
    public $vip_tier = 'normal';

    // --- Form fields (vehicle-specific) ---
    public $vehicle_type = 'car';
    public $brand;
    public $model_name;
    public $year;
    public $mileage;
    public $transmission;
    public $fuel_type;
    public $engine_capacity;
    public $color;
    public $seats;
    public $condition = 'used';
    public $origin;
    public $price;
    public $price_unit = 'Triệu';
    public $youtube_link;

    // --- Media ---
    public $avatar;
    public $tempAvatar;
    public $images = [];
    public $tempImages = [];

    // --- Location options ---
    public $districts = [];
    public $wards = [];

    public $isAdmin = false;

    public function mount()
    {
        $this->filter_province = null;
        $this->loadFilterDistricts();

        $user = auth()->user();
        $this->isAdmin = $user?->isAdmin() ?? false;
    }

    // Reuse province constant from RealEstateListing in the view.

    // ====================== IMAGE UPLOAD HOOKS ======================

    public function updatedTempImages()
    {
        if (count($this->tempImages) > 0) {
            foreach ($this->tempImages as $temp) {
                try {
                    $originalName = $temp->getClientOriginalName();
                    $filenameOnly = pathinfo($originalName, PATHINFO_FILENAME);
                    $extension = $temp->getClientOriginalExtension();

                    $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filenameOnly);
                    $uniqueSuffix = time() . '_' . substr(uniqid(), -4);
                    $filename = $safeFilename . '_' . $uniqueSuffix . '.' . $extension;

                    $path = $temp->storeAs(date('Y/m'), $filename, ['disk' => 's3', 'visibility' => 'public']);

                    $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path;

                    \App\Models\File::create([
                        'folder_id' => null,
                        'name' => $filename,
                        'path' => $path,
                        'disk' => 's3',
                        'mime_type' => $temp->getMimeType(),
                        'size' => $temp->getSize(),
                        'metadata' => [
                            'source' => 'vehicle_quick_upload',
                            'public_url' => $publicUrl,
                        ],
                        'user_id' => auth()->id(),
                    ]);

                    $this->images[] = $publicUrl;
                } catch (\Exception $e) {
                    \Log::error('Vehicle Image S3 Upload Error: ' . $e->getMessage());
                }
            }

            sleep(60);

            $this->tempImages = [];
        }
    }

    public function updatedTempAvatar()
    {
        if ($this->tempAvatar) {
            try {
                $temp = $this->tempAvatar;
                $originalName = $temp->getClientOriginalName();
                $filenameOnly = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = $temp->getClientOriginalExtension();

                $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filenameOnly);
                $uniqueSuffix = time() . '_' . substr(uniqid(), -4);
                $filename = 'avatar_' . $safeFilename . '_' . $uniqueSuffix . '.' . $extension;

                $path = $temp->storeAs(date('Y/m'), $filename, ['disk' => 's3', 'visibility' => 'public']);
                $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path;

                \App\Models\File::create([
                    'folder_id' => null,
                    'name' => $filename,
                    'path' => $path,
                    'disk' => 's3',
                    'mime_type' => $temp->getMimeType(),
                    'size' => $temp->getSize(),
                    'metadata' => [
                        'source' => 'vehicle_avatar_upload',
                        'public_url' => $publicUrl,
                    ],
                    'user_id' => auth()->id(),
                ]);

                sleep(60);

                $this->avatar = $publicUrl;
                $this->tempAvatar = null;
            } catch (\Exception $e) {
                \Log::error('Vehicle Avatar S3 Upload Error: ' . $e->getMessage());
            }
        }
    }

    public function removeImage($index)
    {
        array_splice($this->images, $index, 1);
    }

    public function removeTempImage($index)
    {
        array_splice($this->tempImages, $index, 1);
    }

    public function removeAvatar()
    {
        $this->avatar = null;
    }

    public function setAvatarFromImage($index)
    {
        if (isset($this->images[$index])) {
            $this->avatar = $this->images[$index];
            $this->dispatch('toast', ['message' => 'Đã chọn ảnh làm đại diện!', 'type' => 'success']);
        }
    }

    public function handleMediaSelected($data)
    {
        $newImages = $data['images'] ?? [];
        $this->images = array_merge($this->images, $newImages);
        $this->showMediaPopup = false;
    }

    // ====================== LOCATION CASCADE ======================

    public function updatedProvinceId($value)
    {
        $this->districts = [];
        $this->wards = [];
        $this->district_id = null;
        $this->ward_id = null;

        if ($value) {
            $this->fetchDistricts($value);
        }
    }

    public function updatedDistrictId($value)
    {
        $this->wards = [];
        $this->ward_id = null;

        if ($value) {
            $this->fetchWards($value);
        }
    }

    public function updatedFilterProvince($value)
    {
        $this->filter_districts = [];
        $this->filter_district = null;

        if ($value) {
            $this->fetchFilterDistricts($value);
        }
    }

    public function loadFilterDistricts()
    {
        if ($this->filter_province) {
            $this->filter_districts = [];
            $this->filter_district = null;
            $this->fetchFilterDistricts($this->filter_province);
        }
    }

    protected function fetchDistricts($provinceId)
    {
        $data = $this->getLocationData();
        $this->districts = [];

        if (isset($data[$provinceId]['districts'])) {
            foreach ($data[$provinceId]['districts'] as $id => $district) {
                $this->districts[$id] = $district['name'];
            }
        }
    }

    protected function fetchWards($districtId)
    {
        $data = $this->getLocationData();
        $this->wards = [];

        $provinceId = $this->province_id;
        if (isset($data[$provinceId]['districts'][$districtId]['wards'])) {
            $this->wards = $data[$provinceId]['districts'][$districtId]['wards'];
        }
    }

    protected function fetchFilterDistricts($provinceId)
    {
        $data = $this->getLocationData();
        $this->filter_districts = [];

        if (isset($data[$provinceId]['districts'])) {
            foreach ($data[$provinceId]['districts'] as $id => $district) {
                $this->filter_districts[$id] = $district['name'];
            }
        }
    }

    public function clearFilters()
    {
        $this->filter_type = null;
        $this->filter_vehicle_type = null;
        $this->filter_brand = null;
        $this->filter_price_min = null;
        $this->filter_price_max = null;
        $this->filter_province = null;
        $this->filter_district = null;
        $this->filter_is_sold = null;
        $this->filter_month = null;
        $this->filter_year = null;
        $this->filter_date_from = null;
        $this->filter_date_to = null;
        $this->filter_districts = [];
    }

    // ====================== CODE GENERATION ======================

    protected function generateListingCode($vehicleType)
    {
        $prefix = $vehicleType === 'motorbike' ? 'XM' : 'OT';

        for ($i = 0; $i < 5; $i++) {
            $random = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $random;

            if (!VehicleModel::where('code', $code)->exists()) {
                return $code;
            }
        }

        return $prefix . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ====================== CRUD ======================

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

    public function resetForm()
    {
        $this->selectedListingId = null;
        $this->reset([
            'title', 'type', 'contact_type', 'contact_phone', 'code', 'is_sold',
            'address', 'description', 'brand', 'model_name', 'year', 'mileage',
            'transmission', 'fuel_type', 'engine_capacity', 'color', 'seats',
            'origin', 'price', 'youtube_link', 'images', 'tempImages',
            'avatar', 'tempAvatar', 'province_id', 'district_id', 'ward_id',
        ]);
        $this->is_sold = false;
        $this->type = 'Cần bán';
        $this->vehicle_type = 'car';
        $this->condition = 'used';
        $this->price_unit = 'Triệu';
        $this->status = 'active';
        $this->vip_tier = 'normal';
        $this->districts = [];
        $this->wards = [];
    }

    public function editListing($id)
    {
        $listing = VehicleModel::find($id);
        if (!$listing) {
            return;
        }

        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Bạn không có quyền sửa tin này!', 'type' => 'error']);
            return;
        }

        $this->selectedListingId = $id;

        $this->title = $listing->title;
        $this->type = $listing->type ?: 'Cần bán';
        $this->contact_type = $listing->contact_type;
        $this->contact_phone = $listing->contact_phone;
        $this->code = $listing->code;
        $this->is_sold = $listing->is_sold ?? false;
        $this->status = $listing->status ?: 'active';
        $this->vip_tier = $listing->vip_tier ?: 'normal';

        $this->vehicle_type = $listing->vehicle_type ?: 'car';
        $this->brand = $listing->brand;
        $this->model_name = $listing->model_name;
        $this->year = $listing->year;
        $this->mileage = $listing->mileage;
        $this->transmission = $listing->transmission;
        $this->fuel_type = $listing->fuel_type;
        $this->engine_capacity = $listing->engine_capacity;
        $this->color = $listing->color;
        $this->seats = $listing->seats;
        $this->condition = $listing->condition ?: 'used';
        $this->origin = $listing->origin;

        $this->province_id = $listing->province_id;
        if ($this->province_id) {
            $this->fetchDistricts($this->province_id);
        }
        $this->district_id = $listing->district_id;
        if ($this->district_id) {
            $this->fetchWards($this->district_id);
        }
        $this->ward_id = $listing->ward_id;
        $this->address = $listing->address;

        $this->price = $listing->price !== null ? number_format((float) $listing->price, 0, ',', '.') : null;
        $this->price_unit = $listing->price_unit ?: 'Triệu';

        $this->description = $listing->description;
        $this->youtube_link = $listing->youtube_link;
        $this->images = is_array($listing->images) ? $listing->images : [];
        $this->avatar = $listing->avatar;

        $this->showCreatePopup = true;
    }

    public function saveListing()
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền tạo/sửa tin xe!', 'type' => 'error']);
            return;
        }

        // Normalize price (VN format -> float)
        $priceValue = $this->normalizeCurrency($this->price);

        // Normalize numeric ints
        $year = ($this->year === '' || $this->year === null) ? null : (int) preg_replace('/[^0-9]/', '', (string) $this->year);
        $mileage = ($this->mileage === '' || $this->mileage === null) ? null : (int) preg_replace('/[^0-9]/', '', (string) $this->mileage);
        $seats = ($this->seats === '' || $this->seats === null) ? null : (int) preg_replace('/[^0-9]/', '', (string) $this->seats);

        $rules = [
            'title' => 'required',
            'vehicle_type' => 'required|in:car,motorbike',
            'youtube_link' => 'nullable|url|max:2000',
        ];

        if ($this->type !== 'Cần mua') {
            $rules['province_id'] = 'required';
        }

        $this->validate($rules);

        // Auto-generate code if empty
        if (!$this->selectedListingId && empty($this->code)) {
            $this->code = $this->generateListingCode($this->vehicle_type);
        }

        $data = [
            'title' => $this->title,
            'type' => $this->type,
            'contact_type' => $this->contact_type,
            'contact_phone' => $this->contact_phone,
            'code' => $this->code,
            'is_sold' => $this->is_sold,
            'status' => $this->status,
            'vip_tier' => $this->vip_tier,

            'vehicle_type' => $this->vehicle_type,
            'brand' => $this->brand ?: null,
            'model_name' => $this->model_name ?: null,
            'year' => $year,
            'mileage' => $mileage,
            'transmission' => $this->transmission ?: null,
            'fuel_type' => $this->fuel_type ?: null,
            'engine_capacity' => $this->engine_capacity ?: null,
            'color' => $this->color ?: null,
            'seats' => $this->vehicle_type === 'car' ? $seats : null,
            'condition' => $this->condition ?: null,
            'origin' => $this->origin ?: null,

            'price' => $priceValue,
            'price_unit' => $this->price_unit,

            'province_id' => $this->province_id,
            'district_id' => $this->district_id,
            'ward_id' => $this->ward_id,
            'province_name' => \App\Livewire\RealEstateListing::PROVINCES[$this->province_id] ?? null,
            'district_name' => $this->districts[$this->district_id] ?? null,
            'ward_name' => $this->wards[$this->ward_id] ?? null,
            'address' => $this->address,

            'description' => $this->description,
            'youtube_link' => $this->youtube_link ?: null,
            'images' => $this->images,
            'avatar' => $this->avatar,
            'user_id' => auth()->id(),
        ];

        try {
            if ($this->selectedListingId) {
                $listing = VehicleModel::findOrFail($this->selectedListingId);
                $listing->update($data);
                $message = 'Đã cập nhật tin xe thành công!';
            } else {
                $data['published_at'] = now();
                $data['expires_at'] = now()->addDays(60);
                $listing = VehicleModel::create($data);
                $message = 'Đã đăng tin xe thành công!';
            }

            // Stable slug based on id.
            $listing->slug = Str::slug($listing->title) . '-' . $listing->id;
            $listing->save();

            $this->dispatch('toast', ['message' => $message, 'type' => 'success']);
            $this->closeCreatePopup();
            $this->clearFilters();
            $this->resetPage();
        } catch (\Exception $e) {
            \Log::error('Save Vehicle Error: ' . $e->getMessage());
            $this->dispatch('toast', ['message' => 'Có lỗi xảy ra: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function deleteListing($id)
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền xóa!', 'type' => 'error']);
            return;
        }

        $listing = VehicleModel::find($id);
        if ($listing) {
            $listing->delete();
            $this->dispatch('toast', ['message' => 'Đã xóa tin xe!', 'type' => 'success']);
        }
    }

    public function toggleSold($id)
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền thực hiện!', 'type' => 'error']);
            return;
        }

        $listing = VehicleModel::find($id);
        if ($listing) {
            $listing->is_sold = !$listing->is_sold;
            $listing->save();
            $this->dispatch('toast', [
                'message' => $listing->is_sold ? 'Đã đánh dấu Đã bán.' : 'Đã mở bán lại.',
                'type' => 'success',
            ]);
        }
    }

    protected function normalizeCurrency($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = str_replace(['.', ' '], '', (string) $value);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float) $clean : null;
    }

    public function downloadSingleImage($url)
    {
        if (!$url) {
            return;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get($url);
            if ($response->successful()) {
                $filename = basename(parse_url($url, PHP_URL_PATH));
                if (!$filename || $filename == '') {
                    $filename = 'image.jpg';
                }
                return response()->streamDownload(function () use ($response) {
                    echo $response->body();
                }, $filename, ['Content-Type' => $response->header('Content-Type') ?? 'image/jpeg']);
            }
        } catch (\Exception $e) {
        }

        $this->dispatch('toast', ['message' => 'Lỗi tải ảnh.', 'type' => 'error']);
    }

    public function render()
    {
        $query = VehicleModel::with('user')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                if (preg_match('/^[A-ZĐ]{1,3}\d+$/i', $term)) {
                    $q->where('code', $term)
                        ->orWhere('title', 'like', '%' . $term . '%');
                } else {
                    $q->where('title', 'like', '%' . $term . '%')
                        ->orWhere('brand', 'like', '%' . $term . '%')
                        ->orWhere('model_name', 'like', '%' . $term . '%')
                        ->orWhere('code', 'like', '%' . $term . '%')
                        ->orWhere('contact_phone', 'like', '%' . $term . '%');
                }
            });
        }

        if (!empty($this->filter_type)) {
            $query->where('type', $this->filter_type);
        }
        if (!empty($this->filter_vehicle_type)) {
            $query->where('vehicle_type', $this->filter_vehicle_type);
        }
        if (!empty($this->filter_brand)) {
            $query->where('brand', $this->filter_brand);
        }
        if (!empty($this->filter_price_min)) {
            $query->where('price', '>=', str_replace('.', '', $this->filter_price_min));
        }
        if (!empty($this->filter_price_max)) {
            $query->where('price', '<=', str_replace('.', '', $this->filter_price_max));
        }
        if (!empty($this->filter_province)) {
            $query->where('province_id', $this->filter_province);
        }
        if (!empty($this->filter_district)) {
            $query->where('district_id', $this->filter_district);
        }
        if ($this->filter_is_sold !== null && $this->filter_is_sold !== '') {
            $query->where('is_sold', $this->filter_is_sold);
        }
        if (!empty($this->filter_month)) {
            $query->whereMonth('created_at', $this->filter_month);
        }
        if (!empty($this->filter_year)) {
            $query->whereYear('created_at', $this->filter_year);
        }
        if (!empty($this->filter_date_from)) {
            $query->whereDate('created_at', '>=', $this->filter_date_from);
        }
        if (!empty($this->filter_date_to)) {
            $query->whereDate('created_at', '<=', $this->filter_date_to);
        }

        $isMobile = isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/(android|blackberry|iphone|ipod|mini|mobi|palm|phone)/i", $_SERVER['HTTP_USER_AGENT']);
        $perPage = $isMobile ? 20 : 12;

        $vehicles = $query->paginate($perPage)->onEachSide(0);

        return view('livewire.vehicle-listing', [
            'vehicles' => $vehicles,
            'vehicleTypes' => VehicleModel::VEHICLE_TYPES,
            'transmissions' => VehicleModel::TRANSMISSIONS,
            'fuelTypes' => VehicleModel::FUEL_TYPES,
            'conditions' => VehicleModel::CONDITIONS,
            'origins' => VehicleModel::ORIGINS,
            'brandOptions' => $this->vehicle_type === 'motorbike'
                ? VehicleModel::MOTORBIKE_BRANDS
                : VehicleModel::CAR_BRANDS,
            'filterBrandOptions' => $this->filter_vehicle_type === 'motorbike'
                ? VehicleModel::MOTORBIKE_BRANDS
                : VehicleModel::CAR_BRANDS,
        ])->layout('components.layouts.blog', ['title' => 'Quản lý tin xe']);
    }
}
