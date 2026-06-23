<?php

namespace App\Livewire;

use App\Models\RealEstateListing as ListingModel;
use App\Models\SiteBanner;
use App\Models\SiteAmenity;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ListingImageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SiteAdmin extends Component
{
    use WithPagination, WithFileUploads;

    public const ROOM_TYPES = [
        'duplex' => 'Duplex',
        'studio' => 'Studio',
        'loft' => 'Gác',
        'balcony' => 'Ban công',
    ];

    public const CONDITION_OPTIONS = [
        '' => 'Liên hệ',
        'Có' => 'Có',
        'Không' => 'Không',
    ];

    public const FURNISH_TYPES = [
        'full' => 'Đầy đủ nội thất',
        'basic' => 'Nội thất cơ bản',
        'empty' => 'Phòng trống',
    ];

    public const AMENITIES = [
        'bed' => 'Giường',
        'mattress' => 'Nệm',
        'wardrobe' => 'Tủ quần áo',
        'wifi' => 'Wifi',
        'air_conditioner' => 'Máy lạnh',
        'kitchen' => 'Kệ bếp',
        'water_heater' => 'Nước nóng',
        'fridge' => 'Tủ lạnh',
    ];

    public const ACCESS_HOUR_OPTIONS = [
        '' => 'Liên hệ',
        'Tự do' => 'Tự do',
        'Giới hạn' => 'Giới hạn',
    ];

    public const LISTING_PROPERTY_TYPES = [
        115 => 'Phòng trọ',
        108 => 'Nhà nguyên căn',
        103 => 'Chung cư',
    ];

    public const BOOST_PACKAGES = [
        'normal' => ['label' => 'Không đẩy tin', 'days' => 0],
        'boost_1' => ['label' => 'Mức 1 - 10k / 1 ngày', 'days' => 1],
        'boost_2' => ['label' => 'Mức 2 - 20k / 3 ngày', 'days' => 3],
        'boost_3' => ['label' => 'Mức 3 - 50k / 1 tuần', 'days' => 7],
    ];

    public const POSTING_PLANS = [
        'free' => 'Free - 10 tin/ngày',
        'daily_20' => 'Gói 20 tin/ngày - 399k/tháng',
        'daily_40' => 'Gói 40 tin/ngày - 599k/tháng',
    ];

    public $activeTab = 'dashboard';

    public $showBannerModal = false;
    public $bannerEditingId = null;
    public $bannerTitle = '';
    public $bannerSubtitle = '';
    public $bannerImageUrl = '';
    public $bannerLinkUrl = '';
    public $bannerIsActive = true;
    public $bannerSortOrder = 0;
    public $bannerImageFile;

    public $showListingModal = false;
    public $listingEditingId = null;
    public $listingSearch = '';
    public $listingTitle = '';
    public $listingCode = '';
    public $listingContactPhone = '';
    public $listingHousePassword = '';
    public $listingProvinceId = '79';
    public $listingDistrictId = '';
    public $listingWardId = '';
    public $listingPrice = '';
    public $listingPropertyType = 115;
    public $listingRoomType = 'studio';
    public $listingFurnish = 'basic';
    public $listingAmenities = [];
    public $listingArea = '';
    public $listingFloors = '';
    public $listingDepositMonths = '';
    public $listingApartmentName = '';
    public $listingApartmentBlock = '';
    public $listingBoostTier = 'normal';
    public $listingPublishedAt = '';
    public $listingBedrooms = '';
    public $listingToilets = '';
    public $listingElectricity = '';
    public $listingWater = '';
    public $listingParkingFee = '';
    public $listingAccessHours = '';
    public $listingWindow = '';
    public $listingPets = '';
    public $listingParking = '';
    public $listingDescription = '';
    public $listingYoutubeLink = '';
    public $listingYoutubeShort = '';
    public $listingFacebookLink = '';
    public $listingFacebookVideoLink = '';
    public $listingTiktokLink = '';
    public $listingGoogleMapLink = '';
    public $listingAvatar = '';
    public $listingAvatarFile;
    public $listingImages = [];
    public $listingImageFiles = [];
    public $listingIsSold = false;
    public $districts = [];
    public $wards = [];

    // Component (not model) — resolves to App\Livewire\RealEstateListing in this namespace.
    public const PROVINCES = RealEstateListing::PROVINCES;

    public const ROLE_OPTIONS = [
        'admin' => 'Quản trị',
        'ctv' => 'Cộng tác viên',
        'buyer' => 'Người dùng',
    ];

    public const MODERATION = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];

    public $listingModeration = 'all'; // all | pending | approved | rejected
    public $showRejectModal = false;
    public $rejectingId = null;
    public $rejectReason = '';

    public $showAccountModal = false;
    public $accountEditingId = null;
    public $accountSearch = '';
    public $accountName = '';
    public $accountPhone = '';
    public $accountRole = 'ctv';
    public $accountPassword = '';
    public $accountPostingPlan = 'free';
    public $accountPostingPlanExpiresAt = '';

    public $showAmenityModal = false;
    public $amenityEditingId = null;
    public $amenityType = 'amenity';      // segment đang xem: 'amenity' | 'furniture'
    public $amenityFormType = 'amenity';  // loại của mục đang thêm/sửa
    public $amenityName = '';
    public $amenityKey = '';
    public $amenitySortOrder = 0;
    public $amenityIsActive = true;
    public $amenityIconUrl = '';
    public $amenityIconFile;

    public $siteName = 'nhatrosv.com';
    public $logoUrl = '';
    public $logoFile;
    public $contactPhone = '';
    public $contactZalo = '';
    public $contactEmail = '';
    public $contactFacebook = '';
    public $contactPosition = 'right';
    public $watermarkImageUrl = '';
    public $watermarkImageFile;
    public $watermarkMode = 'image'; // 'image' = ảnh logo | 'text' = tên site

    protected $queryString = [
        'activeTab' => ['except' => 'dashboard', 'as' => 'tab'],
        'listingSearch' => ['except' => '', 'as' => 'q'],
    ];

    protected $locationData = null;

    public function mount(): void
    {
        $this->siteName = SiteSetting::query()->whereKey('site_name')->value('value') ?: 'nhatrosv.com';
        $this->logoUrl = SiteSetting::query()->whereKey('logo_url')->value('value') ?: '';
        $this->contactPhone = SiteSetting::query()->whereKey('contact_phone')->value('value') ?: '';
        $this->contactZalo = SiteSetting::query()->whereKey('contact_zalo')->value('value') ?: '';
        $this->contactEmail = SiteSetting::query()->whereKey('contact_email')->value('value') ?: '';
        $this->contactFacebook = SiteSetting::query()->whereKey('contact_facebook')->value('value') ?: '';
        $this->contactPosition = SiteSetting::query()->whereKey('contact_position')->value('value') ?: 'right';
        $this->watermarkImageUrl = SiteSetting::query()->whereKey('watermark_image_url')->value('value') ?: '';
        $this->watermarkMode = SiteSetting::query()->whereKey('watermark_mode')->value('value') === 'text' ? 'text' : 'image';
        $this->loadDistricts($this->listingProvinceId);
    }

    public function render()
    {
        return view('livewire.site-admin', [
            'banners' => SiteBanner::query()
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'bannerPage'),
            'siteListings' => $this->listingQuery()
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'listingPage'),
            'bannerCount' => SiteBanner::query()->count(),
            'activeBannerCount' => SiteBanner::query()->where('is_active', true)->count(),
            'listingCount' => $this->publicRoomQuery()->count(),
            'availableListingCount' => $this->publicRoomQuery()->where('is_sold', false)->count(),
            'siteAccounts' => $this->accountQuery()
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'accountPage'),
            'accountCount' => User::query()->count(),
            'siteAmenities' => SiteAmenity::query()
                ->type($this->amenityType)
                ->ordered()
                ->paginate(20, ['*'], 'amenityPage'),
            'amenityCounts' => [
                'amenity' => SiteAmenity::query()->type('amenity')->count(),
                'furniture' => SiteAmenity::query()->type('furniture')->count(),
            ],
            'amenityTypes' => SiteAmenity::TYPES,
            'moderationCounts' => [
                'all' => $this->publicRoomQuery()->count(),
                'pending' => $this->publicRoomQuery()->where('moderation_status', 'pending')->count(),
                'active' => $this->publicRoomQuery()->where('is_sold', false)->where(function ($q) { $q->whereNull('status')->orWhere('status', 'active'); })->where('moderation_status', 'approved')->count(),
                'rejected' => $this->publicRoomQuery()->where('moderation_status', 'rejected')->count(),
                'hidden' => $this->publicRoomQuery()->where(function ($q) { $q->where('is_sold', true)->orWhere('status', 'inactive'); })->count(),
                'boosting' => $this->publicRoomQuery()->where('boost_tier', '<>', 'normal')->where('boost_expires_at', '>', now())->count(),
            ],
            'moderationOptions' => self::MODERATION,
            'roomTypes' => self::ROOM_TYPES,
            'furnishTypes' => self::FURNISH_TYPES,
            'conditionOptions' => self::CONDITION_OPTIONS,
            'accessHourOptions' => self::ACCESS_HOUR_OPTIONS,
            'listingPropertyTypes' => self::LISTING_PROPERTY_TYPES,
            'boostPackages' => self::BOOST_PACKAGES,
            'postingPlans' => self::POSTING_PLANS,
            'amenityOptions' => SiteAmenity::query()->active()->ordered()->pluck('name', 'key')->all(),
            'provinces' => self::PROVINCES,
            'roleOptions' => self::ROLE_OPTIONS,
        ])->layout('components.layouts.site-admin', [
            'title' => 'Quản trị nhà trọ',
        ]);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['dashboard', 'listings', 'banners', 'accounts', 'amenities', 'identity'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage('listingPage');
        $this->resetPage('bannerPage');
        $this->resetPage('accountPage');
        $this->resetPage('amenityPage');
    }

    public function updatedAccountSearch(): void
    {
        $this->resetPage('accountPage');
    }

    public function updatedListingSearch(): void
    {
        $this->resetPage('listingPage');
    }

    public function setListingModeration(string $m): void
    {
        if (in_array($m, ['all', 'pending', 'active', 'rejected', 'hidden', 'boosting'], true)) {
            $this->listingModeration = $m;
            $this->activeTab = 'listings';
            $this->resetPage('listingPage');
        }
    }

    public function setListingMod(int $id, string $status): void
    {
        if ($status === 'rejected') {
            $this->promptReject($id);
            return;
        }
        if (! in_array($status, ['pending', 'approved'], true)) {
            return;
        }
        ListingModel::whereKey($id)->update(['moderation_status' => $status, 'rejection_reason' => null]);
        session()->flash('message', $status === 'approved' ? 'Đã duyệt tin đăng.' : 'Đã chuyển tin về chờ duyệt.');
    }

    public function approveListing(int $id): void
    {
        ListingModel::whereKey($id)->update(['moderation_status' => 'approved', 'rejection_reason' => null]);
        $this->activeTab = 'listings';
        session()->flash('message', 'Đã duyệt tin đăng.');
    }

    public function promptReject(int $id): void
    {
        $this->rejectingId = $id;
        $this->rejectReason = '';
        $this->showRejectModal = true;
        $this->activeTab = 'listings';
    }

    public function confirmReject(): void
    {
        $this->validate(
            ['rejectReason' => 'required|string|max:500'],
            ['rejectReason.required' => 'Vui lòng nhập lý do từ chối.']
        );

        if ($this->rejectingId) {
            ListingModel::whereKey($this->rejectingId)->update([
                'moderation_status' => 'rejected',
                'rejection_reason' => $this->rejectReason,
            ]);
        }

        $this->closeRejectModal();
        $this->activeTab = 'listings';
        session()->flash('message', 'Đã từ chối tin đăng.');
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectingId = null;
        $this->rejectReason = '';
    }

    public function updatedListingProvinceId($value): void
    {
        $this->listingDistrictId = '';
        $this->listingWardId = '';
        $this->wards = [];
        $this->loadDistricts((string) $value);
    }

    public function updatedListingDistrictId($value): void
    {
        $this->listingWardId = '';
        $this->loadWards((string) $value);
    }

    public function createListing(): void
    {
        $this->resetListingForm();
        $this->activeTab = 'listings';
        $this->showListingModal = true;
    }

    public function editListing(int $id): void
    {
        $listing = ListingModel::findOrFail($id);

        $this->listingEditingId = $listing->id;
        $this->listingTitle = $listing->title ?: '';
        $this->listingCode = $listing->code ?: '';
        $this->listingContactPhone = $listing->contact_phone ?: '';
        $this->listingHousePassword = $listing->house_password ?: '';
        $this->listingProvinceId = (string) ($listing->province_id ?: '79');
        $this->loadDistricts($this->listingProvinceId);
        $this->listingDistrictId = (string) ($listing->district_id ?: '');
        $this->loadWards($this->listingDistrictId);
        $this->listingWardId = (string) ($listing->ward_id ?: '');
        $this->listingPrice = $listing->price ? rtrim(rtrim((string) $listing->price, '0'), '.') : '';
        $this->listingPropertyType = (int) ($listing->property_type ?: 115);
        $this->listingRoomType = $listing->room_type ?: 'studio';
        $this->listingFurnish = $listing->furnish ?: 'basic';
        $this->listingAmenities = array_values($listing->amenities ?: []);
        $this->listingArea = $listing->area ? rtrim(rtrim((string) $listing->area, '0'), '.') : '';
        $this->listingFloors = $listing->floors ?: '';
        $this->listingDepositMonths = $listing->deposit_months ?: '';
        $this->listingApartmentName = $listing->apartment_name ?: '';
        $this->listingApartmentBlock = $listing->apartment_block ?: '';
        $this->listingBoostTier = $listing->boost_tier ?: 'normal';
        $this->listingPublishedAt = optional($listing->created_at)->format('Y-m-d\TH:i') ?: '';
        $this->listingBedrooms = $listing->bedrooms ?: '';
        $this->listingToilets = $listing->toilets ?: '';
        $this->listingElectricity = $listing->electricity_price ?: '';
        $this->listingWater = $listing->water_price ?: '';
        $this->listingParkingFee = $listing->parking_fee ?: '';
        $this->listingAccessHours = $listing->access_hours ?: '';
        $this->listingWindow = $listing->has_window ?: '';
        $this->listingPets = $listing->pets_allowed ?: '';
        $this->listingParking = $listing->parking_available ?: '';
        $this->listingDescription = $listing->description ?: '';
        $this->listingYoutubeLink = $listing->youtube_link ?: '';
        $this->listingYoutubeShort = $listing->youtube_link_short ?: '';
        $this->listingFacebookLink = $listing->facebook_link ?: '';
        $this->listingFacebookVideoLink = $listing->facebook_video_link ?: '';
        $this->listingTiktokLink = $listing->tiktok_link ?: '';
        $this->listingGoogleMapLink = $listing->google_map_link ?: '';
        $this->listingAvatar = $listing->avatar ?: '';
        // Slider = images, không gộp avatar (tách 2 ô riêng).
        $this->listingImages = array_values(array_filter(
            $listing->images ?: [],
            fn ($img) => $img !== $listing->avatar
        ));
        $this->listingAvatarFile = null;
        $this->listingIsSold = (bool) $listing->is_sold;
        $this->listingImageFiles = [];
        $this->activeTab = 'listings';
        $this->showListingModal = true;
    }

    public function saveListing(): void
    {
        $data = $this->validate([
            'listingTitle' => 'required|string|max:180',
            'listingContactPhone' => 'required|string|max:60',
            'listingHousePassword' => 'nullable|string|max:80',
            'listingProvinceId' => 'required|string',
            'listingDistrictId' => 'required|string',
            'listingWardId' => 'required|string',
            'listingPrice' => 'required|regex:/^[0-9]+([,.][0-9]+)?$/',
            'listingPropertyType' => 'required|in:115,108,103',
            'listingRoomType' => 'required|in:duplex,studio,loft,balcony',
            'listingFurnish' => 'nullable|in:full,basic,empty',
            'listingAmenities' => 'array',
            'listingAmenities.*' => 'in:' . implode(',', SiteAmenity::query()->pluck('key')->all() ?: ['_none']),
            'listingArea' => 'nullable|numeric|min:0|max:100000',
            'listingFloors' => 'nullable|integer|min:0|max:100',
            'listingDepositMonths' => 'nullable|integer|min:0|max:36',
            'listingApartmentName' => 'nullable|string|max:160',
            'listingApartmentBlock' => 'nullable|string|max:80',
            'listingBoostTier' => 'required|in:' . implode(',', array_keys(self::BOOST_PACKAGES)),
            'listingPublishedAt' => 'nullable|date',
            'listingBedrooms' => 'nullable|integer|min:0|max:20',
            'listingToilets' => 'nullable|integer|min:0|max:20',
            'listingElectricity' => 'nullable|numeric|min:0|max:10000000',
            'listingWater' => 'nullable|numeric|min:0|max:10000000',
            'listingParkingFee' => 'nullable|numeric|min:0|max:10000000',
            'listingAccessHours' => 'nullable|in:,Tự do,Giới hạn',
            'listingWindow' => 'nullable|in:,Có,Không',
            'listingPets' => 'nullable|in:,Có,Không',
            'listingParking' => 'nullable|in:,Có,Không',
            'listingDescription' => 'nullable|string',
            'listingYoutubeLink' => 'nullable|url|max:2048',
            'listingYoutubeShort' => 'nullable|url|max:2048',
            'listingFacebookLink' => 'nullable|url|max:2048',
            'listingFacebookVideoLink' => 'nullable|url|max:2048',
            'listingTiktokLink' => 'nullable|url|max:2048',
            'listingGoogleMapLink' => 'nullable|url|max:2048',
            'listingAvatarFile' => 'nullable|image|max:2048',
            'listingImageFiles.*' => 'nullable|image|max:2048',
            'listingIsSold' => 'boolean',
        ]);

        // Ảnh chính (avatar)
        $avatar = $this->listingAvatar ?: null;
        if ($this->listingAvatarFile) {
            $avatar = app(ListingImageService::class)->storeWithWatermark($this->listingAvatarFile);
        }

        // Ảnh slider
        foreach ($this->listingImageFiles as $file) {
            $this->listingImages[] = app(ListingImageService::class)->storeWithWatermark($file);
        }
        $this->listingImages = array_values(array_filter($this->listingImages));

        // Chưa có ảnh chính thì lấy ảnh slider đầu tiên làm bìa.
        if (! $avatar) {
            $avatar = $this->listingImages[0] ?? null;
        }

        $code = $this->listingCode ?: $this->generateListingCode();

        // Đảm bảo mã không trùng (kể cả bản ghi đã xóa mềm — vì unique index vẫn tính).
        $codeTaken = ListingModel::withTrashed()
            ->where('code', $code)
            ->when($this->listingEditingId, fn ($q) => $q->where('id', '!=', $this->listingEditingId))
            ->exists();

        if ($codeTaken) {
            if ($this->listingCode) {
                $this->addError('listingCode', 'Mã tin "' . $code . '" đã tồn tại, vui lòng nhập mã khác.');
                return;
            }
            $code = $this->generateListingCode();
        }

        $boost = $this->boostPayload($data['listingBoostTier'], $this->listingEditingId);

        ListingModel::updateOrCreate(
            ['id' => $this->listingEditingId],
            [
                'title' => $data['listingTitle'],
                'type' => 'Cho thuê',
                'code' => $code,
                'contact_phone' => $data['listingContactPhone'],
                'house_password' => $data['listingHousePassword'] ?: null,
                'is_sold' => (bool) $data['listingIsSold'],
                'status' => (bool) $data['listingIsSold'] ? 'inactive' : 'active',
                'moderation_status' => 'approved',
                'rejection_reason' => null,
                'property_type' => (int) $data['listingPropertyType'],
                'apartment_name' => $data['listingApartmentName'] ?: null,
                'apartment_block' => $data['listingApartmentBlock'] ?: null,
                'room_type' => $data['listingRoomType'],
                'furnish' => $data['listingFurnish'] ?: null,
                'amenities' => array_values($data['listingAmenities'] ?? []),
                'province_id' => $data['listingProvinceId'],
                'province_name' => self::PROVINCES[$data['listingProvinceId']] ?? null,
                'district_id' => $data['listingDistrictId'],
                'district_name' => $this->districts[$data['listingDistrictId']] ?? null,
                'ward_id' => $data['listingWardId'],
                'ward_name' => $this->wards[$data['listingWardId']] ?? null,
                'address' => null,
                'area' => $data['listingArea'] ?: null,
                'price' => $this->normalizeCurrency($data['listingPrice']),
                'price_unit' => '2',
                'floors' => $data['listingFloors'] ?: null,
                'deposit_months' => $data['listingDepositMonths'] ?: null,
                'boost_tier' => $boost['boost_tier'],
                'boost_started_at' => $boost['boost_started_at'],
                'boost_expires_at' => $boost['boost_expires_at'],
                'published_at' => $data['listingPublishedAt'] ?: now(),
                'created_at' => $data['listingPublishedAt'] ?: now(),
                'bedrooms' => $data['listingBedrooms'] ?: null,
                'toilets' => $data['listingToilets'] ?: null,
                'electricity_price' => $data['listingElectricity'] ?: null,
                'water_price' => $data['listingWater'] ?: null,
                'parking_fee' => $data['listingParkingFee'] ?: null,
                'access_hours' => $data['listingAccessHours'] ?: null,
                'has_window' => $data['listingWindow'] ?: null,
                'pets_allowed' => $data['listingPets'] ?: null,
                'parking_available' => $data['listingParking'] ?: null,
                'direction' => null,
                'front_width' => null,
                'road_width' => null,
                'youtube_link' => $data['listingYoutubeLink'] ?: null,
                'youtube_link_short' => $data['listingYoutubeShort'] ?: null,
                'facebook_link' => $data['listingFacebookLink'] ?: null,
                'facebook_video_link' => $data['listingFacebookVideoLink'] ?: null,
                'tiktok_link' => $data['listingTiktokLink'] ?: null,
                'google_map_link' => $data['listingGoogleMapLink'] ?: null,
                'description' => $data['listingDescription'] ?: null,
                'images' => $this->listingImages,
                'avatar' => $avatar,
                'user_id' => auth()->id(),
            ]
        );

        session()->flash('message', 'Đã lưu tin đăng nhà trọ.');
        $this->activeTab = 'listings';
        $this->closeListingModal();
    }

    public function toggleListing(int $id): void
    {
        $listing = ListingModel::findOrFail($id);
        $hidden = ! $listing->is_sold;   // đảo trạng thái: is_sold=true => ẩn
        $listing->update([
            'is_sold' => $hidden,
            'status' => $hidden ? 'inactive' : 'active',
        ]);
        $this->activeTab = 'listings';
    }

    public function updateListingBoost(int $id, string $tier): void
    {
        if (! array_key_exists($tier, self::BOOST_PACKAGES)) {
            return;
        }

        ListingModel::whereKey($id)->update($this->boostPayload($tier, $id));
        $this->activeTab = 'listings';
    }

    public function updateListingPublishedAt(int $id, string $value): void
    {
        if (! $value) {
            return;
        }

        ListingModel::whereKey($id)->update([
            'created_at' => $value,
            'published_at' => $value,
        ]);
        $this->activeTab = 'listings';
    }

    public function deleteListing(int $id): void
    {
        ListingModel::whereKey($id)->delete();
        $this->activeTab = 'listings';
        session()->flash('message', 'Đã xóa tin đăng nhà trọ.');
    }

    public function removeListingImage(int $index): void
    {
        if (isset($this->listingImages[$index])) {
            array_splice($this->listingImages, $index, 1);
        }
    }

    public function removeAvatar(): void
    {
        $this->listingAvatar = '';
    }

    public function closeListingModal(): void
    {
        $this->showListingModal = false;
        $this->resetListingForm();
    }

    public function createBanner(): void
    {
        $this->resetBannerForm();
        $this->activeTab = 'banners';
        $this->showBannerModal = true;
    }

    public function editBanner(int $id): void
    {
        $banner = SiteBanner::findOrFail($id);
        $this->bannerEditingId = $banner->id;
        $this->bannerTitle = $banner->title ?: '';
        $this->bannerSubtitle = $banner->subtitle ?: '';
        $this->bannerImageUrl = $banner->image_url;
        $this->bannerLinkUrl = $banner->link_url ?: '';
        $this->bannerIsActive = (bool) $banner->is_active;
        $this->bannerSortOrder = (int) $banner->sort_order;
        $this->activeTab = 'banners';
        $this->showBannerModal = true;
    }

    public function saveBanner(): void
    {
        $data = $this->validate([
            'bannerTitle' => 'nullable|string|max:180',
            'bannerSubtitle' => 'nullable|string|max:240',
            'bannerImageUrl' => 'nullable|url|max:2048',
            'bannerImageFile' => 'nullable|image|max:4096',
            'bannerLinkUrl' => 'nullable|url|max:2048',
            'bannerIsActive' => 'boolean',
            'bannerSortOrder' => 'required|integer|min:0|max:9999',
        ]);

        $imageUrl = $data['bannerImageUrl'] ?: '';
        if ($this->bannerImageFile) {
            $imageUrl = Storage::disk('public')->url($this->bannerImageFile->store('site/banners', 'public'));
        }

        if (! $imageUrl) {
            $this->addError('bannerImageUrl', 'Vui lòng nhập URL hoặc upload ảnh banner.');
            return;
        }

        SiteBanner::updateOrCreate(
            ['id' => $this->bannerEditingId],
            [
                'title' => $data['bannerTitle'] ?: null,
                'subtitle' => $data['bannerSubtitle'] ?: null,
                'image_url' => $imageUrl,
                'link_url' => $data['bannerLinkUrl'] ?: null,
                'is_active' => (bool) $data['bannerIsActive'],
                'sort_order' => (int) $data['bannerSortOrder'],
            ]
        );

        session()->flash('message', 'Đã lưu banner nhà trọ.');
        $this->activeTab = 'banners';
        $this->closeBannerModal();
    }

    public function saveSiteIdentity(): void
    {
        $data = $this->validate([
            'siteName' => 'required|string|max:80',
            'logoFile' => 'nullable|image|max:2048',
            'contactPhone' => 'nullable|string|max:40',
            'contactZalo' => 'nullable|string|max:255',
            'contactEmail' => 'nullable|string|max:120',
            'contactFacebook' => 'nullable|string|max:255',
            'contactPosition' => 'nullable|in:left,right',
            'watermarkImageFile' => 'nullable|image|max:2048',
            'watermarkMode' => 'nullable|in:image,text',
        ]);

        if ($this->logoFile) {
            $this->logoUrl = Storage::disk('public')->url($this->logoFile->store('site/logo', 'public'));
        }

        if ($this->watermarkImageFile) {
            $this->watermarkImageUrl = Storage::disk('public')->url($this->watermarkImageFile->store('site/watermark', 'public'));
        }

        SiteSetting::updateOrCreate(['key' => 'site_name'], ['value' => $data['siteName']]);
        SiteSetting::updateOrCreate(['key' => 'logo_url'], ['value' => $this->logoUrl ?: null]);
        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => $this->contactPhone ?: null]);
        SiteSetting::updateOrCreate(['key' => 'contact_zalo'], ['value' => $this->contactZalo ?: null]);
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => $this->contactEmail ?: null]);
        SiteSetting::updateOrCreate(['key' => 'contact_facebook'], ['value' => $this->contactFacebook ?: null]);
        SiteSetting::updateOrCreate(['key' => 'contact_position'], ['value' => $this->contactPosition ?: 'right']);
        SiteSetting::updateOrCreate(['key' => 'watermark_image_url'], ['value' => $this->watermarkImageUrl ?: null]);
        SiteSetting::updateOrCreate(['key' => 'watermark_mode'], ['value' => $this->watermarkMode === 'text' ? 'text' : 'image']);

        $this->logoFile = null;
        $this->watermarkImageFile = null;
        $this->activeTab = 'identity';
        session()->flash('message', 'Đã cập nhật nhận diện & liên hệ.');
    }

    public function toggleBanner(int $id): void
    {
        $banner = SiteBanner::findOrFail($id);
        $banner->update(['is_active' => ! $banner->is_active]);
        $this->activeTab = 'banners';
    }

    public function deleteBanner(int $id): void
    {
        SiteBanner::whereKey($id)->delete();
        $this->activeTab = 'banners';
        session()->flash('message', 'Đã xóa banner nhà trọ.');
    }

    public function closeBannerModal(): void
    {
        $this->showBannerModal = false;
        $this->resetBannerForm();
    }

    public function createAccount(): void
    {
        $this->resetAccountForm();
        $this->activeTab = 'accounts';
        $this->showAccountModal = true;
    }

    public function editAccount(int $id): void
    {
        $user = User::findOrFail($id);
        $this->accountEditingId = $user->id;
        $this->accountName = $user->name ?: '';
        $this->accountPhone = $user->phone ?: '';
        $this->accountRole = $user->role ?: 'ctv';
        $this->accountPassword = '';
        $this->accountPostingPlan = $user->posting_plan ?: 'free';
        $this->accountPostingPlanExpiresAt = optional($user->posting_plan_expires_at)->format('Y-m-d') ?: '';
        $this->activeTab = 'accounts';
        $this->showAccountModal = true;
    }

    public function saveAccount(): void
    {
        $data = $this->validate([
            'accountName' => 'required|string|max:255',
            'accountPhone' => [
                'required',
                'regex:/^0\d{9}$/',
                Rule::unique('users', 'phone')->ignore($this->accountEditingId),
            ],
            'accountRole' => 'required|in:' . implode(',', array_keys(self::ROLE_OPTIONS)),
            'accountPostingPlan' => 'required|in:' . implode(',', array_keys(self::POSTING_PLANS)),
            'accountPostingPlanExpiresAt' => 'nullable|date',
            // Tạo mới: bắt buộc đặt mật khẩu. Sửa: để trống = giữ mật khẩu cũ.
            'accountPassword' => [$this->accountEditingId ? 'nullable' : 'required', 'string', 'min:6', 'max:255'],
        ], [
            'accountPhone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'accountPhone.unique' => 'Số điện thoại này đã có tài khoản.',
            'accountPassword.required' => 'Vui lòng đặt mật khẩu cho tài khoản mới.',
            'accountPassword.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $payload = [
            'name' => $data['accountName'],
            'phone' => $data['accountPhone'],
            'role' => $data['accountRole'],
            'posting_plan' => $data['accountPostingPlan'],
            'posting_plan_expires_at' => $data['accountPostingPlanExpiresAt'] ?: null,
        ];

        // password là NOT NULL. Đặt khi tạo mới, hoặc khi sửa mà có nhập mật khẩu mới.
        if (! empty($data['accountPassword'])) {
            $payload['password'] = Hash::make($data['accountPassword']);
        }

        User::updateOrCreate(['id' => $this->accountEditingId], $payload);

        session()->flash('message', 'Đã lưu tài khoản.');
        $this->activeTab = 'accounts';
        $this->closeAccountModal();
    }

    public function deleteAccount(int $id): void
    {
        $this->activeTab = 'accounts';

        if ($id === auth()->id()) {
            session()->flash('message', 'Không thể xóa chính tài khoản đang đăng nhập.');
            return;
        }

        User::whereKey($id)->delete();
        session()->flash('message', 'Đã xóa tài khoản.');
    }

    public function closeAccountModal(): void
    {
        $this->showAccountModal = false;
        $this->resetAccountForm();
    }

    public function setAmenityType(string $type): void
    {
        if (! array_key_exists($type, SiteAmenity::TYPES)) {
            return;
        }

        $this->amenityType = $type;
        $this->activeTab = 'amenities';
        $this->resetPage('amenityPage');
    }

    public function createAmenity(): void
    {
        $this->resetAmenityForm();
        $this->amenityFormType = $this->amenityType;
        $this->amenitySortOrder = (int) (SiteAmenity::query()->type($this->amenityType)->max('sort_order') ?? 0) + 1;
        $this->activeTab = 'amenities';
        $this->showAmenityModal = true;
    }

    public function editAmenity(int $id): void
    {
        $amenity = SiteAmenity::findOrFail($id);
        $this->amenityEditingId = $amenity->id;
        $this->amenityFormType = $amenity->type ?: 'amenity';
        $this->amenityName = $amenity->name;
        $this->amenityKey = $amenity->key;
        $this->amenitySortOrder = (int) $amenity->sort_order;
        $this->amenityIsActive = (bool) $amenity->is_active;
        $this->amenityIconUrl = $amenity->icon ?: '';
        $this->amenityIconFile = null;
        $this->activeTab = 'amenities';
        $this->showAmenityModal = true;
    }

    public function saveAmenity(): void
    {
        // Tự sinh key từ tên nếu chưa nhập.
        if (! $this->amenityKey && $this->amenityName) {
            $this->amenityKey = Str::slug($this->amenityName, '_');
        }

        $data = $this->validate([
            'amenityName' => 'required|string|max:120',
            'amenityKey' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('site_amenities', 'key')->ignore($this->amenityEditingId),
            ],
            'amenityFormType' => 'required|in:' . implode(',', array_keys(SiteAmenity::TYPES)),
            'amenitySortOrder' => 'required|integer|min:0|max:9999',
            'amenityIsActive' => 'boolean',
            'amenityIconFile' => 'nullable|image|max:2048',
        ], [
            'amenityKey.regex' => 'Mã chỉ gồm chữ thường, số và dấu gạch dưới.',
            'amenityKey.unique' => 'Mã này đã tồn tại.',
        ]);

        $iconUrl = $this->amenityIconUrl ?: null;
        if ($this->amenityIconFile) {
            $iconUrl = Storage::disk('public')->url($this->amenityIconFile->store('site/amenities', 'public'));
        }

        SiteAmenity::updateOrCreate(
            ['id' => $this->amenityEditingId],
            [
                'key' => $data['amenityKey'],
                'name' => $data['amenityName'],
                'type' => $data['amenityFormType'],
                'icon' => $iconUrl,
                'sort_order' => (int) $data['amenitySortOrder'],
                'is_active' => (bool) $data['amenityIsActive'],
            ]
        );

        // Quay về đúng segment của loại vừa lưu.
        $this->amenityType = $data['amenityFormType'];

        session()->flash('message', 'Đã lưu ' . (SiteAmenity::TYPES[$data['amenityFormType']] ?? 'mục') . '.');
        $this->activeTab = 'amenities';
        $this->closeAmenityModal();
    }

    public function toggleAmenity(int $id): void
    {
        $amenity = SiteAmenity::findOrFail($id);
        $amenity->update(['is_active' => ! $amenity->is_active]);
        $this->activeTab = 'amenities';
    }

    public function deleteAmenity(int $id): void
    {
        SiteAmenity::whereKey($id)->delete();
        $this->activeTab = 'amenities';
        session()->flash('message', 'Đã xóa tiện ích / nội thất.');
    }

    public function closeAmenityModal(): void
    {
        $this->showAmenityModal = false;
        $this->resetAmenityForm();
    }

    private function listingQuery()
    {
        return $this->publicRoomQuery()
            ->when($this->listingModeration === 'pending', fn ($q) => $q->where('moderation_status', 'pending'))
            ->when($this->listingModeration === 'active', fn ($q) => $q->where('is_sold', false)->where(function ($sub) { $sub->whereNull('status')->orWhere('status', 'active'); })->where('moderation_status', 'approved'))
            ->when($this->listingModeration === 'rejected', fn ($q) => $q->where('moderation_status', 'rejected'))
            ->when($this->listingModeration === 'hidden', fn ($q) => $q->where(function ($sub) { $sub->where('is_sold', true)->orWhere('status', 'inactive'); }))
            ->when($this->listingModeration === 'boosting', fn ($q) => $q->where('boost_tier', '<>', 'normal')->where('boost_expires_at', '>', now()))
            ->when($this->listingSearch, function ($query) {
                $term = '%' . trim($this->listingSearch) . '%';
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('title', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('contact_phone', 'like', $term)
                        ->orWhere('district_name', 'like', $term)
                        ->orWhere('ward_name', 'like', $term);
                });
            });
    }

    private function publicRoomQuery()
    {
        return ListingModel::query()
            ->whereIn('property_type', [115, 108, 103]);
    }

    private function boostPayload(string $tier, ?int $listingId = null): array
    {
        $package = self::BOOST_PACKAGES[$tier] ?? self::BOOST_PACKAGES['normal'];
        if ($tier === 'normal' || (int) $package['days'] <= 0) {
            return [
                'boost_tier' => 'normal',
                'boost_started_at' => null,
                'boost_expires_at' => null,
            ];
        }

        $existing = $listingId ? ListingModel::find($listingId) : null;
        $keepCurrentStart = $existing?->boost_started_at
            && $existing->boost_tier === $tier
            && $existing->boost_expires_at
            && $existing->boost_expires_at->isFuture();
        $start = $keepCurrentStart ? $existing->boost_started_at : now();

        return [
            'boost_tier' => $tier,
            'boost_started_at' => $start,
            'boost_expires_at' => $start->copy()->addDays((int) $package['days']),
        ];
    }

    private function accountQuery()
    {
        return User::query()
            ->when($this->accountSearch, function ($query) {
                $term = '%' . trim($this->accountSearch) . '%';
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });
    }

    private function resetAccountForm(): void
    {
        $this->accountEditingId = null;
        $this->accountName = '';
        $this->accountPhone = '';
        $this->accountRole = 'ctv';
        $this->accountPassword = '';
        $this->accountPostingPlan = 'free';
        $this->accountPostingPlanExpiresAt = '';
        $this->resetValidation();
    }

    private function resetAmenityForm(): void
    {
        $this->amenityEditingId = null;
        $this->amenityFormType = $this->amenityType;
        $this->amenityName = '';
        $this->amenityKey = '';
        $this->amenitySortOrder = 0;
        $this->amenityIsActive = true;
        $this->amenityIconUrl = '';
        $this->amenityIconFile = null;
        $this->resetValidation();
    }

    private function resetListingForm(): void
    {
        $this->listingEditingId = null;
        $this->listingTitle = '';
        $this->listingCode = '';
        $this->listingContactPhone = '';
        $this->listingHousePassword = '';
        $this->listingProvinceId = '79';
        $this->listingDistrictId = '';
        $this->listingWardId = '';
        $this->listingPrice = '';
        $this->listingPropertyType = 115;
        $this->listingRoomType = 'studio';
        $this->listingFurnish = 'basic';
        $this->listingAmenities = [];
        $this->listingArea = '';
        $this->listingFloors = '';
        $this->listingDepositMonths = '';
        $this->listingApartmentName = '';
        $this->listingApartmentBlock = '';
        $this->listingBoostTier = 'normal';
        $this->listingPublishedAt = '';
        $this->listingBedrooms = '';
        $this->listingToilets = '';
        $this->listingElectricity = '';
        $this->listingWater = '';
        $this->listingParkingFee = '';
        $this->listingAccessHours = '';
        $this->listingWindow = '';
        $this->listingPets = '';
        $this->listingParking = '';
        $this->listingDescription = '';
        $this->listingYoutubeLink = '';
        $this->listingYoutubeShort = '';
        $this->listingFacebookLink = '';
        $this->listingFacebookVideoLink = '';
        $this->listingTiktokLink = '';
        $this->listingGoogleMapLink = '';
        $this->listingAvatar = '';
        $this->listingAvatarFile = null;
        $this->listingImages = [];
        $this->listingImageFiles = [];
        $this->listingIsSold = false;
        $this->wards = [];
        $this->loadDistricts($this->listingProvinceId);
        $this->resetValidation();
    }

    private function resetBannerForm(): void
    {
        $this->bannerEditingId = null;
        $this->bannerTitle = '';
        $this->bannerSubtitle = '';
        $this->bannerImageUrl = '';
        $this->bannerLinkUrl = '';
        $this->bannerIsActive = true;
        $this->bannerSortOrder = 0;
        $this->bannerImageFile = null;
        $this->resetValidation();
    }

    private function loadDistricts(string $provinceId): void
    {
        $this->districts = [];
        if (! $provinceId) {
            return;
        }

        $data = $this->getLocationData();
        foreach (($data[$provinceId]['districts'] ?? []) as $id => $district) {
            $this->districts[$id] = $district['name'];
        }
    }

    private function loadWards(string $districtId): void
    {
        $this->wards = [];
        if (! $districtId || ! $this->listingProvinceId) {
            return;
        }

        $data = $this->getLocationData();
        $this->wards = $data[$this->listingProvinceId]['districts'][$districtId]['wards'] ?? [];
    }

    private function getLocationData(): array
    {
        if ($this->locationData !== null) {
            return $this->locationData;
        }

        // Dùng cache nếu đã có dữ liệu thật; KHÔNG cache kết quả rỗng để khi
        // file all_vietnam.json được thêm vào (hosting) thì cascade chạy ngay.
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

    private function generateListingCode(): string
    {
        for ($i = 0; $i < 8; $i++) {
            $code = '#NT' . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            // withTrashed: unique index trong DB vẫn tính cả bản ghi đã xóa mềm.
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
}
