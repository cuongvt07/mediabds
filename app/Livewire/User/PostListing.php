<?php

namespace App\Livewire\User;

use App\Livewire\RealEstateListing as RealEstateLivewire;
use App\Models\RealEstateListing as ListingModel;
use App\Models\SiteAmenity;
use App\Services\ListingImageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostListing extends Component
{
    use WithFileUploads;

    public const ROOM_TYPES = [
        'duplex' => 'Duplex',
        'studio' => 'Studio',
        'loft' => 'Gác',
        'balcony' => 'Ban công',
    ];

    public const FURNISH_TYPES = [
        'full' => 'Đầy đủ nội thất',
        'basic' => 'Nội thất cơ bản',
        'empty' => 'Phòng trống',
    ];

    public const CONDITION_OPTIONS = ['' => 'Liên hệ', 'Có' => 'Có', 'Không' => 'Không'];

    public const ACCESS_HOUR_OPTIONS = [
        '' => 'Liên hệ',
        'Tự do' => 'Tự do',
        'Giới hạn' => 'Giới hạn',
    ];

    public const PROPERTY_TYPES = [
        115 => 'Phòng trọ',
        108 => 'Nhà nguyên căn',
        103 => 'Chung cư',
    ];

    public const PROVINCES = RealEstateLivewire::PROVINCES;

    public $editingId = null;
    public $title = '';
    public $contactPhone = '';
    public $provinceId = '79';
    public $districtId = '';
    public $wardId = '';
    public $price = '';
    public $propertyType = 115;
    public $roomType = 'studio';
    public $furnish = 'basic';
    public $area = '';
    public $floors = '';
    public $depositMonths = '';
    public $apartmentName = '';
    public $apartmentBlock = '';
    public $bedrooms = '';
    public $toilets = '';
    public $electricity = '';
    public $water = '';
    public $parkingFee = '';
    public $accessHours = '';
    public $window = '';
    public $pets = '';
    public $parking = '';
    public $amenities = [];
    public $description = '';
    public $youtubeLink = '';
    public $facebookLink = '';
    public $tiktokLink = '';
    public $googleMapLink = '';
    public $avatar = '';
    public $avatarFile;
    public $images = [];
    public $imageFiles = [];

    public $districts = [];
    public $wards = [];

    protected $locationData = null;

    public function mount($listing = null): void
    {
        $this->loadDistricts($this->provinceId);

        if ($listing !== null) {
            $model = ListingModel::where('user_id', auth()->id())->findOrFail($listing);
            $this->editingId = $model->id;
            $this->title = $model->title ?: '';
            $this->contactPhone = $model->contact_phone ?: auth()->user()->phone;
            $this->provinceId = (string) ($model->province_id ?: '79');
            $this->loadDistricts($this->provinceId);
            $this->districtId = (string) ($model->district_id ?: '');
            $this->loadWards($this->districtId);
            $this->wardId = (string) ($model->ward_id ?: '');
            $this->price = $model->price ? rtrim(rtrim((string) $model->price, '0'), '.') : '';
            $this->propertyType = (int) ($model->property_type ?: 115);
            $this->roomType = $model->room_type ?: 'studio';
            $this->furnish = $model->furnish ?: 'basic';
            $this->area = $model->area ? rtrim(rtrim((string) $model->area, '0'), '.') : '';
            $this->floors = $model->floors ?: '';
            $this->depositMonths = $model->deposit_months ?: '';
            $this->apartmentName = $model->apartment_name ?: '';
            $this->apartmentBlock = $model->apartment_block ?: '';
            $this->bedrooms = $model->bedrooms ?: '';
            $this->toilets = $model->toilets ?: '';
            $this->electricity = $model->electricity_price ?: '';
            $this->water = $model->water_price ?: '';
            $this->parkingFee = $model->parking_fee ?: '';
            $this->accessHours = $model->access_hours ?: '';
            $this->window = $model->has_window ?: '';
            $this->pets = $model->pets_allowed ?: '';
            $this->parking = $model->parking_available ?: '';
            $this->amenities = array_values($model->amenities ?: []);
            $this->description = $model->description ?: '';
            $this->youtubeLink = $model->youtube_link ?: '';
            $this->facebookLink = $model->facebook_link ?: '';
            $this->tiktokLink = $model->tiktok_link ?: '';
            $this->googleMapLink = $model->google_map_link ?: '';
            $this->avatar = $model->avatar ?: '';
            $this->images = array_values(array_filter(
                $model->images ?: [],
                fn ($img) => $img !== $model->avatar
            ));
        } else {
            $this->contactPhone = auth()->user()->phone ?: '';
        }
    }

    public function updatedProvinceId($value): void
    {
        $this->districtId = '';
        $this->wardId = '';
        $this->wards = [];
        $this->loadDistricts((string) $value);
    }

    public function updatedDistrictId($value): void
    {
        $this->wardId = '';
        $this->loadWards((string) $value);
    }

    public function removeImage(int $index): void
    {
        if (isset($this->images[$index])) {
            array_splice($this->images, $index, 1);
        }
    }

    public function removeAvatar(): void
    {
        $this->avatar = '';
    }

    public function save()
    {
        if (! $this->editingId && ! $this->canPostToday()) {
            $this->addError('title', 'Tài khoản của bạn đã hết lượt đăng tin hôm nay. Vui lòng mua gói cao hơn hoặc quay lại ngày mai.');
            return;
        }

        $data = $this->validate([
            'title' => 'required|string|max:180',
            'contactPhone' => 'required|string|max:60',
            'provinceId' => 'required|string',
            'districtId' => 'required|string',
            'wardId' => 'required|string',
            'price' => 'required|regex:/^[0-9]+([,.][0-9]+)?$/',
            'propertyType' => 'required|in:115,108,103',
            'roomType' => 'required|in:duplex,studio,loft,balcony',
            'furnish' => 'nullable|in:full,basic,empty',
            'area' => 'nullable|numeric|min:0|max:100000',
            'floors' => 'nullable|integer|min:0|max:100',
            'depositMonths' => 'nullable|integer|min:0|max:36',
            'apartmentName' => 'nullable|string|max:160',
            'apartmentBlock' => 'nullable|string|max:80',
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'toilets' => 'nullable|integer|min:0|max:20',
            'electricity' => 'nullable|numeric|min:0|max:10000000',
            'water' => 'nullable|numeric|min:0|max:10000000',
            'parkingFee' => 'nullable|numeric|min:0|max:10000000',
            'accessHours' => 'nullable|in:,Tự do,Giới hạn',
            'window' => 'nullable|in:,Có,Không',
            'pets' => 'nullable|in:,Có,Không',
            'parking' => 'nullable|in:,Có,Không',
            'amenities' => 'array',
            'amenities.*' => 'in:' . implode(',', SiteAmenity::query()->pluck('key')->all() ?: ['_none']),
            'description' => 'nullable|string',
            'youtubeLink' => 'nullable|url|max:2048',
            'facebookLink' => 'nullable|url|max:2048',
            'tiktokLink' => 'nullable|url|max:2048',
            'googleMapLink' => 'nullable|url|max:2048',
            'avatarFile' => 'nullable|image|max:2048',
            'imageFiles.*' => 'nullable|image|max:2048',
        ]);

        $avatar = $this->avatar ?: null;
        if ($this->avatarFile) {
            $avatar = app(ListingImageService::class)->storeWithWatermark($this->avatarFile);
        }

        foreach ($this->imageFiles as $file) {
            $this->images[] = app(ListingImageService::class)->storeWithWatermark($file);
        }
        $this->images = array_values(array_filter($this->images));

        if (! $avatar) {
            $avatar = $this->images[0] ?? null;
        }

        $code = $this->editingId
            ? ListingModel::withTrashed()->whereKey($this->editingId)->value('code')
            : $this->generateCode();

        $listingData = [
            'title' => $data['title'],
            'type' => 'Cho thuê',
            'code' => $code ?: $this->generateCode(),
            'contact_phone' => $data['contactPhone'],
            'is_sold' => false,
            'status' => 'active',
            'moderation_status' => 'pending',
            'rejection_reason' => null,
            'property_type' => (int) $data['propertyType'],
            'apartment_name' => $data['apartmentName'] ?: null,
            'apartment_block' => $data['apartmentBlock'] ?: null,
            'room_type' => $data['roomType'],
            'furnish' => $data['furnish'] ?: null,
            'amenities' => array_values($data['amenities'] ?? []),
            'province_id' => $data['provinceId'],
            'province_name' => self::PROVINCES[$data['provinceId']] ?? null,
            'district_id' => $data['districtId'],
            'district_name' => $this->districts[$data['districtId']] ?? null,
            'ward_id' => $data['wardId'],
            'ward_name' => $this->wards[$data['wardId']] ?? null,
            'price' => $this->normalizeCurrency($data['price']),
            'price_unit' => '2',
            'area' => $data['area'] ?: null,
            'floors' => $data['floors'] ?: null,
            'deposit_months' => $data['depositMonths'] ?: null,
            'bedrooms' => $data['bedrooms'] ?: null,
            'toilets' => $data['toilets'] ?: null,
            'electricity_price' => $data['electricity'] ?: null,
            'water_price' => $data['water'] ?: null,
            'parking_fee' => $data['parkingFee'] ?: null,
            'access_hours' => $data['accessHours'] ?: null,
            'has_window' => $data['window'] ?: null,
            'pets_allowed' => $data['pets'] ?: null,
            'parking_available' => $data['parking'] ?: null,
            'youtube_link' => $data['youtubeLink'] ?: null,
            'facebook_link' => $data['facebookLink'] ?: null,
            'tiktok_link' => $data['tiktokLink'] ?: null,
            'google_map_link' => $data['googleMapLink'] ?: null,
            'description' => $data['description'] ?: null,
            'images' => $this->images,
            'avatar' => $avatar,
            'user_id' => auth()->id(),
        ];

        if (! $this->editingId) {
            $listingData['published_at'] = now();
        }

        ListingModel::updateOrCreate(['id' => $this->editingId], $listingData);

        session()->flash('message', $this->editingId
            ? 'Đã cập nhật tin — tin sẽ chờ duyệt lại trước khi hiển thị.'
            : 'Đã gửi tin — tin đang chờ admin duyệt trước khi hiển thị.');

        return redirect()->route('user.dashboard');
    }

    public function render()
    {
        return view('livewire.user.post-listing', [
            'roomTypes' => self::ROOM_TYPES,
            'furnishTypes' => self::FURNISH_TYPES,
            'conditionOptions' => self::CONDITION_OPTIONS,
            'accessHourOptions' => self::ACCESS_HOUR_OPTIONS,
            'propertyTypes' => self::PROPERTY_TYPES,
            'provinces' => self::PROVINCES,
            'amenityItems' => SiteAmenity::query()->active()->ordered()->get(),
        ])->layout('site.layout');
    }

    private function loadDistricts(string $provinceId): void
    {
        $this->districts = [];
        if (! $provinceId) {
            return;
        }
        foreach (($this->getLocationData()[$provinceId]['districts'] ?? []) as $id => $d) {
            $this->districts[$id] = $d['name'];
        }
    }

    private function loadWards(string $districtId): void
    {
        $this->wards = [];
        if (! $districtId || ! $this->provinceId) {
            return;
        }
        $this->wards = $this->getLocationData()[$this->provinceId]['districts'][$districtId]['wards'] ?? [];
    }

    private function getLocationData(): array
    {
        if ($this->locationData !== null) {
            return $this->locationData;
        }
        $cached = Cache::get('vietnam_locations_full');
        if (is_array($cached) && $cached !== []) {
            return $this->locationData = $cached;
        }
        $path = 'locations/all_vietnam.json';
        $data = Storage::disk('local')->exists($path)
            ? (json_decode(Storage::disk('local')->get($path), true) ?: [])
            : [];
        if ($data !== []) {
            Cache::put('vietnam_locations_full', $data, 86400);
        }
        return $this->locationData = $data;
    }

    private function generateCode(): string
    {
        for ($i = 0; $i < 8; $i++) {
            $code = '#NT' . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            if (! ListingModel::withTrashed()->where('code', $code)->exists()) {
                return $code;
            }
        }
        return '#NT' . substr((string) (int) (microtime(true) * 1000), -8);
    }

    private function normalizeCurrency($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = str_replace(['.', ' '], '', (string) $value);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }

    private function canPostToday(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $postedToday = ListingModel::withTrashed()
            ->where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return $postedToday < $user->postingLimitPerDay();
    }
}
