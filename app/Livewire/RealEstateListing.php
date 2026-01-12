<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use App\Models\RealEstateListing as ListingModel;

class RealEstateListing extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showCreatePopup = false;
    public $showMediaPopup = false;

    // Form Fields
    public $title;
    public $type = 'Cần bán';
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
        108 => 'Nhà riêng',
        109 => 'Trang trại',
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
        // 
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
        // Sanitize Price (remove dots)
        $this->price = str_replace('.', '', $this->price);

        $this->validate([
            'title' => 'required',
            'province_id' => 'required',
            'price' => 'required|numeric',
        ]);

        // Process images
        if (count($this->tempImages) > 0) {
            foreach ($this->tempImages as $temp) {
                $path = $temp->store('listings/' . date('Y/m'), 'public');
                $this->images[] = Storage::url($path);
            }
            $this->tempImages = [];
        }

        $data = [
            'title' => $this->title,
            'type' => $this->type,
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
            'description' => $this->description,
            'images' => $this->images,
            'user_id' => auth()->id(),
        ];

        if ($this->selectedListingId) {
            ListingModel::where('id', $this->selectedListingId)->update(array_filter($data, fn($v) => !is_null($v)));
            $message = 'Đã cập nhật tin đăng thành công!';
        } else {
            ListingModel::create($data);
            $message = 'Đã đăng tin thành công!';
        }

        $this->dispatch('toast', ['message' => $message, 'type' => 'success']);
        $this->closeCreatePopup();
        $this->resetPage(); 
    }

    public function editListing($id)
    {
        $listing = ListingModel::find($id);
        if (!$listing) return;

        $this->selectedListingId = $id;

        $this->title = $listing->title;
        $this->type = $listing->type;
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
        $this->description = $listing->description;
        $this->images = $listing->images ?? [];

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
        $this->reset(['title', 'type', 'address', 'area', 'price', 'description', 'floors', 'bedrooms', 'toilets', 'direction', 'front_width', 'road_width', 'youtube_link', 'images', 'province_id', 'district_id', 'ward_id', 'tempImages']);
        $this->districts = [];
        $this->wards = [];
    }

    public function deleteListing($id)
    {
        $listing = ListingModel::find($id);
        if ($listing) {
            $listing->delete();
            $this->dispatch('toast', ['message' => 'Đã xóa tin đăng!', 'type' => 'success']);
        }
    }



    public function render()
    {
        $query = ListingModel::latest();

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%')
                  ->orWhere('province_name', 'like', '%' . $this->search . '%')
                  ->orWhere('district_name', 'like', '%' . $this->search . '%')
                  ->orWhere('ward_name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $listings = $query->paginate(12);

        return view('livewire.real-estate-listing', [
            'listings' => $listings
        ]);
    }
}
