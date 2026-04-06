<?php

namespace App\Livewire;

use App\Models\RealEstateListing as ListingModel;
use App\Models\RealEstateListingSale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ListingsExport;

use Illuminate\Support\Facades\Storage;

class RealEstateListing extends Component
{
    use WithPagination, WithFileUploads;

    public $locationData = null;

    protected function getLocationData()
    {
        if ($this->locationData === null) {
            $path = 'locations/all_vietnam.json';
            if (Storage::disk('local')->exists($path)) {
                $this->locationData = json_decode(Storage::disk('local')->get($path), true);
            } else {
                $this->locationData = [];
            }
        }
        return $this->locationData;
    }
    
    protected $queryString = [
        'search' => ['except' => ''],
        'filter_phone' => ['except' => ''],
    ];

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterPhone()
    {
        $this->resetPage();
    }

    public $showCreatePopup = false;
    public $showMediaPopup = false;
    public $showDetailPopup = false;
    public $showSoldPopup = false;
    public $selectedListing = null;
    public $selectedListingId = null;

    // --- New Sale State (Rebuild) ---
    public $saleListingId = null;
    public $saleProjectName = '';
    public $saleActualPrice = '';
    public $saleRevenuePercent = '';
    public $saleBonusAmount = '';
    public $sale_members = []; // Array of ['user_id' => ID, 'received_amount' => AMOUNT]
    
    // Computed/Display only
    public $saleNetAmount = 0;
    public $saleRemainingAmount = 0;
    public $saleRevenueAmount = 0;
    public $saleBonusNumeric = 0;

    // Filters
    public $filter_price_min;
    public $filter_price_max;
    public $filter_province;
    public $filter_district;
    public $filter_ward;
    public $filter_property_type;
    public $filter_type; // New filter for Sale/Rent
    public $filter_is_sold; // Filter for sold status
    public $filter_phone; // Filter by contact phone
    public $filter_month; // Filter by month
    public $filter_year; // Filter by year
    public $filter_date_from;
    public $filter_date_to;
    public $filter_area_min;
    public $filter_area_max;
    public $filter_districts = [];
    public $filter_wards = [];

    // Form Fields
    public $title;
    public $type = 'Cần bán';
    public $contact_type; // Chủ or Môi giới
    public $contact_phone;
    public $house_password; // Alphanumeric password
    public $code; // Auto-generated listing code
    public $is_sold = false; // Sold status
    public $province_id;
    public $district_id;
    public $ward_id;
    public $property_type = 0; // Default to "Chọn loại..."
    public $address;

    const PROPERTY_TYPES = [
        110 => 'Bất động sản khác',
        102 => 'Biệt thự',
        103 => 'Căn hộ – chung cư',
        104 => 'Đất',
        105 => 'Đất nền dự án',
        106 => 'Mặt tiền',
        107 => 'Nhà mặt phố',
        111 => 'Nhà mặt phố (LG 4M-5M)',
        108 => 'Nhà riêng',
        109 => 'Trang trại',
        112 => 'Khách sạn',
        113 => 'Nhà nghỉ',
        114 => 'Homestay',
    ];

    const PREFIX_MAP = [
        110 => '#K',
        102 => '#BT',
        103 => '#CH',
        104 => '#D',
        105 => '#DA',
        106 => '#MT',
        107 => '#MP',
        111 => '#MPN',
        108 => '#NR',
        109 => '#TT',
        112 => '#KS',
        113 => '#NN',
        114 => '#HS',
    ];

    const DIRECTIONS = [
        1 => 'Đông',
        2 => 'Tây',
        3 => 'Nam',
        4 => 'Bắc',
        5 => 'Đông bắc',
        6 => 'Đông nam',
        7 => 'Tây bắc',
        8 => 'Tây nam',
    ];
    public $area;
    public $price;
    public $price_unit = 'Tỷ';
    public $floors;
    public $bedrooms;
    public $toilets;
    public $direction = 'Đông Nam';
    public $front_width;
    public $road_width;
    public $youtube_link;
    public $youtube_link_short;
    public $facebook_link;
    public $facebook_video_link;
    public $google_map_link;
    public $tiktok_link;
    public $description;
    public $images = []; // Array of URLs
    public $tempImages = []; // For new uploads
    public $avatar;
    public $tempAvatar; // For avatar upload
    public $reporter_id; // For 'Người đưa tin'

    
    public $revealedPhones = [];
    public $showPinModal = false;
    public $pinListingId = null;
    public $viewPinInput = '';

    // New Customer Integration
    public $customer_selection_mode = 'existing';
    public $new_customer_name;
    public $new_customer_phone;
    public $new_customer_status = 'khach_mua_o';

    // Dynamic Options
    public $districts = [];
    public $wards = [];

    public $isAdmin = false;
    public $userPropertyTypes = [];

    public function toggleCustomerMode($mode)
    {
        $this->customer_selection_mode = $mode;
    }

    public function handleMediaSelected($data)
    {
        $newImages = $data['images'] ?? [];
        // Merge with existing
        $this->images = array_merge($this->images, $newImages);
        $this->showMediaPopup = false;
    }

    public function removeImage($index)
    {
        array_splice($this->images, $index, 1);
    }

    public function removeTempImage($index)
    {
        array_splice($this->tempImages, $index, 1);
    }

    public function mount()
    {
        // Load provinces for filters - View uses constant directly

        // Capture filter_phone from query parameter (from customer listings link)
        $this->filter_phone = request('filter_phone');

        $this->filter_province = null;
        $this->loadFilterDistricts();

        // Set permissions
        $user = auth()->user();
        $this->isAdmin = $user?->isAdmin() ?? false;
        $this->userPropertyTypes = $user?->property_types ?? [];
    }

    const PROVINCES = [
        '89' => 'An Giang',
        '77' => 'Bà Rịa - Vũng Tàu',
        '74' => 'Bình Dương',
        '70' => 'Bình Phước',
        '60' => 'Bình Thuận',
        '52' => 'Bình Định',
        '95' => 'Bạc Liêu',
        '24' => 'Bắc Giang',
        '06' => 'Bắc Kạn',
        '27' => 'Bắc Ninh',
        '83' => 'Bến Tre',
        '04' => 'Cao Bằng',
        '96' => 'Cà Mau',
        '92' => 'Cần Thơ',
        '64' => 'Gia Lai',
        '17' => 'Hoà Bình',
        '02' => 'Hà Giang',
        '35' => 'Hà Nam',
        '01' => 'Hà Nội',
        '42' => 'Hà Tĩnh',
        '33' => 'Hưng Yên',
        '30' => 'Hải Dương',
        '31' => 'Hải Phòng',
        '93' => 'Hậu Giang',
        '79' => 'Hồ Chí Minh',
        '56' => 'Khánh Hòa',
        '91' => 'Kiên Giang',
        '62' => 'Kon Tum',
        '12' => 'Lai Châu',
        '80' => 'Long An',
        '10' => 'Lào Cai',
        '68' => 'Lâm Đồng',
        '20' => 'Lạng Sơn',
        '36' => 'Nam Định',
        '40' => 'Nghệ An',
        '37' => 'Ninh Bình',
        '58' => 'Ninh Thuận',
        '25' => 'Phú Thọ',
        '54' => 'Phú Yên',
        '44' => 'Quảng Bình',
        '49' => 'Quảng Nam',
        '51' => 'Quảng Ngãi',
        '22' => 'Quảng Ninh',
        '45' => 'Quảng Trị',
        '94' => 'Sóc Trăng',
        '14' => 'Sơn La',
        '38' => 'Thanh Hóa',
        '34' => 'Thái Bình',
        '19' => 'Thái Nguyên',
        '46' => 'Thừa Thiên Huế',
        '82' => 'Tiền Giang',
        '84' => 'Trà Vinh',
        '08' => 'Tuyên Quang',
        '72' => 'Tây Ninh',
        '86' => 'Vĩnh Long',
        '26' => 'Vĩnh Phúc',
        '15' => 'Yên Bái',
        '11' => 'Điện Biên',
        '48' => 'Đà Nẵng',
        '66' => 'Đắk Lắk',
        '67' => 'Đắk Nông',
        '75' => 'Đồng Nai',
        '87' => 'Đồng Tháp',
    ];

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
        $this->filter_wards = [];
        $this->filter_district = null;
        $this->filter_ward = null;

        if ($value) {
            $this->fetchFilterDistricts($value);
        }
    }

    public function updatedFilterDistrict($value)
    {
        $this->filter_wards = [];
        $this->filter_ward = null;

        if ($value) {
            $this->fetchFilterWards($value);
        }
    }

    public function clearFilters()
    {
        $this->filter_price_min = null;
        $this->filter_price_max = null;
        $this->filter_province = null;
        $this->filter_district = null;
        $this->filter_ward = null;
        $this->filter_property_type = null;
        $this->filter_type = null;
        $this->filter_is_sold = null;
        $this->filter_month = null;
        $this->filter_year = null;
        $this->filter_phone = null;
        $this->filter_date_from = null;
        $this->filter_date_to = null;
        $this->filter_area_min = null;
        $this->filter_area_max = null;
        $this->filter_districts = [];
        $this->filter_wards = [];
    }

    public function loadFilterDistricts()
    {
        if ($this->filter_province) {
            $this->filter_districts = [];
            $this->filter_wards = [];
            $this->filter_district = null;
            $this->filter_ward = null;
            $this->fetchFilterDistricts($this->filter_province);
        }
    }

    public function loadFilterWards()
    {
        if ($this->filter_district) {
            $this->filter_wards = [];
            $this->filter_ward = null;
            $this->fetchFilterWards($this->filter_district);
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
        
        // We need to find the district in the current province
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

    protected function fetchFilterWards($districtId)
    {
        $data = $this->getLocationData();
        $this->filter_wards = [];
        
        $provinceId = $this->filter_province;
        if (isset($data[$provinceId]['districts'][$districtId]['wards'])) {
            $this->filter_wards = $data[$provinceId]['districts'][$districtId]['wards'];
        }
    }

    public function updatedPropertyType($value)
    {
        if (!$this->selectedListingId && $value) {
            $this->code = $this->generateListingCode($value);
        }
    }

    protected function generateListingCode($propertyType)
    {
        $prefix = self::PREFIX_MAP[$propertyType] ?? '#RE';

        // Try up to 5 times to generate a unique code
        for ($i = 0; $i < 5; $i++) {
            $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $random;

            // Check uniqueness
            if (!ListingModel::where('code', $code)->exists()) {
                return $code;
            }
        }

        // Fallback if collision
        return $prefix . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }


    public function saveListing()
    {
        // Permission check - only admin can create/edit listings
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền tạo/sửa tin đăng!', 'type' => 'error']);
            return;
        }

        // Robust Sanitization for Price
        if ($this->price === '' || $this->price === null) {
            $this->price = null;
        } else {
            // Remove non-numeric chars except dot and comma
            $cleanPrice = preg_replace('/[^0-9,.]/', '', $this->price);
            // Vietnamese format: 1.000.000 or 1,5
            // Remove thousand separator (.)
            $cleanPrice = str_replace('.', '', $cleanPrice);
            // Replace decimal separator (,) with (.)
            $this->price = str_replace(',', '.', $cleanPrice);
        }

        // Robust Sanitization for other numeric fields
        $numericFields = ['area', 'front_width', 'road_width'];
        foreach ($numericFields as $field) {
            $this->$field = $this->normalizeDecimal($this->$field);
        }

        $intFields = ['floors', 'bedrooms', 'toilets'];
        foreach ($intFields as $field) {
            if ($this->$field === '') {
                $this->$field = null;
            }
        }

        // Sanitize Social Links
        if ($this->facebook_link === '')
            $this->facebook_link = null;
        if ($this->facebook_video_link === '')
            $this->facebook_video_link = null;
        if ($this->google_map_link === '')
            $this->google_map_link = null;
        if ($this->tiktok_link === '')
            $this->tiktok_link = null;

        $rules = [
            'title' => 'required',
            'facebook_link' => 'nullable|url|max:2000',
            'facebook_video_link' => 'nullable|url|max:2000',
            'google_map_link' => 'nullable|url|max:2000',
            'tiktok_link' => 'nullable|url|max:2000',
            'reporter_id' => 'nullable|exists:users,id',
        ];

        if ($this->type !== 'Cần mua') {
            $rules['province_id'] = 'required';
            $rules['price'] = 'required|numeric';
        }

        $this->validate($rules);

        // Auto-generate code if creating new listing and code is empty
        if (!$this->selectedListingId && empty($this->code)) {
            $this->code = $this->generateListingCode($this->property_type ?? 110); // Default to Other if not set
        }

        // Process images with Media Sync
        if (count($this->tempImages) > 0) {
            foreach ($this->tempImages as $temp) {
                // ===== UNIQUE FILENAME TO PREVENT OVERWRITES =====
                $originalName = $temp->getClientOriginalName();
                $filenameOnly = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = $temp->getClientOriginalExtension();

                // Sanitize + Add unique suffix (timestamp + random)
                $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filenameOnly);
                $uniqueSuffix = time() . '_' . substr(uniqid(), -4);
                $filename = $safeFilename . '_' . $uniqueSuffix . '.' . $extension;

                // Match Media Manager structure: YYYY/MM/UniqueFilename
                $path = $temp->storeAs(date('Y/m'), $filename, ['disk' => 's3', 'visibility' => 'public']);

                $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path;

                // Create File Record for Media Manager
                $file = \App\Models\File::create([
                    'folder_id' => null, // Root folder or specific listing folder
                    'name' => $filename, // Store UNIQUE filename (not original)
                    'path' => $path,
                    'disk' => 's3',
                    'mime_type' => $temp->getMimeType(),
                    'size' => $temp->getSize(),
                    'metadata' => [
                        'source' => 'real_estate_quick_upload',
                        'public_url' => $publicUrl
                    ]
                ]);

                // Use the URL from the File model or generate it
                // We use the public URL directly to ensure it works
                $this->images[] = $publicUrl;
            }
            $this->tempImages = [];
        }

        $data = [
            'title' => $this->title,
            'type' => $this->type,
            'contact_type' => $this->contact_type,
            'contact_phone' => $this->contact_phone,
            'house_password' => $this->house_password,
            'code' => $this->code,
            'is_sold' => $this->is_sold,
            'property_type' => $this->property_type,
            'province_id' => $this->province_id,
            'district_id' => $this->district_id,
            'ward_id' => $this->ward_id,

            // Save Names
            'province_name' => self::PROVINCES[$this->province_id] ?? null,
            'district_name' => $this->districts[$this->district_id] ?? null,
            'ward_name' => $this->wards[$this->ward_id] ?? null,

            'address' => $this->address,
            'area' => $this->area,
            'price' => $this->normalizeCurrency($this->price),
            'price_unit' => $this->price_unit,
            'floors' => $this->floors,
            'bedrooms' => $this->bedrooms,
            'toilets' => $this->toilets,
            'direction' => $this->direction,
            'front_width' => $this->front_width,
            'road_width' => $this->road_width,
            'youtube_link' => $this->youtube_link,
            'youtube_link_short' => $this->youtube_link_short,
            'facebook_link' => $this->facebook_link,
            'facebook_video_link' => $this->facebook_video_link,
            'google_map_link' => $this->google_map_link,
            'tiktok_link' => $this->tiktok_link,
            'description' => $this->description,
            'images' => $this->images,
            'avatar' => $this->avatar,
            'user_id' => auth()->id(),
            'reporter_id' => $this->reporter_id,
        ];

        // Handle Customer Integration
        if ($this->customer_selection_mode === 'new') {
            if (!empty($this->new_customer_phone)) {
                // Check if customer exists
                $existingCustomer = \App\Models\Customer::where('phone', $this->new_customer_phone)->first();
                if ($existingCustomer) {
                    $this->contact_phone = $existingCustomer->phone;
                } else {
                    $this->validate([
                        'new_customer_name' => 'required',
                        'new_customer_phone' => 'required',
                        'new_customer_status' => 'required',
                    ]);

                    $customer = \App\Models\Customer::create([
                        'code' => \App\Models\Customer::generateCode(),
                        'name' => $this->new_customer_name,
                        'phone' => $this->new_customer_phone,
                        'status' => $this->new_customer_status,
                        'assigned_user_id' => auth()->id(),
                    ]);
                    $this->contact_phone = $customer->phone;
                }
            }
        }

        try {
            if ($this->selectedListingId) {
                ListingModel::where('id', $this->selectedListingId)->update($data);
                $message = 'Đã cập nhật tin đăng thành công!';
            } else {
                ListingModel::create($data);
                $message = 'Đã đăng tin thành công!';
            }

            $this->dispatch('toast', ['message' => $message, 'type' => 'success']);

            // Refresh Cache
            $this->refreshCacheVersion();

            $this->closeCreatePopup();

            // Clear filters to ensure the new listing is seen (if it doesn't match current filters)
            $this->clearFilters();

            $this->resetPage();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Save Listing Error: " . $e->getMessage());
            $this->dispatch('toast', ['message' => 'Có lỗi xảy ra: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function editListing($id)
    {
        $listing = ListingModel::find($id);
        if (!$listing)
            return;

        // Permission check
        $user = auth()->user();
        if (!$user || !$user->canEditListing($listing)) {
            $this->dispatch('toast', ['message' => 'Bạn không có quyền sửa tin này!', 'type' => 'error']);
            return;
        }

        $this->selectedListingId = $id;

        $this->title = $listing->title;
        $this->type = $listing->type;
        $this->contact_type = $listing->contact_type;
        $this->contact_phone = $listing->contact_phone;
        $this->house_password = $listing->house_password;
        $this->code = $listing->code;
        $this->is_sold = $listing->is_sold ?? false;
        $this->property_type = $listing->property_type;
        $this->province_id = $listing->province_id;

        // Fetch Dependent Options
        if ($this->province_id)
            $this->fetchDistricts($this->province_id);

        $this->district_id = $listing->district_id;

        if ($this->district_id)
            $this->fetchWards($this->district_id);

        $this->ward_id = $listing->ward_id;
        $this->ward_id = $listing->ward_id;
        $this->address = $listing->address;

        // Format numbers for display
        $this->area = floatval($listing->area);
        $this->price = number_format($listing->price, 0, ',', '.'); // Format as VN currency
        $this->price_unit = $listing->price_unit;

        $this->floors = $listing->floors;
        $this->bedrooms = $listing->bedrooms;
        $this->toilets = $listing->toilets;
        $this->direction = $listing->direction;

        // Format Widths
        $this->front_width = floatval($listing->front_width);
        $this->road_width = floatval($listing->road_width);

        $this->youtube_link = $listing->youtube_link;
        $this->youtube_link_short = $listing->youtube_link_short;
        $this->facebook_link = $listing->facebook_link;
        $this->facebook_video_link = $listing->facebook_video_link;
        $this->google_map_link = $listing->google_map_link;
        $this->tiktok_link = $listing->tiktok_link;
        $this->description = $listing->description;
        $this->images = $listing->images ?? [];
        $this->avatar = $listing->avatar;
        $this->reporter_id = $listing->reporter_id;

        $this->showCreatePopup = true;
    }

    public function viewListingDetail($id)
    {
        $listing = ListingModel::with('sale.soldBy')->find($id);
        if (!$listing)
            return;

        $this->selectedListing = $this->prepareListingForDetail($listing);
        $this->showDetailPopup = true;
    }

    protected function prepareListingForDetail($listing)
    {
        if (!$listing->relationLoaded('sale')) {
            $listing->load(['sale.soldBy', 'sale.members.user', 'user', 'reporter']);
        }
        if (!$listing->relationLoaded('reporter')) {
            $listing->load('reporter');
        }
        if (!$listing->relationLoaded('user')) {
            $listing->load('user');
        }

        $data = $listing->toArray();
        // Prepare slider images for detail view: Avatar first, then others
        if (!empty($data['avatar'])) {
            $images = $data['images'] ?? [];
            if (!is_array($images))
                $images = [];

            // Check if avatar is already in images to avoid duplication
            if (!in_array($data['avatar'], $images)) {
                array_unshift($images, $data['avatar']);
            }
            $data['images'] = $images;
        }
        return $data;
    }

    public function closeDetailPopup()
    {
        $this->showDetailPopup = false;
        $this->selectedListing = null;
    }

    public function editFromDetail()
    {
        if ($this->selectedListing) {
            $listingId = $this->selectedListing['id'];
            $this->closeDetailPopup();
            $this->editListing($listingId);
        }
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

    public function resetForm()
    {
        $this->selectedListingId = null;
        $this->reset(['title', 'type', 'contact_type', 'contact_phone', 'house_password', 'code', 'is_sold', 'address', 'area', 'price', 'description', 'floors', 'bedrooms', 'toilets', 'direction', 'front_width', 'road_width', 'youtube_link', 'facebook_link', 'facebook_video_link', 'google_map_link', 'tiktok_link', 'images', 'province_id', 'district_id', 'ward_id', 'tempImages', 'avatar', 'tempAvatar', 'reporter_id']);
        $this->is_sold = false;
        $this->youtube_link_short = null;
        $this->customer_selection_mode = 'existing';
        $this->reset(['new_customer_name', 'new_customer_phone', 'new_customer_status']);
        $this->districts = [];
        $this->wards = [];
    }

    public function deleteListing($id)
    {
        $user = auth()->user();
        if (!$user || !$user->canDeleteListing()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền xóa!', 'type' => 'error']);
            return;
        }

        $listing = ListingModel::find($id);
        if ($listing) {
            $listing->delete();
            $this->refreshCacheVersion();
            $this->dispatch('toast', ['message' => 'Đã xóa tin đăng!', 'type' => 'success']);
            
            // Close detail popup if it was the one deleted
            if ($this->selectedListing && $this->selectedListing['id'] == $id) {
                $this->closeDetailPopup();
            }
        }
    }


    // --- NEW SALE REBUILD LOGIC ---
    
    public function toggleSold($id)
    {
        $listing = ListingModel::find($id);
        if (!$listing) return;

        if ($listing->is_sold) {
            $listing->update(['is_sold' => false]);
            $this->refreshCacheVersion();
            $this->dispatch('toast', ['message' => 'Đã hủy trạng thái Đã bán.', 'type' => 'info']);
            $this->refreshSelectedListing($id);
            return;
        }

        $this->openSoldPopup($id);
    }

    public function openSoldPopup($id)
    {
        $listing = ListingModel::find($id);
        if (!$listing) return;

        $this->saleListingId = $id;
        $this->saleProjectName = $listing->title;
        // Pre-fill with listing price
        $this->saleActualPrice = number_format((float)$listing->price, 0, ',', '.');
        $this->saleRevenuePercent = 1; // Default 1%
        $this->saleBonusAmount = 0;
        
        // Start with one distributor (the user who posted it)
        $this->sale_members = [
            ['user_id' => $listing->user_id, 'received_amount' => 0]
        ];

        $this->syncSaleAmounts();
        $this->showSoldPopup = true;
    }

    public function closeSoldPopup()
    {
        $this->showSoldPopup = false;
        $this->reset(['saleListingId', 'saleProjectName', 'saleActualPrice', 'saleRevenuePercent', 'saleBonusAmount', 'sale_members']);
    }

    public function syncSaleAmounts()
    {
        $actual = $this->parseNumeric($this->saleActualPrice);
        $percent = (float) str_replace(',', '.', (string)$this->saleRevenuePercent);
        $bonus = $this->parseNumeric($this->saleBonusAmount);
    
        $this->saleRevenueAmount = round(($actual * $percent) / 100, 2);
        $this->saleBonusNumeric = round($bonus, 2);
        $this->saleNetAmount = $this->saleRevenueAmount + $this->saleBonusNumeric;
    
        $distributed = 0;
        if (is_array($this->sale_members)) {
            foreach ($this->sale_members as $m) {
                $distributed += $this->parseNumeric($m['received_amount'] ?? 0);
            }
        }
        $this->saleRemainingAmount = round($this->saleNetAmount - $distributed, 2);
    }

    public function addSaleMember()
    {
        $this->sale_members[] = ['user_id' => null, 'received_amount' => 0];
        $this->syncSaleAmounts();
    }

    public function removeSaleMember($index)
    {
        if (isset($this->sale_members[$index])) {
            unset($this->sale_members[$index]);
            $this->sale_members = array_values($this->sale_members);
            $this->syncSaleAmounts();
        }
    }

    public function updatedSaleActualPrice() { $this->syncSaleAmounts(); }
    public function updatedSaleRevenuePercent() { $this->syncSaleAmounts(); }
    public function updatedSaleBonusAmount() { $this->syncSaleAmounts(); }

    public function saveSoldInformation()
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            $this->dispatch('toast', ['message' => 'Chỉ Admin mới có quyền thực hiện!', 'type' => 'error']);
            return;
        }

        $this->syncSaleAmounts();

        if (abs($this->saleRemainingAmount) > 1) { // 1đ tolerance
            $msg = $this->saleRemainingAmount > 0 
                ? "Còn dư " . number_format($this->saleRemainingAmount, 0,',','.') . " VNĐ chưa chia!" 
                : "Chi vượt quá lợi nhuận " . number_format(abs($this->saleRemainingAmount), 0,',','.') . " VNĐ!";
            $this->dispatch('toast', ['message' => $msg, 'type' => 'error']);
            return;
        }

        if (empty($this->sale_members)) {
            $this->dispatch('toast', ['message' => 'Vui lòng thêm ít nhất một người nhận!', 'type' => 'error']);
            return;
        }

        foreach ($this->sale_members as $index => $m) {
            if (empty($m['user_id'])) {
                $this->dispatch('toast', ['message' => "Dòng " . ($index + 1) . " chưa chọn nhân viên!", 'type' => 'error']);
                return;
            }
        }

        try {
            DB::transaction(function () {
                $listing = ListingModel::findOrFail($this->saleListingId);
                $listing->update(['is_sold' => true]);

                $sale = RealEstateListingSale::updateOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'sold_by_user_id' => $this->sale_members[0]['user_id'], // Primary ref
                        'project_name' => $this->saleProjectName,
                        'actual_price' => $this->parseNumeric($this->saleActualPrice),
                        'revenue_percent' => (float) str_replace(',', '.', (string)$this->saleRevenuePercent),
                        'revenue_amount' => $this->saleRevenueAmount,
                        'bonus_amount' => $this->saleBonusNumeric,
                        'net_received_amount' => $this->saleNetAmount,
                        'sold_at' => now(),
                    ]
                );

                $sale->members()->delete();
                foreach ($this->sale_members as $m) {
                    $sale->members()->create([
                        'user_id' => $m['user_id'],
                        'received_amount' => $this->parseNumeric($m['received_amount']),
                    ]);
                }
            });

            $this->refreshCacheVersion();
            $this->dispatch('toast', ['message' => 'Xác nhận giao dịch thành công!', 'type' => 'success']);
            $listingId = $this->saleListingId;
            $this->closeSoldPopup();
            $this->refreshSelectedListing($listingId);

        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'Lỗi: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    protected function parseNumeric($val): float
    {
        if (is_numeric($val)) return (float) $val;
        $clean = preg_replace('/[^0-9]/', '', (string)$val);
        return $clean === '' ? 0 : (float)$clean;
    }

    protected function refreshSelectedListing($id): void
    {
        if (!$this->selectedListing || (int) $this->selectedListing['id'] !== (int) $id) {
            return;
        }

        $listing = ListingModel::with('sale.soldBy')->find($id);
        if ($listing) {
            $this->selectedListing = $this->prepareListingForDetail($listing);
        }
    }

    protected function uploadFile($file)
    {
        // ===== UNIQUE FILENAME TO PREVENT OVERWRITES =====
        $originalName = $file->getClientOriginalName();
        $filenameOnly = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        // Sanitize + Add unique suffix (timestamp + random)
        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filenameOnly);
        $uniqueSuffix = time() . '_' . substr(uniqid(), -4);
        $filename = $safeFilename . '_' . $uniqueSuffix . '.' . $extension;

        // Match Media Manager structure: YYYY/MM/UniqueFilename
        $path = $file->storeAs(date('Y/m'), $filename, ['disk' => 's3', 'visibility' => 'public']);

        $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path;

        // Create File Record for Media Manager
        \App\Models\File::create([
            'folder_id' => null,
            'name' => $filename,
            'path' => $path,
            'disk' => 's3',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'metadata' => [
                'source' => 'real_estate_quick_upload',
                'public_url' => $publicUrl
            ]
        ]);

        return $publicUrl;
    }

    public function setAvatarFromImage($index)
    {
        if (isset($this->images[$index])) {
            $this->avatar = $this->images[$index];
            $this->dispatch('toast', ['message' => 'Đã chọn ảnh làm đại diện!', 'type' => 'success']);
        }
    }

    protected function getCacheVersion()
    {
        return \Illuminate\Support\Facades\Cache::get('listings_version', time());
    }

    protected function refreshCacheVersion()
    {
        \Illuminate\Support\Facades\Cache::put('listings_version', time(), now()->addDays(1));
    }

    public function render()
    {
        // Generate Cache Key based on filters, page, AND data version
        $filters = [
            'search' => $this->search,
            'price_min' => $this->filter_price_min,
            'price_max' => $this->filter_price_max,
            'province' => $this->filter_province,
            'district' => $this->filter_district,
            'ward' => $this->filter_ward,
            'property_type' => $this->filter_property_type,
            'type' => $this->filter_type,
            'is_sold' => $this->filter_is_sold,
            'month' => $this->filter_month,
            'year' => $this->filter_year,
            'date_from' => $this->filter_date_from,
            'date_to' => $this->filter_date_to,
            'phone' => $this->filter_phone,
            'area_min' => $this->filter_area_min,
            'area_max' => $this->filter_area_max,
            'page' => $this->getPage(),
            'version' => $this->getCacheVersion(), // Include version in key
        ];

        // Serialize filters to create a unique key
        $cacheKey = 'listings_' . md5(json_encode($filters));

        // Cache for 60 seconds (so even if version doesn't change, we still refresh occasionally)
        $listings = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () {
            // Use deterministic sorting: Created At DESC, then ID DESC
            $query = ListingModel::with(['user', 'reporter'])->orderBy('created_at', 'desc')->orderBy('id', 'desc');

            // Auto-filter by user's property types (if not admin)
            $user = auth()->user();
            if ($user && !$user->isAdmin()) {
                if (!empty($user->property_types)) {
                    $query->whereIn('property_type', $user->property_types);
                }

                // Filter by CTV Rank price limits
                $invitesCount = $user->sentInviteLogs()->count();
                $ctvRank = \App\Models\CtvRank::where('min_invites', '<=', $invitesCount)
                    ->orderBy('min_invites', 'desc')
                    ->first();

                if ($ctvRank) {
                    if (!empty($ctvRank->min_price)) {
                        $query->whereRaw("(CASE WHEN price_unit = 'Triệu' THEN CAST(price AS DECIMAL(15,2)) * 1000000 ELSE CAST(price AS DECIMAL(15,2)) * 1000000000 END) >= ?", [$ctvRank->min_price * 1000000000]);
                    }
                    if (!empty($ctvRank->max_price)) {
                        $query->whereRaw("(CASE WHEN price_unit = 'Triệu' THEN CAST(price AS DECIMAL(15,2)) * 1000000 ELSE CAST(price AS DECIMAL(15,2)) * 1000000000 END) <= ?", [$ctvRank->max_price * 1000000000]);
                    }
                } else {
                    $query->whereId(0);
                }
            }

            if (!empty($this->search)) {
            $query->where(function ($q) {
                $term = trim($this->search);
                
                // If term looks like a code (e.g. ND123, CC123, D123), do exact match on code
                // Code pattern: typically 1-3 letters followed by numbers
                if (preg_match('/^[A-ZĐ]{1,3}\d+$/i', $term)) {
                    $q->where('code', $term)
                      ->orWhere('title', 'like', '%' . $term . '%');
                } else {
                    $q->where('title', 'like', '%' . $term . '%')
                        ->orWhere('address', 'like', '%' . $term . '%')
                        ->orWhere('code', 'like', '%' . $term . '%');
                }
            });
        }

            // Price Filters
            if (!empty($this->filter_price_min)) {
                $query->where('price', '>=', str_replace('.', '', $this->filter_price_min));
            }
            if (!empty($this->filter_price_max)) {
                $query->where('price', '<=', str_replace('.', '', $this->filter_price_max));
            }

            // Area Filters
            if (!empty($this->filter_area_min)) {
                $query->where('area', '>=', $this->normalizeDecimal($this->filter_area_min));
            }
            if (!empty($this->filter_area_max)) {
                $query->where('area', '<=', $this->normalizeDecimal($this->filter_area_max));
            }

            // Location Filters
            if (!empty($this->filter_province)) {
                $query->where('province_id', $this->filter_province);
            }
            if (!empty($this->filter_district)) {
                $query->where('district_id', $this->filter_district);
            }
            if (!empty($this->filter_ward)) {
                $query->where('ward_id', $this->filter_ward);
            }
            if (!empty($this->filter_property_type)) {
                $query->where('property_type', $this->filter_property_type);
            }
            if (!empty($this->filter_type)) {
                $query->where('type', $this->filter_type);
            }
            if ($this->filter_is_sold !== null && $this->filter_is_sold !== '') {
                $query->where('is_sold', $this->filter_is_sold);
            }

            // Date Filters
            if (!empty($this->filter_month)) {
                $query->whereMonth('created_at', $this->filter_month);
            }
            if (!empty($this->filter_year)) {
                $query->whereYear('created_at', $this->filter_year);
            }

            // Date Range Filters
            if (!empty($this->filter_date_from)) {
                $query->whereDate('created_at', '>=', $this->filter_date_from);
            }
            if (!empty($this->filter_date_to)) {
                $query->whereDate('created_at', '<=', $this->filter_date_to);
            }

            // Phone Filter (for customer listings) - support multiple comma-separated phones
            if (!empty($this->filter_phone)) {
                $phones = array_filter(explode(',', $this->filter_phone));
                if (!empty($phones)) {
                    $query->where(function ($q) use ($phones) {
                        foreach ($phones as $p) {
                            $normalizedPhone = preg_replace('/[^0-9]/', '', $p);
                            if (!empty($normalizedPhone)) {
                                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(contact_phone, '.', ''), '-', ''), ' ', '') LIKE ?", ['%' . $normalizedPhone . '%']);
                            }
                        }
                    });
                }
            }

            // Mobile-aware pagination limit
            $isMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
            $perPage = $isMobile ? 20 : 15;

            return $query->paginate($perPage)
                ->onEachSide(0);
        });

        return view('livewire.real-estate-listing', [
            'listings' => $listings,
            'salesUsers' => User::orderBy('name')->get(['id', 'name', 'phone']),
            'allCustomers' => \App\Models\Customer::orderBy('name')->get(['id', 'name', 'phone']),
        ])->layout('components.layouts.blog');
    }

    public function downloadSingleImage($url)
    {
        if (!$url)
            return;

        try {
            $response = \Illuminate\Support\Facades\Http::get($url);
            if ($response->successful()) {
                $filename = basename(parse_url($url, PHP_URL_PATH));
                if (!$filename || $filename == '')
                    $filename = 'image.jpg';
                return response()->streamDownload(function () use ($response) {
                    echo $response->body();
                }, $filename, ['Content-Type' => $response->header('Content-Type') ?? 'image/jpeg']);
            }
        } catch (\Exception $e) {
        }

        $this->dispatch('toast', ['message' => 'Lỗi tải ảnh.', 'type' => 'error']);
    }

    public function exportExcel()
    {
        $filters = [
            'search' => $this->search,
            'price_min' => $this->filter_price_min,
            'price_max' => $this->filter_price_max,
            'province' => $this->filter_province,
            'district' => $this->filter_district,
            'ward' => $this->filter_ward,
            'property_type' => $this->filter_property_type,
            'type' => $this->filter_type,
            'is_sold' => $this->filter_is_sold,
            'month' => $this->filter_month,
            'year' => $this->filter_year,
            'phone' => $this->filter_phone,
            'date_from' => $this->filter_date_from,
            'date_to' => $this->filter_date_to,
            'area_min' => $this->filter_area_min,
            'area_max' => $this->filter_area_max,
        ];

        return Excel::download(new ListingsExport($filters), 'tong-hop-tin-dang-' . date('Y-m-d') . '.xlsx');
    }

    protected function normalizeCurrency($value)
    {
        if ($value === null || $value === '')
            return null;
        // Remove spaces and dots (thousand separators)
        $clean = str_replace(['.', ' '], '', $value);
        // Replace comma with dot for decimal
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }

    protected function normalizeDecimal($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        // Handle string input (e.g., from UI text fields)
        if (is_string($value)) {
            // Remove any spaces
            $cleanVal = str_replace(' ', '', $value);
            
            // If it contains both comma and dot, assume dot is thousand separator (VN style)
            if (str_contains($cleanVal, ',') && str_contains($cleanVal, '.')) {
                $cleanVal = str_replace('.', '', $cleanVal);
                $cleanVal = str_replace(',', '.', $cleanVal);
            } 
            // If it contains only comma, assume it's the decimal separator
            else if (str_contains($cleanVal, ',')) {
                $cleanVal = str_replace(',', '.', $cleanVal);
            }
            // If it contains only dot, it might be a decimal separator or a thousand separator.
            // For area fields (usually < 10,000 m2), a single dot is almost always a decimal.
            // However, our previous logic was stripping it. Let's keep it as decimal.
            
            $cleanVal = preg_replace('/[^0-9.]/', '', $cleanVal);
            return is_numeric($cleanVal) ? (float) $cleanVal : null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    public function openPinModal($listingId)
    {
        if ($this->isAdmin) {
            $this->revealedPhones[] = $listingId;
            return;
        }
        
        $this->pinListingId = $listingId;
        $this->viewPinInput = '';
        $this->showPinModal = true;
    }

    public function closePinModal()
    {
        $this->showPinModal = false;
        $this->pinListingId = null;
        $this->viewPinInput = '';
        $this->resetValidation();
    }

    public function verifyPin()
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        if (blank($user->view_phone_pin)) {
            $this->addError('viewPinInput', 'Tài khoản chưa được cấp mã PIN. Vui lòng liên hệ Admin.');
            return;
        }

        if ($this->viewPinInput === $user->view_phone_pin) {
            $this->revealedPhones[] = $this->pinListingId;
            $this->showPinModal = false;
            $this->pinListingId = null;
            $this->viewPinInput = '';
            $this->dispatch('toast', ['message' => 'Xác thực thành công!', 'type' => 'success']);
        } else {
            $this->addError('viewPinInput', 'Mã PIN không chính xác!');
        }
    }
}
