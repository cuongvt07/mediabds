<?php

namespace App\Livewire;

use App\Models\RealEstateListing as ListingModel;
use App\Models\SiteBanner;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
    public $listingRoomType = 'studio';
    public $listingFurnish = 'basic';
    public $listingAmenities = [];
    public $listingBedrooms = '';
    public $listingToilets = '';
    public $listingDescription = '';
    public $listingYoutubeLink = '';
    public $listingYoutubeShort = '';
    public $listingFacebookLink = '';
    public $listingFacebookVideoLink = '';
    public $listingTiktokLink = '';
    public $listingGoogleMapLink = '';
    public $listingImages = [];
    public $listingImageFiles = [];
    public $listingIsSold = false;
    public $districts = [];
    public $wards = [];

    // Component (not model) — resolves to App\Livewire\RealEstateListing in this namespace.
    public const PROVINCES = RealEstateListing::PROVINCES;

    public $siteName = 'NHÀ TRỌ SÀI GÒN';
    public $logoUrl = '';
    public $logoFile;

    protected $queryString = [
        'activeTab' => ['except' => 'dashboard', 'as' => 'tab'],
        'listingSearch' => ['except' => '', 'as' => 'q'],
    ];

    protected $locationData = null;

    public function mount(): void
    {
        $this->siteName = SiteSetting::query()->whereKey('site_name')->value('value') ?: 'NHÀ TRỌ SÀI GÒN';
        $this->logoUrl = SiteSetting::query()->whereKey('logo_url')->value('value') ?: '';
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
            'roomTypes' => self::ROOM_TYPES,
            'furnishTypes' => self::FURNISH_TYPES,
            'amenityOptions' => self::AMENITIES,
            'provinces' => self::PROVINCES,
        ])->layout('components.layouts.site-admin', [
            'title' => 'Quản trị nhà trọ',
        ]);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['dashboard', 'listings', 'banners', 'identity'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage('listingPage');
        $this->resetPage('bannerPage');
    }

    public function updatedListingSearch(): void
    {
        $this->resetPage('listingPage');
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
        $this->listingRoomType = $listing->room_type ?: 'studio';
        $this->listingFurnish = $listing->furnish ?: 'basic';
        $this->listingAmenities = array_values($listing->amenities ?: []);
        $this->listingBedrooms = $listing->bedrooms ?: '';
        $this->listingToilets = $listing->toilets ?: '';
        $this->listingDescription = $listing->description ?: '';
        $this->listingYoutubeLink = $listing->youtube_link ?: '';
        $this->listingYoutubeShort = $listing->youtube_link_short ?: '';
        $this->listingFacebookLink = $listing->facebook_link ?: '';
        $this->listingFacebookVideoLink = $listing->facebook_video_link ?: '';
        $this->listingTiktokLink = $listing->tiktok_link ?: '';
        $this->listingGoogleMapLink = $listing->google_map_link ?: '';
        $this->listingImages = array_values($listing->images ?: []);
        if ($listing->avatar && ! in_array($listing->avatar, $this->listingImages, true)) {
            array_unshift($this->listingImages, $listing->avatar);
        }
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
            'listingPrice' => 'required',
            'listingRoomType' => 'required|in:duplex,studio,loft,balcony',
            'listingFurnish' => 'nullable|in:full,basic,empty',
            'listingAmenities' => 'array',
            'listingAmenities.*' => 'in:' . implode(',', array_keys(self::AMENITIES)),
            'listingBedrooms' => 'nullable|integer|min:0|max:20',
            'listingToilets' => 'nullable|integer|min:0|max:20',
            'listingDescription' => 'nullable|string',
            'listingYoutubeLink' => 'nullable|url|max:2048',
            'listingYoutubeShort' => 'nullable|url|max:2048',
            'listingFacebookLink' => 'nullable|url|max:2048',
            'listingFacebookVideoLink' => 'nullable|url|max:2048',
            'listingTiktokLink' => 'nullable|url|max:2048',
            'listingGoogleMapLink' => 'nullable|url|max:2048',
            'listingImageFiles.*' => 'nullable|image|max:4096',
            'listingIsSold' => 'boolean',
        ]);

        foreach ($this->listingImageFiles as $file) {
            $this->listingImages[] = Storage::disk('public')->url($file->store('site/listings', 'public'));
        }

        $this->listingImages = array_values(array_filter($this->listingImages));
        $code = $this->listingCode ?: $this->generateListingCode();

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
                'property_type' => 115,
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
                'area' => null,
                'price' => $this->normalizeCurrency($data['listingPrice']),
                'price_unit' => '2',
                'floors' => null,
                'bedrooms' => $data['listingBedrooms'] ?: null,
                'toilets' => $data['listingToilets'] ?: null,
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
                'avatar' => $this->listingImages[0] ?? null,
                'user_id' => auth()->id(),
            ]
        );

        session()->flash('message', 'Đã lưu tin đăng nhà trọ.');
        $this->activeTab = 'listings';
        $this->closeListingModal();
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
        ]);

        if ($this->logoFile) {
            $this->logoUrl = Storage::disk('public')->url($this->logoFile->store('site/logo', 'public'));
        }

        SiteSetting::updateOrCreate(['key' => 'site_name'], ['value' => $data['siteName']]);
        SiteSetting::updateOrCreate(['key' => 'logo_url'], ['value' => $this->logoUrl ?: null]);

        $this->logoFile = null;
        $this->activeTab = 'identity';
        session()->flash('message', 'Đã cập nhật logo nhà trọ.');
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

    private function listingQuery()
    {
        return $this->publicRoomQuery()
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
            ->where('property_type', 115);
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
        $this->listingRoomType = 'studio';
        $this->listingFurnish = 'basic';
        $this->listingAmenities = [];
        $this->listingBedrooms = '';
        $this->listingToilets = '';
        $this->listingDescription = '';
        $this->listingYoutubeLink = '';
        $this->listingYoutubeShort = '';
        $this->listingFacebookLink = '';
        $this->listingFacebookVideoLink = '';
        $this->listingTiktokLink = '';
        $this->listingGoogleMapLink = '';
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
        if ($this->locationData === null) {
            $this->locationData = Cache::remember('vietnam_locations_full', 86400, function () {
                $path = 'locations/all_vietnam.json';
                if (Storage::disk('local')->exists($path)) {
                    return json_decode(Storage::disk('local')->get($path), true);
                }
                return [];
            });
        }

        return $this->locationData ?: [];
    }

    private function generateListingCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = '#NT' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            if (! ListingModel::where('code', $code)->exists()) {
                return $code;
            }
        }

        return '#NT' . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
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
