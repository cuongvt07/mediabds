<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\RealEstateListing as ListingModel;

class RealEstateListing extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showCreatePopup = false;
    public $showMediaPopup = false;
    public $showDetailPopup = false;
    public $selectedListing = null;

    // Filters
    public $filter_price_min;
    public $filter_price_max;
    public $filter_province;
    public $filter_district;
    public $filter_ward;
    public $filter_property_type;
    public $filter_type; // New filter for Sale/Rent
    public $filter_is_sold; // Filter for sold status
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
    public $facebook_link;
    public $google_map_link;
    public $description;
    public $images = []; // Array of URLs
    public $tempImages = []; // For new uploads

    public $selectedListingId = null;

    // Dynamic Options
    public $districts = [];
    public $wards = [];

    protected $listeners = ['media-selected' => 'handleMediaSelected'];

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
        $this->filter_province = null;
        $this->loadFilterDistricts();
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
        try {
            $response = Http::get('https://phongphatland.com/wp-admin/admin-ajax.php', [
                'province' => $provinceId,
                'action' => 'willgroup_get_districts'
            ]);
            
            if ($response->successful()) {
                $this->districts = $this->parseOptions($response->body());
            }
        } catch (\Exception $e) {
            // Handle error silently or log
        }
    }

    protected function fetchWards($districtId)
    {
        try {
            $response = Http::get('https://phongphatland.com/wp-admin/admin-ajax.php', [
                'district' => $districtId,
                'action' => 'willgroup_get_wards'
            ]);

            if ($response->successful()) {
                $this->wards = $this->parseOptions($response->body());
            }
        } catch (\Exception $e) {
            // 
        }
    }
    
    protected function fetchFilterDistricts($provinceId)
    {
        try {
            $response = Http::get('https://phongphatland.com/wp-admin/admin-ajax.php', [
                'province' => $provinceId,
                'action' => 'willgroup_get_districts'
            ]);
            
            if ($response->successful()) {
                $this->filter_districts = $this->parseOptions($response->body());
            }
        } catch (\Exception $e) {
            // Handle error silently or log
        }
    }

    protected function fetchFilterWards($districtId)
    {
        try {
            $response = Http::get('https://phongphatland.com/wp-admin/admin-ajax.php', [
                'district' => $districtId,
                'action' => 'willgroup_get_wards'
            ]);

            if ($response->successful()) {
                $this->filter_wards = $this->parseOptions($response->body());
            }
        } catch (\Exception $e) {
            // 
        }
    }
    
    protected function parseOptions($html)
    {
        $options = [];
        // Regex to extract value and text from <option value="val">Text</option>
        preg_match_all('/<option\s+value="([^"]+)">([^<]+)<\/option>/i', $html, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            if ($match[1]) {
                $options[$match[1]] = trim($match[2]);
            }
        }
        return $options;
    }

    public function saveListing()
    {
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
            if ($this->$field === '' || $this->$field === null) {
                $this->$field = null;
            } else {
                $cleanVal = preg_replace('/[^0-9,.]/', '', $this->$field);
                $cleanVal = str_replace('.', '', $cleanVal);
                $this->$field = str_replace(',', '.', $cleanVal);
            }
        }

        $intFields = ['floors', 'bedrooms', 'toilets'];
        foreach ($intFields as $field) {
             if ($this->$field === '') {
                $this->$field = null;
            }
        }

        // Sanitize Social Links
        if ($this->facebook_link === '') $this->facebook_link = null;
        if ($this->google_map_link === '') $this->google_map_link = null;

        $rules = [
            'title' => 'required',
            'facebook_link' => 'nullable|url|max:2000',
            'google_map_link' => 'nullable|url|max:2000',
        ];

        if ($this->type !== 'Cần mua') {
            $rules['province_id'] = 'required';
            $rules['price'] = 'required|numeric';
        }

        $this->validate($rules);

        // Auto-generate code if creating new listing and code is empty
        if (!$this->selectedListingId && empty($this->code)) {
            $this->code = 'RE-' . time() . '-' . strtoupper(substr(uniqid(), -6));
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
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'floors' => $this->floors,
            'bedrooms' => $this->bedrooms,
            'toilets' => $this->toilets,
            'direction' => $this->direction,
            'front_width' => $this->front_width,
            'road_width' => $this->road_width,
            'youtube_link' => $this->youtube_link,
            'facebook_link' => $this->facebook_link,
            'google_map_link' => $this->google_map_link,
            'description' => $this->description,
            'images' => $this->images,
            'user_id' => auth()->id(),
        ];

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
        // Clear cache to ensure we edit fresh data? No, finding by ID is direct query usually not cached in this component context (we cached the list query).
        // But if we return to list, list might be stale.
        // It's fine.
        $listing = ListingModel::find($id);
        if (!$listing) return;

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
        if ($this->province_id) $this->fetchDistricts($this->province_id);
        
        $this->district_id = $listing->district_id;
        
        if ($this->district_id) $this->fetchWards($this->district_id);
        
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
        $this->facebook_link = $listing->facebook_link;
        $this->google_map_link = $listing->google_map_link;
        $this->description = $listing->description;
        $this->images = $listing->images ?? [];

        $this->showCreatePopup = true;
    }

    public function viewListingDetail($id)
    {
        $listing = ListingModel::find($id);
        if (!$listing) return;

        $this->selectedListing = $listing->toArray();
        $this->showDetailPopup = true;
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
        $this->reset(['title', 'type', 'contact_type', 'contact_phone', 'house_password', 'code', 'is_sold', 'address', 'area', 'price', 'description', 'floors', 'bedrooms', 'toilets', 'direction', 'front_width', 'road_width', 'youtube_link', 'facebook_link', 'google_map_link', 'images', 'province_id', 'district_id', 'ward_id', 'tempImages']);
        $this->is_sold = false;
        $this->districts = [];
        $this->wards = [];
    }

    public function deleteListing($id)
    {
        $listing = ListingModel::find($id);
        if ($listing) {
            $listing->delete();
            $this->refreshCacheVersion(); // Refresh cache on delete
            $this->dispatch('toast', ['message' => 'Đã xóa tin đăng!', 'type' => 'success']);
        }
    }

    public function toggleSold($id)
    {
        $listing = ListingModel::find($id);
        if ($listing) {
            $listing->is_sold = !$listing->is_sold;
            $listing->save();
            $this->refreshCacheVersion(); // Refresh cache
            
            $status = $listing->is_sold ? 'đã bán' : 'chưa bán';
            $this->dispatch('toast', ['message' => "Đã đánh dấu tin {$status}!", 'type' => 'success']);
            
            // Refresh the detail popup if it's open
            if ($this->selectedListing && $this->selectedListing['id'] == $id) {
                $this->selectedListing = $listing->toArray();
            }
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
            'page' => $this->getPage(),
            'version' => $this->getCacheVersion(), // Include version in key
        ];

        // Serialize filters to create a unique key
        $cacheKey = 'listings_' . md5(json_encode($filters));

        // Cache for 60 seconds (so even if version doesn't change, we still refresh occasionally)
        $listings = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () {
            // Use deterministic sorting: Created At DESC, then ID DESC
            $query = ListingModel::orderBy('created_at', 'desc')->orderBy('id', 'desc');

            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            }

            // Price Filters
            if (!empty($this->filter_price_min)) {
                $query->where('price', '>=', str_replace('.', '', $this->filter_price_min));
            }
            if (!empty($this->filter_price_max)) {
                $query->where('price', '<=', str_replace('.', '', $this->filter_price_max));
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

            return $query->paginate(12);
        });

        return view('livewire.real-estate-listing', [
            'listings' => $listings
        ])->layout('components.layouts.blog');
    }
}
