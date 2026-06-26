<?php

namespace App\Livewire;

use App\Models\BlogPost;
use App\Models\ListingCategory;
use App\Models\ListingContactRequest;
use App\Models\ListingReport;
use App\Models\RealEstateListing;
use App\Models\User;
use App\Models\VehicleBrand;
use App\Models\VehicleListing as VehicleModel;
use App\Models\UserInvite;
use App\Models\WebsiteHomeSection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class WebsiteAdmin extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $activeTab = 'overview';

    public $listingSearch = '';
    public $listingStatus = 'all';
    public $listingVip = 'all';

    // --- Listing create/edit form (full CRUD, mirrors the /listings form) ---
    public $showListingModal = false;
    public $listingFormId = null;          // null = create, id = edit
    public $listingTitle = '';
    public $listingType = 'Cần bán';
    public $listingContactType = '';
    public $listingContactPhone = '';
    public $listingHousePassword = '';
    public $listingCode = '';
    public $listingState = 'active';       // -> status column
    public $listingTier = 'normal';        // -> vip_tier column
    public $listingPropertyType = 110;
    public $listingProvinceId = '';
    public $listingDistrictId = '';
    public $listingWardId = '';
    public $listingAddress = '';
    public $listingArea = '';
    public $listingPrice = '';
    public $listingPriceUnit = '1';        // 1=VNĐ, 2=VNĐ/tháng, 3=VNĐ/m2 (khớp form /listings)
    public $listingFloors = '';
    public $listingBedrooms = '';
    public $listingToilets = '';
    public $listingDirection = '';          // 1..8 theo \App\Livewire\RealEstateListing::DIRECTIONS
    public $listingFrontWidth = '';
    public $listingRoadWidth = '';
    public $listingYoutubeLink = '';
    public $listingFacebookLink = '';
    public $listingFacebookVideoLink = '';
    public $listingGoogleMapLink = '';
    public $listingTiktokLink = '';
    public $listingDescription = '';
    public $listingImages = [];
    public $listingAvatar = '';
    public $listingReporterId = '';
    public $listingDistricts = [];         // [districtId => name] for the picked province
    public $listingWards = [];             // [wardId => name] for the picked district
    protected $listingLocationCache = null;

    public $showHomeSectionModal = false;
    public $homeSectionEditingId = null;
    public $homeSectionKey = '';
    public $homeSectionTitle = '';
    public $homeSectionDescription = '';
    public $homeSectionType = 'listings';
    public $homeSectionEnabled = true;
    public $homeSectionSourceType = 'latest';
    public $homeSectionTransactionType = '';
    public $homeSectionPropertyKind = '';
    public $homeSectionCategoryId = '';
    public $homeSectionProvinceName = '';
    public $homeSectionSortBy = 'created_at';
    public $homeSectionSortOrder = 'desc';
    public $homeSectionLimit = 8;
    public $homeSectionHref = '';
    public $homeSectionManualIds = '';
    public $homeSectionSortOrderIndex = 0;

    public $categorySearch = '';
    public $showCategoryModal = false;
    public $categoryEditing = false;
    public $categoryId = '';
    public $categoryName = '';
    public $categorySlug = '';
    public $categoryTransactionType = 'both';
    public $categoryPropertyType = '';
    public $categoryIcon = '';
    public $categorySortOrder = 0;

    public $blogSearch = '';
    public $blogStatus = 'all';
    public $showBlogModal = false;
    public $blogEditingId = null;
    public $blogTitle = '';
    public $blogSlug = '';
    public $blogExcerpt = '';
    public $blogContent = '';
    public $blogCoverImage = '';
    public $blogAuthorName = 'BDS Việt';
    public $blogCategoryTag = 'Tin tức';
    public $blogTags = '';
    public $blogReadingMinutes = 5;
    public $blogStatusValue = 'published';
    public $blogPublishedAt = '';

    public $leadSearch = '';
    public $leadStatus = 'all';
    public $showLeadModal = false;
    public $leadEditingId = null;
    public $leadName = '';
    public $leadPhone = '';
    public $leadMessage = '';
    public $leadStatusValue = 'new';
    public $leadAdminNote = '';

    public $accountSearch = '';
    public $accountRole = 'all';
    public $selectedAccountId = null;
    public $showAccountModal = false;
    public $showAccountDeleteModal = false;
    public $accountEditingId = null;
    public $accountName = '';
    public $accountPhone = '';
    public $accountRoleValue = 'buyer';
    public $accountPropertyTypes = [];
    public $accountInviterUserId = '';
    public $accountRootInviteCode = '';
    public $accountExistingInviteCode = '';
    public $accountViewPhonePin = '';

    public $settings = [];

    public $reportSearch = '';
    public $reportStatus = 'pending';
    public $reportTarget = 'all';
    public $showReportModal = false;
    public $reportEditingId = null;
    public $reportAdminReason = '';

    public $showMediaPicker = false;
    public $mediaTarget = null;   // wire path, e.g. 'settings.branding.logo' or 'blogCoverImage'
    public $mediaUpload;          // temporary uploaded file

    protected $queryString = [
        'activeTab' => ['except' => 'overview', 'as' => 'tab'],
        'listingSearch' => ['except' => ''],
        'listingStatus' => ['except' => 'all'],
        'listingVip' => ['except' => 'all'],
        'categorySearch' => ['except' => ''],
        'blogSearch' => ['except' => ''],
        'blogStatus' => ['except' => 'all'],
        'leadSearch' => ['except' => ''],
        'leadStatus' => ['except' => 'all'],
        'accountSearch' => ['except' => ''],
        'accountRole' => ['except' => 'all'],
        'reportSearch' => ['except' => ''],
        'reportStatus' => ['except' => 'pending'],
        'reportTarget' => ['except' => 'all'],
    ];

    public function setTab($tab)
    {
        $allowed = ['overview', 'home', 'listings', 'vehicles', 'vehicle-brands', 'categories', 'blogs', 'accounts', 'leads', 'reports', 'favorites', 'saved-searches', 'analytics', 'settings'];
        if (in_array($tab, $allowed, true)) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function mount()
    {
        $this->settings = Schema::hasTable('site_settings')
            ? \App\Models\SiteSetting::values()
            : config('site.defaults');
    }

    public function rendered()
    {
        // The flash message is shown as a toast inside this component's view
        // (so it appears on Livewire AJAX saves). Clear it afterwards so it
        // doesn't re-appear on the next interaction.
        session()->forget('message');
    }

    /**
     * Short Vietnamese money format: 3.150.000.000 → "3,15 tỷ", 550.000.000 → "550 triệu".
     */
    public function formatMoneyShort($amount): string
    {
        $n = (float) $amount;
        if ($n <= 0) {
            return '—';
        }

        if ($n >= 1000000000) {
            return rtrim(rtrim(number_format($n / 1000000000, 2, ',', '.'), '0'), ',') . ' tỷ';
        }

        if ($n >= 1000000) {
            return rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',') . ' triệu';
        }

        return number_format($n, 0, ',', '.') . ' đ';
    }

    public function updated($property)
    {
        if (Str::contains($property, ['Search', 'Status', 'Vip', 'Role', 'Target'])) {
            $this->resetPage();
        }
    }

    /* =========================================================
     |  Quản lý tin đăng — tạo / sửa (CRUD đầy đủ như /listings)
     |  Tái sử dụng hằng số tỉnh/loại BĐS/hướng từ component
     |  \App\Livewire\RealEstateListing và media picker sẵn có.
     * =======================================================*/

    /** Dữ liệu địa giới (tỉnh → huyện → xã) dùng chung cache với /listings. */
    protected function listingLocationData(): array
    {
        if ($this->listingLocationCache === null) {
            $this->listingLocationCache = \Illuminate\Support\Facades\Cache::remember('vietnam_locations_full', 86400, function () {
                $path = 'locations/all_vietnam.json';
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
                    return json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($path), true) ?: [];
                }
                return [];
            });
        }

        return $this->listingLocationCache;
    }

    protected function fetchListingDistricts($provinceId): void
    {
        $data = $this->listingLocationData();
        $this->listingDistricts = [];
        if (! empty($data[$provinceId]['districts'])) {
            foreach ($data[$provinceId]['districts'] as $id => $district) {
                $this->listingDistricts[$id] = $district['name'];
            }
        }
    }

    protected function fetchListingWards($districtId): void
    {
        $data = $this->listingLocationData();
        $this->listingWards = [];
        $provinceId = $this->listingProvinceId;
        if (! empty($data[$provinceId]['districts'][$districtId]['wards'])) {
            $this->listingWards = $data[$provinceId]['districts'][$districtId]['wards'];
        }
    }

    public function updatedListingProvinceId($value): void
    {
        $this->listingDistricts = [];
        $this->listingWards = [];
        $this->listingDistrictId = '';
        $this->listingWardId = '';
        if ($value) {
            $this->fetchListingDistricts($value);
        }
    }

    public function updatedListingDistrictId($value): void
    {
        $this->listingWards = [];
        $this->listingWardId = '';
        if ($value) {
            $this->fetchListingWards($value);
        }
    }

    public function updatedListingPropertyType($value): void
    {
        // Tự sinh mã tin theo loại BĐS khi đang tạo mới và chưa có mã.
        if (! $this->listingFormId && $value && empty($this->listingCode)) {
            $this->listingCode = $this->generateListingCode($value);
        }
    }

    protected function generateListingCode($propertyType): string
    {
        $prefix = \App\Livewire\RealEstateListing::PREFIX_MAP[$propertyType] ?? '#RE';

        for ($i = 0; $i < 5; $i++) {
            $code = $prefix . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            if (! RealEstateListing::where('code', $code)->exists()) {
                return $code;
            }
        }

        return $prefix . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function openCreateListing(): void
    {
        $this->resetListingForm();
        $this->showListingModal = true;
    }

    public function editListing($id): void
    {
        $listing = RealEstateListing::find($id);
        if (! $listing) {
            return;
        }

        $this->listingFormId = $listing->id;
        $this->listingTitle = $listing->title ?: '';
        $this->listingType = $listing->type ?: 'Cần bán';
        $this->listingContactType = $listing->contact_type ?: '';
        $this->listingContactPhone = $listing->contact_phone ?: '';
        $this->listingHousePassword = $listing->house_password ?: '';
        $this->listingCode = $listing->code ?: '';
        $this->listingState = $listing->is_sold ? 'sold' : ($listing->status ?: 'active');
        $this->listingTier = $listing->vip_tier ?: 'normal';
        $this->listingPropertyType = $listing->property_type ?: 110;
        $this->listingProvinceId = $listing->province_id ?: '';

        if ($this->listingProvinceId) {
            $this->fetchListingDistricts($this->listingProvinceId);
        }
        $this->listingDistrictId = $listing->district_id ?: '';

        if ($this->listingDistrictId) {
            $this->fetchListingWards($this->listingDistrictId);
        }
        $this->listingWardId = $listing->ward_id ?: '';

        $this->listingAddress = $listing->address ?: '';
        $this->listingArea = $listing->area !== null ? $this->formatDecimalForInput($listing->area) : '';
        $this->listingPrice = $listing->price !== null ? $this->formatDecimalForInput($listing->price) : '';
        $this->listingPriceUnit = $listing->price_unit ?: '1';
        $this->listingFloors = $listing->floors ?? '';
        $this->listingBedrooms = $listing->bedrooms ?? '';
        $this->listingToilets = $listing->toilets ?? '';
        $this->listingDirection = (string) ($listing->direction ?? '');
        $this->listingFrontWidth = $listing->front_width !== null ? $this->formatDecimalForInput($listing->front_width) : '';
        $this->listingRoadWidth = $listing->road_width !== null ? $this->formatDecimalForInput($listing->road_width) : '';
        $this->listingYoutubeLink = $listing->youtube_link ?: '';
        $this->listingFacebookLink = $listing->facebook_link ?: '';
        $this->listingFacebookVideoLink = $listing->facebook_video_link ?: '';
        $this->listingGoogleMapLink = $listing->google_map_link ?: '';
        $this->listingTiktokLink = $listing->tiktok_link ?: '';
        $this->listingDescription = $listing->description ?: '';
        $this->listingImages = is_array($listing->images) ? array_values($listing->images) : [];
        $this->listingAvatar = $listing->avatar ?: '';
        $this->listingReporterId = $listing->reporter_id ?: '';

        $this->resetValidation();
        $this->showListingModal = true;
    }

    public function saveListing(): void
    {
        // Chuẩn hoá giá (VN: "3.150.000.000" hoặc "3,15") -> số thực.
        $this->listingPrice = $this->normalizeListingCurrency($this->listingPrice);

        foreach (['listingArea', 'listingFrontWidth', 'listingRoadWidth'] as $field) {
            $this->{$field} = $this->normalizeListingDecimal($this->{$field});
        }
        foreach (['listingFloors', 'listingBedrooms', 'listingToilets'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }
        foreach (['listingFacebookLink', 'listingFacebookVideoLink', 'listingGoogleMapLink', 'listingTiktokLink'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        $rules = [
            'listingTitle' => 'required|string|max:255',
            'listingState' => 'required|in:pending,active,expired,rejected,sold',
            'listingTier' => 'required|in:normal,vip1,vip2,vip3',
            'listingFacebookLink' => 'nullable|url|max:2000',
            'listingFacebookVideoLink' => 'nullable|url|max:2000',
            'listingGoogleMapLink' => 'nullable|url|max:2000',
            'listingTiktokLink' => 'nullable|url|max:2000',
            'listingReporterId' => 'nullable|exists:users,id',
        ];

        if ($this->listingType !== 'Cần mua') {
            $rules['listingProvinceId'] = 'required';
            $rules['listingPrice'] = 'required|numeric';
        }

        $this->validate($rules, [], [
            'listingTitle' => 'tiêu đề',
            'listingProvinceId' => 'tỉnh/thành',
            'listingPrice' => 'giá',
        ]);

        if (! $this->listingFormId && empty($this->listingCode)) {
            $this->listingCode = $this->generateListingCode($this->listingPropertyType ?: 110);
        }

        $isSold = $this->listingState === 'sold';

        $data = [
            'title' => $this->listingTitle,
            'type' => $this->listingType,
            'contact_type' => $this->listingContactType ?: null,
            'contact_phone' => $this->listingContactPhone ?: null,
            'house_password' => $this->listingHousePassword ?: null,
            'code' => $this->listingCode,
            'status' => $this->listingState,
            'is_sold' => $isSold,
            'vip_tier' => $this->listingTier,
            'property_type' => $this->listingPropertyType,
            'province_id' => $this->listingProvinceId ?: null,
            'district_id' => $this->listingDistrictId ?: null,
            'ward_id' => $this->listingWardId ?: null,
            'province_name' => \App\Livewire\RealEstateListing::PROVINCES[$this->listingProvinceId] ?? null,
            'district_name' => $this->listingDistricts[$this->listingDistrictId] ?? null,
            'ward_name' => $this->listingWards[$this->listingWardId] ?? null,
            'address' => $this->listingAddress ?: null,
            'area' => $this->listingArea,
            'price' => $this->listingPrice,
            'price_unit' => $this->listingPriceUnit,
            'floors' => $this->listingFloors,
            'bedrooms' => $this->listingBedrooms,
            'toilets' => $this->listingToilets,
            'direction' => $this->listingDirection,
            'front_width' => $this->listingFrontWidth,
            'road_width' => $this->listingRoadWidth,
            'youtube_link' => $this->listingYoutubeLink ?: null,
            'facebook_link' => $this->listingFacebookLink,
            'facebook_video_link' => $this->listingFacebookVideoLink,
            'google_map_link' => $this->listingGoogleMapLink,
            'tiktok_link' => $this->listingTiktokLink,
            'description' => $this->listingDescription ?: null,
            'images' => array_values($this->listingImages ?: []),
            'avatar' => $this->listingAvatar ?: null,
            'reporter_id' => $this->listingReporterId ?: null,
        ];

        if ($this->listingState === 'rejected') {
            // giữ rejection_reason hiện có; nếu bỏ trạng thái từ chối thì xoá lý do
        } else {
            $data['rejection_reason'] = null;
        }

        try {
            if ($this->listingFormId) {
                RealEstateListing::where('id', $this->listingFormId)->update($data);
                $message = 'Đã cập nhật tin đăng.';
            } else {
                $data['user_id'] = auth()->id();
                RealEstateListing::create($data);
                $message = 'Đã thêm tin đăng mới.';
            }

            \Illuminate\Support\Facades\Cache::put('listings_version', time(), now()->addDay());
            session()->flash('message', $message);
            $this->closeListingModal();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('listingTitle', 'Lưu tin thất bại: ' . $e->getMessage());
        }
    }

    public function removeListingImage($index): void
    {
        if (isset($this->listingImages[$index])) {
            array_splice($this->listingImages, $index, 1);
        }
    }

    public function setListingAvatarFromImage($index): void
    {
        if (isset($this->listingImages[$index])) {
            $this->listingAvatar = $this->listingImages[$index];
        }
    }

    public function closeListingModal(): void
    {
        $this->showListingModal = false;
        $this->resetListingForm();
    }

    protected function resetListingForm(): void
    {
        $this->listingFormId = null;
        $this->listingTitle = '';
        $this->listingType = 'Cần bán';
        $this->listingContactType = '';
        $this->listingContactPhone = '';
        $this->listingHousePassword = '';
        $this->listingCode = '';
        $this->listingState = 'active';
        $this->listingTier = 'normal';
        $this->listingPropertyType = 110;
        $this->listingProvinceId = '';
        $this->listingDistrictId = '';
        $this->listingWardId = '';
        $this->listingAddress = '';
        $this->listingArea = '';
        $this->listingPrice = '';
        $this->listingPriceUnit = '1';
        $this->listingFloors = '';
        $this->listingBedrooms = '';
        $this->listingToilets = '';
        $this->listingDirection = '';
        $this->listingFrontWidth = '';
        $this->listingRoadWidth = '';
        $this->listingYoutubeLink = '';
        $this->listingFacebookLink = '';
        $this->listingFacebookVideoLink = '';
        $this->listingGoogleMapLink = '';
        $this->listingTiktokLink = '';
        $this->listingDescription = '';
        $this->listingImages = [];
        $this->listingAvatar = '';
        $this->listingReporterId = '';
        $this->listingDistricts = [];
        $this->listingWards = [];
        $this->resetValidation();
    }

    private function formatDecimalForInput($value): string
    {
        $n = (float) $value;
        if ($n == 0.0) {
            return $value === 0 || $value === '0' || $value === 0.0 ? '0' : '';
        }

        // Số nguyên -> phân tách hàng nghìn; có thập phân -> dùng dấu phẩy.
        if (floor($n) == $n) {
            return number_format($n, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($n, 4, ',', '.'), '0'), ',');
    }

    private function normalizeListingCurrency($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = str_replace(['.', ' '], '', (string) $value);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function normalizeListingDecimal($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_string($value)) {
            $clean = str_replace(' ', '', $value);
            if (str_contains($clean, ',') && str_contains($clean, '.')) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } elseif (str_contains($clean, ',')) {
                $clean = str_replace(',', '.', $clean);
            }
            $clean = preg_replace('/[^0-9.]/', '', $clean);

            return is_numeric($clean) ? (float) $clean : null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    public function openReport($id)
    {
        if (! Schema::hasTable('listing_reports')) {
            return;
        }

        $report = ListingReport::findOrFail($id);
        $this->reportEditingId = $report->id;
        $this->reportAdminReason = $report->admin_reason ?: '';
        $this->showReportModal = true;
    }

    /**
     * Resolve a report. $action = 'remove' (gỡ bài) | 'keep' (giữ bài).
     * Both require an admin reason that is surfaced to the user.
     */
    public function resolveReport($action)
    {
        if (! in_array($action, ['remove', 'keep'], true) || ! $this->reportEditingId || ! Schema::hasTable('listing_reports')) {
            return;
        }

        $this->validate([
            'reportAdminReason' => 'required|string|min:5|max:2000',
        ], [], ['reportAdminReason' => 'lý do']);

        $report = ListingReport::findOrFail($this->reportEditingId);

        $report->update([
            'status' => $action === 'remove' ? 'resolved_removed' : 'resolved_kept',
            'admin_reason' => $this->reportAdminReason,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        // "Gỡ" → mark the listing rejected with the reason so the owner sees it.
        if ($action === 'remove' && $report->listing_id) {
            RealEstateListing::where('id', $report->listing_id)->update([
                'status' => 'rejected',
                'rejection_reason' => $this->reportAdminReason,
            ]);
        }

        session()->flash('message', $action === 'remove' ? 'Đã gỡ bài và lưu lý do.' : 'Đã giữ bài và lưu phản hồi.');
        $this->closeReportModal();
    }

    public function deleteReport($id)
    {
        if (Schema::hasTable('listing_reports')) {
            ListingReport::where('id', $id)->delete();
            session()->flash('message', 'Đã xóa báo cáo.');
        }
    }

    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->reportEditingId = null;
        $this->reportAdminReason = '';
        $this->resetValidation();
    }

    public function openMediaPicker($target)
    {
        $this->mediaTarget = $target;
        $this->mediaUpload = null;
        $this->resetErrorBag('mediaUpload');
        $this->showMediaPicker = true;
    }

    public function closeMediaPicker()
    {
        $this->showMediaPicker = false;
        $this->mediaTarget = null;
        $this->mediaUpload = null;
    }

    /**
     * Handle a new upload (max 3MB) → store to the media library and apply the URL.
     */
    public function updatedMediaUpload()
    {
        $this->validate([
            'mediaUpload' => 'image|mimes:jpg,jpeg,png,webp,gif,svg|max:3072',
        ], [], ['mediaUpload' => 'ảnh']);

        $url = $this->storeMediaUpload($this->mediaUpload);
        if ($url) {
            $this->applyMediaValue($url);
            session()->flash('message', 'Đã tải ảnh lên thư viện.');
            // Gallery tin đăng/tin xe: giữ picker mở để chọn/tải nhiều ảnh.
            if (in_array($this->mediaTarget, ['listingImages', 'vehicleImages'], true)) {
                $this->mediaUpload = null;
            } else {
                $this->closeMediaPicker();
            }
        }
    }

    public function selectExistingMedia($url)
    {
        if ($url) {
            $this->applyMediaValue($url);
        }
        // Gallery tin đăng/tin xe: cho phép chọn nhiều ảnh liên tiếp, không đóng picker.
        if (! in_array($this->mediaTarget, ['listingImages', 'vehicleImages'], true)) {
            $this->closeMediaPicker();
        }
    }

    public function clearMedia($target)
    {
        $this->mediaTarget = $target;
        $this->applyMediaValue('');
        $this->mediaTarget = null;
    }

    private function applyMediaValue($url)
    {
        if (! $this->mediaTarget) {
            return;
        }

        // Gallery tin đăng: nối ảnh vào mảng thay vì gán đè.
        if ($this->mediaTarget === 'listingImages') {
            if ($url !== '' && ! in_array($url, $this->listingImages, true)) {
                $this->listingImages[] = $url;
            }
            return;
        }

        // Gallery tin xe.
        if ($this->mediaTarget === 'vehicleImages') {
            if ($url !== '' && ! in_array($url, $this->vehicleImages, true)) {
                $this->vehicleImages[] = $url;
            }
            return;
        }

        if (str_starts_with($this->mediaTarget, 'settings.')) {
            data_set($this->settings, substr($this->mediaTarget, strlen('settings.')), $url);
        } else {
            $this->{$this->mediaTarget} = $url;
        }
    }

    private function storeMediaUpload($file): ?string
    {
        try {
            $disk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
            $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
            $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'media';
            $filename = $name . '_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(5)) . '.' . $ext;
            $dir = 'media/' . now()->format('Y/m');

            $path = $file->storeAs($dir, $filename, ['disk' => $disk, 'visibility' => 'public']);

            $publicUrl = $disk === 's3'
                ? rtrim((string) config('filesystems.disks.s3.endpoint'), '/') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path
                : \Illuminate\Support\Facades\Storage::disk($disk)->url($path);

            if (Schema::hasTable('files')) {
                \App\Models\File::create([
                    'folder_id' => null,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => $disk,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'metadata' => ['public_url' => $publicUrl],
                    'user_id' => auth()->id(),
                ]);
            }

            return $publicUrl;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('mediaUpload', 'Tải ảnh thất bại: ' . $e->getMessage());
            return null;
        }
    }

    private function mediaImages()
    {
        if (! $this->showMediaPicker || ! Schema::hasTable('files')) {
            return collect();
        }

        try {
            return \App\Models\File::query()
                ->where('mime_type', 'like', 'image/%')
                ->latest()
                ->limit(60)
                ->get(['id', 'name', 'metadata'])
                ->map(fn ($f) => ['name' => $f->name, 'url' => $f->metadata['public_url'] ?? ''])
                ->filter(fn ($m) => ! empty($m['url']))
                ->values();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    public function saveSettings()
    {
        $data = $this->validate([
            'settings.contact.site_name' => 'required|string|max:120',
            'settings.contact.hotline' => 'required|string|max:40',
            'settings.contact.zalo_phone' => 'required|string|max:40',
            'settings.contact.email' => 'nullable|email|max:160',
            'settings.contact.support_hours' => 'nullable|string|max:120',

            'settings.branding.logo' => 'nullable|string|max:2048',
            'settings.branding.logo_dark' => 'nullable|string|max:2048',
            'settings.branding.favicon' => 'nullable|string|max:2048',
            'settings.branding.tagline' => 'nullable|string|max:200',

            'settings.packages.free_daily_quota' => 'required|integer|min:0|max:1000',
            'settings.packages.tier_30_price' => 'required|integer|min:0|max:100000000',
            'settings.packages.tier_30_quota' => 'required|integer|min:0|max:1000',
            'settings.packages.tier_50_price' => 'required|integer|min:0|max:100000000',
            'settings.packages.tier_50_quota' => 'required|integer|min:0|max:1000',
            'settings.packages.online_payment_enabled' => 'boolean',

            'settings.watermark.enabled' => 'boolean',
            'settings.watermark.text' => 'nullable|string|max:60',
            'settings.watermark.position' => 'required|in:top-left,top-right,bottom-left,bottom-right,center',
            'settings.watermark.opacity' => 'required|integer|min:0|max:100',
            'settings.watermark.font_size' => 'required|integer|min:8|max:200',
            'settings.watermark.color' => 'required|string|max:9',
            'settings.watermark.margin' => 'required|integer|min:0|max:200',

            'settings.upload.max_size_mb' => 'required|integer|min:1|max:50',
            'settings.upload.max_count' => 'required|integer|min:1|max:60',
            'settings.upload.compress_quality' => 'required|integer|min:30|max:100',
            'settings.upload.max_dimension' => 'required|integer|min:480|max:8000',

            'settings.seo.default_title' => 'required|string|max:180',
            'settings.seo.title_template' => 'required|string|max:120',
            'settings.seo.default_description' => 'required|string|max:320',
            'settings.seo.keywords' => 'nullable|string|max:500',
            'settings.seo.og_image' => 'nullable|string|max:2048',
            'settings.seo.robots_index' => 'boolean',
            'settings.seo.canonical_base' => 'nullable|string|max:255',
            'settings.seo.google_site_verification' => 'nullable|string|max:255',
            'settings.seo.facebook_app_id' => 'nullable|string|max:64',
            'settings.seo.twitter_handle' => 'nullable|string|max:64',
            'settings.seo.analytics_id' => 'nullable|string|max:64',
        ]);

        // Cast booleans/ints that arrive as strings from the form.
        $data['settings']['packages']['online_payment_enabled'] = (bool) ($this->settings['packages']['online_payment_enabled'] ?? false);
        $data['settings']['watermark']['enabled'] = (bool) ($this->settings['watermark']['enabled'] ?? false);

        $setting = \App\Models\SiteSetting::current();
        $setting->forceFill([
            'value' => array_replace_recursive(config('site.defaults'), $data['settings']),
            'updated_by' => auth()->id(),
        ])->save();

        $this->settings = \App\Models\SiteSetting::values();
        session()->flash('message', 'Đã lưu cấu hình website.');
    }

    // ============================================================
    // ===================  QUẢN LÝ TIN XE (tab vehicles)  ========
    //  Mô phỏng y hệt tab "listings", chỉ khác field phục vụ xe.
    // ============================================================

    // Bộ lọc
    public $vehicleSearch = '';
    public $vehicleStatus = 'all';
    public $vehicleVip = 'all';
    public $vehicleKindFilter = 'all';

    // Form
    public $showVehicleModal = false;
    public $vehicleFormId = null;
    public $vehicleTitle = '';
    public $vehicleDealType = 'Cần bán';          // -> type
    public $vehicleKind = 'car';                  // -> vehicle_type
    public $vehicleBrand = '';
    public $vehicleModelName = '';
    public $vehicleYear = '';
    public $vehicleMileage = '';
    public $vehicleTransmission = '';
    public $vehicleFuelType = '';
    public $vehicleEngineCapacity = '';
    public $vehicleColor = '';
    public $vehicleSeats = '';
    public $vehicleCondition = 'used';
    public $vehicleOrigin = '';
    public $vehicleCode = '';
    public $vehicleContactType = '';
    public $vehicleContactPhone = '';
    public $vehicleState = 'active';
    public $vehicleTier = 'normal';
    public $vehicleProvinceId = '';
    public $vehicleDistrictId = '';
    public $vehicleWardId = '';
    public $vehicleAddress = '';
    public $vehiclePrice = '';
    public $vehiclePriceUnit = 'Triệu';
    public $vehicleDescription = '';
    public $vehicleYoutubeLink = '';
    public $vehicleImages = [];
    public $vehicleAvatar = '';
    public $vehicleDistricts = [];
    public $vehicleWards = [];

    public function updatedVehicleSearch() { $this->resetPage('vehiclesPage'); }
    public function updatedVehicleStatus() { $this->resetPage('vehiclesPage'); }
    public function updatedVehicleVip() { $this->resetPage('vehiclesPage'); }
    public function updatedVehicleKindFilter() { $this->resetPage('vehiclesPage'); }

    protected function fetchVehicleDistricts($provinceId): void
    {
        $data = $this->listingLocationData();
        $this->vehicleDistricts = [];
        if (! empty($data[$provinceId]['districts'])) {
            foreach ($data[$provinceId]['districts'] as $id => $district) {
                $this->vehicleDistricts[$id] = $district['name'];
            }
        }
    }

    protected function fetchVehicleWards($districtId): void
    {
        $data = $this->listingLocationData();
        $this->vehicleWards = [];
        $provinceId = $this->vehicleProvinceId;
        if (! empty($data[$provinceId]['districts'][$districtId]['wards'])) {
            $this->vehicleWards = $data[$provinceId]['districts'][$districtId]['wards'];
        }
    }

    public function updatedVehicleProvinceId($value): void
    {
        $this->vehicleDistricts = [];
        $this->vehicleWards = [];
        $this->vehicleDistrictId = '';
        $this->vehicleWardId = '';
        if ($value) {
            $this->fetchVehicleDistricts($value);
        }
    }

    public function updatedVehicleDistrictId($value): void
    {
        $this->vehicleWards = [];
        $this->vehicleWardId = '';
        if ($value) {
            $this->fetchVehicleWards($value);
        }
    }

    public function updatedVehicleKind($value): void
    {
        if (! $this->vehicleFormId && empty($this->vehicleCode)) {
            $this->vehicleCode = $this->generateVehicleCode($value);
        }
    }

    protected function generateVehicleCode($kind): string
    {
        $prefix = $kind === 'motorbike' ? 'XM' : 'OT';
        for ($i = 0; $i < 5; $i++) {
            $code = $prefix . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            if (! VehicleModel::where('code', $code)->exists()) {
                return $code;
            }
        }

        return $prefix . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function openCreateVehicle(): void
    {
        $this->resetVehicleForm();
        $this->showVehicleModal = true;
    }

    public function editVehicle($id): void
    {
        $v = VehicleModel::find($id);
        if (! $v) {
            return;
        }

        $this->vehicleFormId = $v->id;
        $this->vehicleTitle = $v->title ?: '';
        $this->vehicleDealType = $v->type ?: 'Cần bán';
        $this->vehicleKind = $v->vehicle_type ?: 'car';
        $this->vehicleBrand = $v->brand ?: '';
        $this->vehicleModelName = $v->model_name ?: '';
        $this->vehicleYear = $v->year ?? '';
        $this->vehicleMileage = $v->mileage ?? '';
        $this->vehicleTransmission = $v->transmission ?: '';
        $this->vehicleFuelType = $v->fuel_type ?: '';
        $this->vehicleEngineCapacity = $v->engine_capacity ?: '';
        $this->vehicleColor = $v->color ?: '';
        $this->vehicleSeats = $v->seats ?? '';
        $this->vehicleCondition = $v->condition ?: 'used';
        $this->vehicleOrigin = $v->origin ?: '';
        $this->vehicleCode = $v->code ?: '';
        $this->vehicleContactType = $v->contact_type ?: '';
        $this->vehicleContactPhone = $v->contact_phone ?: '';
        $this->vehicleState = $v->is_sold ? 'sold' : ($v->status ?: 'active');
        $this->vehicleTier = $v->vip_tier ?: 'normal';
        $this->vehicleProvinceId = $v->province_id ?: '';
        if ($this->vehicleProvinceId) {
            $this->fetchVehicleDistricts($this->vehicleProvinceId);
        }
        $this->vehicleDistrictId = $v->district_id ?: '';
        if ($this->vehicleDistrictId) {
            $this->fetchVehicleWards($this->vehicleDistrictId);
        }
        $this->vehicleWardId = $v->ward_id ?: '';
        $this->vehicleAddress = $v->address ?: '';
        $this->vehiclePrice = $v->price !== null ? $this->formatDecimalForInput($v->price) : '';
        $this->vehiclePriceUnit = $v->price_unit ?: 'Triệu';
        $this->vehicleDescription = $v->description ?: '';
        $this->vehicleYoutubeLink = $v->youtube_link ?: '';
        $this->vehicleImages = is_array($v->images) ? array_values($v->images) : [];
        $this->vehicleAvatar = $v->avatar ?: '';

        $this->resetValidation();
        $this->showVehicleModal = true;
    }

    public function saveVehicle(): void
    {
        $this->vehiclePrice = $this->normalizeListingCurrency($this->vehiclePrice);
        foreach (['vehicleYear', 'vehicleMileage', 'vehicleSeats'] as $field) {
            $this->{$field} = ($this->{$field} === '' || $this->{$field} === null)
                ? null
                : (int) preg_replace('/[^0-9]/', '', (string) $this->{$field});
        }

        $rules = [
            'vehicleTitle' => 'required|string|max:255',
            'vehicleKind' => 'required|in:car,motorbike',
            'vehicleState' => 'required|in:pending,active,expired,rejected,sold',
            'vehicleTier' => 'required|in:normal,vip1,vip2,vip3',
            'vehicleYoutubeLink' => 'nullable|url|max:2000',
        ];
        if ($this->vehicleDealType !== 'Cần mua') {
            $rules['vehiclePrice'] = 'required|numeric';
        }

        $this->validate($rules, [], [
            'vehicleTitle' => 'tiêu đề',
            'vehiclePrice' => 'giá',
        ]);

        if (! $this->vehicleFormId && empty($this->vehicleCode)) {
            $this->vehicleCode = $this->generateVehicleCode($this->vehicleKind);
        }

        $isSold = $this->vehicleState === 'sold';

        $data = [
            'title' => $this->vehicleTitle,
            'type' => $this->vehicleDealType,
            'vehicle_type' => $this->vehicleKind,
            'brand' => $this->vehicleBrand ?: null,
            'model_name' => $this->vehicleModelName ?: null,
            'year' => $this->vehicleYear,
            'mileage' => $this->vehicleMileage,
            'transmission' => $this->vehicleTransmission ?: null,
            'fuel_type' => $this->vehicleFuelType ?: null,
            'engine_capacity' => $this->vehicleEngineCapacity ?: null,
            'color' => $this->vehicleColor ?: null,
            'seats' => $this->vehicleKind === 'car' ? $this->vehicleSeats : null,
            'condition' => $this->vehicleCondition ?: null,
            'origin' => $this->vehicleOrigin ?: null,
            'contact_type' => $this->vehicleContactType ?: null,
            'contact_phone' => $this->vehicleContactPhone ?: null,
            'code' => $this->vehicleCode,
            'status' => $this->vehicleState,
            'is_sold' => $isSold,
            'vip_tier' => $this->vehicleTier,
            'province_id' => $this->vehicleProvinceId ?: null,
            'district_id' => $this->vehicleDistrictId ?: null,
            'ward_id' => $this->vehicleWardId ?: null,
            'province_name' => \App\Livewire\RealEstateListing::PROVINCES[$this->vehicleProvinceId] ?? null,
            'district_name' => $this->vehicleDistricts[$this->vehicleDistrictId] ?? null,
            'ward_name' => $this->vehicleWards[$this->vehicleWardId] ?? null,
            'address' => $this->vehicleAddress ?: null,
            'price' => $this->vehiclePrice,
            'price_unit' => $this->vehiclePriceUnit,
            'description' => $this->vehicleDescription ?: null,
            'youtube_link' => $this->vehicleYoutubeLink ?: null,
            'images' => array_values($this->vehicleImages ?: []),
            'avatar' => $this->vehicleAvatar ?: null,
        ];

        try {
            if ($this->vehicleFormId) {
                $v = VehicleModel::findOrFail($this->vehicleFormId);
                $v->update($data);
                $message = 'Đã cập nhật tin xe.';
            } else {
                $data['user_id'] = auth()->id();
                $data['published_at'] = now();
                $data['expires_at'] = now()->addDays(60);
                $v = VehicleModel::create($data);
                $message = 'Đã thêm tin xe mới.';
            }

            $v->slug = Str::slug($v->title) . '-' . $v->id;
            $v->save();

            session()->flash('message', $message);
            $this->closeVehicleModal();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('vehicleTitle', 'Lưu tin xe thất bại: ' . $e->getMessage());
        }
    }

    public function removeVehicleImage($index): void
    {
        if (isset($this->vehicleImages[$index])) {
            array_splice($this->vehicleImages, $index, 1);
        }
    }

    public function setVehicleAvatarFromImage($index): void
    {
        if (isset($this->vehicleImages[$index])) {
            $this->vehicleAvatar = $this->vehicleImages[$index];
        }
    }

    public function closeVehicleModal(): void
    {
        $this->showVehicleModal = false;
        $this->resetVehicleForm();
    }

    protected function resetVehicleForm(): void
    {
        $this->vehicleFormId = null;
        $this->vehicleTitle = '';
        $this->vehicleDealType = 'Cần bán';
        $this->vehicleKind = 'car';
        $this->vehicleBrand = '';
        $this->vehicleModelName = '';
        $this->vehicleYear = '';
        $this->vehicleMileage = '';
        $this->vehicleTransmission = '';
        $this->vehicleFuelType = '';
        $this->vehicleEngineCapacity = '';
        $this->vehicleColor = '';
        $this->vehicleSeats = '';
        $this->vehicleCondition = 'used';
        $this->vehicleOrigin = '';
        $this->vehicleCode = '';
        $this->vehicleContactType = '';
        $this->vehicleContactPhone = '';
        $this->vehicleState = 'active';
        $this->vehicleTier = 'normal';
        $this->vehicleProvinceId = '';
        $this->vehicleDistrictId = '';
        $this->vehicleWardId = '';
        $this->vehicleAddress = '';
        $this->vehiclePrice = '';
        $this->vehiclePriceUnit = 'Triệu';
        $this->vehicleDescription = '';
        $this->vehicleYoutubeLink = '';
        $this->vehicleImages = [];
        $this->vehicleAvatar = '';
        $this->vehicleDistricts = [];
        $this->vehicleWards = [];
        $this->resetValidation();
    }

    public function updateVehicleStatus($id, $status)
    {
        if (! in_array($status, ['active', 'pending', 'expired', 'sold', 'rejected'], true)) {
            return;
        }
        $v = VehicleModel::findOrFail($id);
        $v->status = $status;
        $v->is_sold = $status === 'sold';
        $v->save();
    }

    public function updateVehicleVip($id, $vip)
    {
        if (! in_array($vip, ['normal', 'vip1', 'vip2', 'vip3'], true)) {
            return;
        }
        VehicleModel::where('id', $id)->update(['vip_tier' => $vip]);
    }

    public function deleteVehicle($id)
    {
        VehicleModel::where('id', $id)->delete();
        session()->flash('message', 'Đã xóa tin xe.');
    }

    private function vehicles()
    {
        try {
            return VehicleModel::query()
                ->with('user:id,name,phone')
                ->when($this->vehicleSearch, function ($query) {
                    $query->where(function ($q) {
                        $q->where('title', 'like', '%' . $this->vehicleSearch . '%')
                            ->orWhere('code', 'like', '%' . $this->vehicleSearch . '%')
                            ->orWhere('brand', 'like', '%' . $this->vehicleSearch . '%')
                            ->orWhere('model_name', 'like', '%' . $this->vehicleSearch . '%')
                            ->orWhere('contact_phone', 'like', '%' . $this->vehicleSearch . '%');
                    });
                })
                ->when($this->vehicleStatus !== 'all', function ($query) {
                    if ($this->vehicleStatus === 'sold') {
                        $query->where('is_sold', true);
                    } else {
                        $query->where('status', $this->vehicleStatus);
                    }
                })
                ->when($this->vehicleVip !== 'all', function ($query) {
                    $query->where('vip_tier', $this->vehicleVip);
                })
                ->when($this->vehicleKindFilter !== 'all', function ($query) {
                    $query->where('vehicle_type', $this->vehicleKindFilter);
                })
                ->latest()
                ->paginate(10, ['*'], 'vehiclesPage');
        } catch (\Throwable $e) {
            return $this->emptyPaginator('vehiclesPage');
        }
    }

    // ===================  HÃNG XE (tab vehicle-brands)  ========
    public $showBrandModal = false;
    public $brandFormId = null;
    public $brandName = '';
    public $brandVehicleType = 'both';
    public $brandSortOrder = 0;
    public $brandActive = true;
    public $brandSearch = '';

    public function openCreateBrand(): void
    {
        $this->resetBrandForm();
        $this->showBrandModal = true;
    }

    public function editBrand($id): void
    {
        $b = VehicleBrand::find($id);
        if (! $b) {
            return;
        }
        $this->brandFormId = $b->id;
        $this->brandName = $b->name;
        $this->brandVehicleType = $b->vehicle_type ?: 'both';
        $this->brandSortOrder = (int) $b->sort_order;
        $this->brandActive = (bool) $b->is_active;
        $this->showBrandModal = true;
    }

    public function saveBrand(): void
    {
        $data = $this->validate([
            'brandName' => 'required|string|max:120',
            'brandVehicleType' => 'required|in:car,motorbike,both',
            'brandSortOrder' => 'nullable|integer|min:0|max:9999',
        ], [], [
            'brandName' => 'tên hãng',
            'brandVehicleType' => 'loại xe',
        ]);

        VehicleBrand::updateOrCreate(
            ['id' => $this->brandFormId],
            [
                'name' => $data['brandName'],
                'vehicle_type' => $data['brandVehicleType'],
                'sort_order' => (int) ($data['brandSortOrder'] ?? 0),
                'is_active' => (bool) $this->brandActive,
            ]
        );

        session()->flash('message', 'Đã lưu hãng xe.');
        $this->closeBrandModal();
    }

    public function deleteBrand($id): void
    {
        VehicleBrand::where('id', $id)->delete();
        session()->flash('message', 'Đã xóa hãng xe.');
    }

    public function toggleBrandActive($id): void
    {
        $b = VehicleBrand::find($id);
        if ($b) {
            $b->is_active = ! $b->is_active;
            $b->save();
        }
    }

    public function closeBrandModal(): void
    {
        $this->showBrandModal = false;
        $this->resetBrandForm();
    }

    protected function resetBrandForm(): void
    {
        $this->brandFormId = null;
        $this->brandName = '';
        $this->brandVehicleType = 'both';
        $this->brandSortOrder = 0;
        $this->brandActive = true;
        $this->resetValidation();
    }

    private function vehicleBrandsList()
    {
        if (! Schema::hasTable('vehicle_brands')) {
            return $this->emptyPaginator('brandsPage');
        }

        try {
            return VehicleBrand::query()
                ->when($this->brandSearch, fn ($q) => $q->where('name', 'like', '%' . $this->brandSearch . '%'))
                ->orderBy('vehicle_type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15, ['*'], 'brandsPage');
        } catch (\Throwable $e) {
            return $this->emptyPaginator('brandsPage');
        }
    }

    public function render()
    {
        return view('livewire.website-admin', [
            'stats' => $this->stats(),
            'vehicles' => $this->vehicles(),
            'vehicleBrands' => $this->vehicleBrandsList(),
            'recentListings' => $this->recentListings(),
            'recentLeads' => $this->recentLeads(),
            'homeSections' => $this->homeSections(),
            'listings' => $this->listings(),
            'categories' => $this->categories(),
            'categoryOptions' => $this->categoryOptions(),
            'provinceOptions' => $this->provinceOptions(),
            'blogs' => $this->blogs(),
            'leads' => $this->leads(),
            'reports' => $this->reports(),
            'selectedReport' => $this->selectedReport(),
            'accounts' => $this->accounts(),
            'accountInviters' => $this->accountInviters(),
            'selectedAccount' => $this->selectedAccount(),
            'selectedAccountStats' => $this->selectedAccountStats(),
            'selectedAccountTransactions' => $this->selectedAccountTransactions(),
            'selectedAccountReferrals' => $this->selectedAccountReferrals(),
            'selectedAccountListings' => $this->selectedAccountListings(),
            'propertyTypeOptions' => $this->propertyTypeOptions(),
            'favorites' => $this->favorites(),
            'savedSearches' => $this->savedSearches(),
            'topViewedListings' => $this->topViewedListings(),
            'dailyViews' => $this->dailyViews(),
            'mediaImages' => $this->mediaImages(),
        ])->layout('components.layouts.website-cms', [
            'title' => 'Quản trị website BĐS',
            'stats' => $this->stats(),
        ]);
    }

    public function createCategory()
    {
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
    }

    public function editCategory($id)
    {
        if (! Schema::hasTable('listing_categories')) {
            return;
        }

        $category = ListingCategory::findOrFail($id);
        $this->categoryEditing = true;
        $this->categoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categorySlug = $category->slug;
        $this->categoryTransactionType = $category->transaction_type;
        $this->categoryPropertyType = $category->property_type ?: '';
        $this->categoryIcon = $category->icon ?: '';
        $this->categorySortOrder = (int) $category->sort_order;
        $this->showCategoryModal = true;
    }

    public function saveCategory()
    {
        $data = $this->validate([
            'categoryId' => 'required|string|max:80|regex:/^[a-z0-9_-]+$/',
            'categoryName' => 'required|string|max:160',
            'categorySlug' => 'nullable|string|max:160',
            'categoryTransactionType' => 'required|in:rent,sale,both',
            'categoryPropertyType' => 'nullable|string|max:80',
            'categoryIcon' => 'nullable|string|max:80',
            'categorySortOrder' => 'nullable|integer|min:0|max:9999',
        ]);

        $slug = $data['categorySlug'] ?: Str::slug($data['categoryName']);

        ListingCategory::updateOrCreate(
            ['id' => $data['categoryId']],
            [
                'name' => $data['categoryName'],
                'slug' => $slug,
                'transaction_type' => $data['categoryTransactionType'],
                'property_type' => $data['categoryPropertyType'] ?: null,
                'icon' => $data['categoryIcon'] ?: null,
                'sort_order' => (int) $data['categorySortOrder'],
            ]
        );

        session()->flash('message', 'Đã lưu danh mục website.');
        $this->closeCategoryModal();
    }

    public function deleteCategory($id)
    {
        if (Schema::hasTable('listing_categories')) {
            ListingCategory::where('id', $id)->delete();
            session()->flash('message', 'Đã xóa danh mục website.');
        }
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    public function createBlog()
    {
        $this->resetBlogForm();
        $this->blogPublishedAt = now()->format('Y-m-d\TH:i');
        $this->showBlogModal = true;
    }

    public function editBlog($id)
    {
        if (! Schema::hasTable('blog_posts')) {
            return;
        }

        $post = BlogPost::findOrFail($id);
        $this->blogEditingId = $post->id;
        $this->blogTitle = $post->title;
        $this->blogSlug = $post->slug;
        $this->blogExcerpt = $post->excerpt ?: '';
        $this->blogContent = $post->content;
        $this->blogCoverImage = $post->cover_image ?: '';
        $this->blogAuthorName = $post->author_name ?: 'BDS Việt';
        $this->blogCategoryTag = $post->category_tag ?: 'Tin tức';
        $this->blogTags = implode(', ', $post->tags ?: []);
        $this->blogReadingMinutes = (int) $post->reading_minutes;
        $this->blogStatusValue = $post->status ?: 'published';
        $this->blogPublishedAt = optional($post->published_at)->format('Y-m-d\TH:i') ?: '';
        $this->showBlogModal = true;
    }

    public function saveBlog()
    {
        $data = $this->validate([
            'blogTitle' => 'required|string|max:220',
            'blogSlug' => 'nullable|string|max:220',
            'blogExcerpt' => 'nullable|string|max:1000',
            'blogContent' => 'required|string|min:20',
            'blogCoverImage' => 'nullable|string|max:2048',
            'blogAuthorName' => 'nullable|string|max:120',
            'blogCategoryTag' => 'nullable|string|max:120',
            'blogTags' => 'nullable|string|max:500',
            'blogReadingMinutes' => 'required|integer|min:1|max:60',
            'blogStatusValue' => 'required|in:draft,published,archived',
            'blogPublishedAt' => 'nullable|date',
        ]);

        $slug = $data['blogSlug'] ?: Str::slug($data['blogTitle']);
        $tags = collect(explode(',', $data['blogTags'] ?: ''))
            ->map(function ($tag) {
                return trim($tag);
            })
            ->filter()
            ->values()
            ->all();

        BlogPost::updateOrCreate(
            ['id' => $this->blogEditingId],
            [
                'title' => $data['blogTitle'],
                'slug' => $slug,
                'excerpt' => $data['blogExcerpt'] ?: null,
                'content' => $data['blogContent'],
                'cover_image' => $data['blogCoverImage'] ?: null,
                'author_name' => $data['blogAuthorName'] ?: 'BDS Việt',
                'category_tag' => $data['blogCategoryTag'] ?: 'Tin tức',
                'tags' => $tags,
                'reading_minutes' => (int) $data['blogReadingMinutes'],
                'status' => $data['blogStatusValue'],
                'published_at' => $data['blogPublishedAt'] ?: null,
            ]
        );

        session()->flash('message', 'Đã lưu bài blog website.');
        $this->closeBlogModal();
    }

    public function deleteBlog($id)
    {
        if (Schema::hasTable('blog_posts')) {
            BlogPost::where('id', $id)->delete();
            session()->flash('message', 'Đã xóa bài blog.');
        }
    }

    public function toggleBlogStatus($id)
    {
        if (! Schema::hasTable('blog_posts')) {
            return;
        }

        $post = BlogPost::findOrFail($id);
        $post->update([
            'status' => $post->status === 'published' ? 'draft' : 'published',
            'published_at' => $post->published_at ?: now(),
        ]);
    }

    public function closeBlogModal()
    {
        $this->showBlogModal = false;
        $this->resetBlogForm();
    }

    public function updateListingStatus($id, $status)
    {
        if (! in_array($status, ['active', 'pending', 'expired', 'sold', 'rejected'], true)) {
            return;
        }

        $listing = RealEstateListing::findOrFail($id);
        $listing->status = $status;
        $listing->is_sold = $status === 'sold';
        if ($status !== 'rejected') {
            $listing->rejection_reason = null;
        }
        $listing->save();
    }

    public function updateListingVip($id, $vip)
    {
        if (! in_array($vip, ['normal', 'vip1', 'vip2', 'vip3'], true)) {
            return;
        }

        RealEstateListing::where('id', $id)->update(['vip_tier' => $vip]);
    }

    public function editHomeSection($id)
    {
        if (! Schema::hasTable('website_home_sections')) {
            return;
        }

        $section = WebsiteHomeSection::findOrFail($id);
        $this->homeSectionEditingId = $section->id;
        $this->homeSectionKey = $section->key;
        $this->homeSectionTitle = $section->title;
        $this->homeSectionDescription = $section->description ?: '';
        $this->homeSectionType = $section->section_type;
        $this->homeSectionEnabled = (bool) $section->enabled;
        $this->homeSectionSourceType = $section->source_type;
        $this->homeSectionTransactionType = $section->transaction_type ?: '';
        $this->homeSectionPropertyKind = $section->property_kind ?: '';
        $this->homeSectionCategoryId = $section->category_id ?: '';
        $this->homeSectionProvinceName = $section->province_name ?: '';
        $this->homeSectionSortBy = $section->sort_by ?: 'created_at';
        $this->homeSectionSortOrder = $section->sort_order ?: 'desc';
        $this->homeSectionLimit = (int) $section->limit;
        $this->homeSectionHref = $section->href ?: '';
        $this->homeSectionManualIds = implode(',', $section->manual_listing_ids ?: []);
        $this->homeSectionSortOrderIndex = (int) $section->sort_order_index;
        $this->showHomeSectionModal = true;
    }

    public function saveHomeSection()
    {
        if (! Schema::hasTable('website_home_sections') || ! $this->homeSectionEditingId) {
            return;
        }

        $data = $this->validate([
            'homeSectionTitle' => 'required|string|max:180',
            'homeSectionDescription' => 'nullable|string|max:500',
            'homeSectionType' => 'required|in:listings,regions,tools,recently_viewed,blogs,feature_descriptions,promo',
            'homeSectionEnabled' => 'boolean',
            'homeSectionSourceType' => 'required|in:latest,vip,property,category,province,manual,regions,static,client',
            'homeSectionTransactionType' => 'nullable|in:,sale,rent',
            'homeSectionPropertyKind' => 'nullable|in:,apartment,room,house,office,land,shared',
            'homeSectionCategoryId' => 'nullable|string|max:80',
            'homeSectionProvinceName' => 'nullable|string|max:120',
            'homeSectionSortBy' => 'required|in:created_at,price,area,view_count',
            'homeSectionSortOrder' => 'required|in:asc,desc',
            'homeSectionLimit' => 'required|integer|min:0|max:24',
            'homeSectionHref' => 'nullable|string|max:255',
            'homeSectionManualIds' => 'nullable|string|max:1000',
            'homeSectionSortOrderIndex' => 'required|integer|min:0|max:9999',
        ]);

        $manualIds = collect(explode(',', $data['homeSectionManualIds'] ?: ''))
            ->map(fn ($id) => trim($id))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $section = WebsiteHomeSection::findOrFail($this->homeSectionEditingId);
        $section->fill([
            'title' => $data['homeSectionTitle'],
            'description' => $data['homeSectionDescription'] ?: null,
            'section_type' => $data['homeSectionType'],
            'enabled' => (bool) $data['homeSectionEnabled'],
            'source_type' => $data['homeSectionSourceType'],
            'transaction_type' => $data['homeSectionTransactionType'] ?: null,
            'property_kind' => $data['homeSectionPropertyKind'] ?: null,
            'category_id' => $data['homeSectionCategoryId'] ?: null,
            'province_name' => $data['homeSectionProvinceName'] ?: null,
            'sort_by' => $data['homeSectionSortBy'],
            'sort_order' => $data['homeSectionSortOrder'],
            'limit' => (int) $data['homeSectionLimit'],
            'href' => $data['homeSectionHref'] ?: null,
            'manual_listing_ids' => $manualIds,
            'sort_order_index' => (int) $data['homeSectionSortOrderIndex'],
        ]);
        $section->save();

        session()->flash('message', 'Da cap nhat khoi hien thi trang user.');
        $this->closeHomeSectionModal();
    }

    public function toggleHomeSection($id)
    {
        if (! Schema::hasTable('website_home_sections')) {
            return;
        }

        $section = WebsiteHomeSection::findOrFail($id);
        $section->update(['enabled' => ! $section->enabled]);
    }

    public function closeHomeSectionModal()
    {
        $this->showHomeSectionModal = false;
        $this->homeSectionEditingId = null;
        $this->homeSectionKey = '';
    }

    public function deleteListing($id)
    {
        RealEstateListing::where('id', $id)->delete();
        session()->flash('message', 'Đã xóa tin website.');
    }

    public function openLead($id)
    {
        if (! Schema::hasTable('listing_contact_requests')) {
            return;
        }

        $lead = ListingContactRequest::findOrFail($id);
        $this->leadEditingId = $lead->id;
        $this->leadName = $lead->name;
        $this->leadPhone = $lead->phone;
        $this->leadMessage = $lead->message ?: '';
        $this->leadStatusValue = $lead->status ?: 'new';
        $this->leadAdminNote = $lead->admin_note ?: '';
        $this->showLeadModal = true;
    }

    public function saveLead()
    {
        $data = $this->validate([
            'leadStatusValue' => 'required|in:new,contacted,qualified,closed,spam',
            'leadAdminNote' => 'nullable|string|max:2000',
        ]);

        if (! $this->leadEditingId || ! Schema::hasTable('listing_contact_requests')) {
            return;
        }

        ListingContactRequest::where('id', $this->leadEditingId)->update([
            'status' => $data['leadStatusValue'],
            'admin_note' => $data['leadAdminNote'] ?: null,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        session()->flash('message', 'Đã cập nhật lead website.');
        $this->closeLeadModal();
    }

    public function quickLeadStatus($id, $status)
    {
        if (! in_array($status, ['new', 'contacted', 'qualified', 'closed', 'spam'], true) || ! Schema::hasTable('listing_contact_requests')) {
            return;
        }

        ListingContactRequest::where('id', $id)->update([
            'status' => $status,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);
    }

    public function deleteLead($id)
    {
        if (Schema::hasTable('listing_contact_requests')) {
            ListingContactRequest::where('id', $id)->delete();
            session()->flash('message', 'Đã xóa lead website.');
        }
    }

    public function closeLeadModal()
    {
        $this->showLeadModal = false;
        $this->leadEditingId = null;
        $this->leadName = '';
        $this->leadPhone = '';
        $this->leadMessage = '';
        $this->leadStatusValue = 'new';
        $this->leadAdminNote = '';
    }

    public function selectAccount($id)
    {
        $this->selectedAccountId = $id;
    }

    public function createAccount()
    {
        $this->resetAccountForm();
        $this->showAccountModal = true;
    }

    public function editAccount($id)
    {
        $user = User::findOrFail($id);
        $this->accountEditingId = $user->id;
        $this->accountName = $user->name ?: '';
        $this->accountPhone = $user->phone ?: '';
        $this->accountRoleValue = $user->role ?: 'buyer';
        $this->accountPropertyTypes = $user->property_types ?: [];
        $this->accountInviterUserId = $user->invited_by_user_id ?: '';
        $this->accountExistingInviteCode = $user->invite_code ?: '';
        $this->accountRootInviteCode = $user->invite_code ?: '';
        $this->accountViewPhonePin = $user->view_phone_pin ?: '';
        $this->showAccountModal = true;
    }

    public function saveAccount()
    {
        $this->accountRootInviteCode = Str::upper(trim((string) $this->accountRootInviteCode));
        if ($this->accountRootInviteCode === '') {
            $this->accountRootInviteCode = null;
        }

        $data = $this->validate([
            'accountName' => 'required|string|min:3|max:255',
            'accountPhone' => [
                'required',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                Rule::unique('users', 'phone')->ignore($this->accountEditingId),
            ],
            'accountRoleValue' => 'required|in:admin,ctv,buyer',
            'accountPropertyTypes' => 'nullable|array',
            'accountInviterUserId' => 'nullable|exists:users,id',
            'accountRootInviteCode' => [
                Rule::requiredIf(fn () => blank($this->accountInviterUserId) && blank($this->accountExistingInviteCode)),
                'nullable',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('users', 'invite_code')->ignore($this->accountEditingId),
            ],
            'accountViewPhonePin' => 'nullable|string|max:10',
        ]);

        $inviter = null;
        if (! blank($data['accountInviterUserId'])) {
            if ($this->accountEditingId && (int) $data['accountInviterUserId'] === (int) $this->accountEditingId) {
                $this->addError('accountInviterUserId', 'Không thể chọn chính tài khoản này làm người mời.');
                return;
            }

            $inviter = User::select('id', 'invite_code')->find($data['accountInviterUserId']);
            if (! $inviter || blank($inviter->invite_code)) {
                $this->addError('accountInviterUserId', 'Người mời được chọn chưa có mã mời hợp lệ.');
                return;
            }
        }

        DB::transaction(function () use ($data, $inviter) {
            if ($this->accountEditingId) {
                $user = User::findOrFail($this->accountEditingId);
                $oldInviterId = $user->invited_by_user_id;
                $updates = [
                    'name' => $data['accountName'],
                    'phone' => $data['accountPhone'],
                    'role' => $data['accountRoleValue'],
                    'property_types' => $data['accountPropertyTypes'] ?: [],
                    'invited_by_user_id' => $inviter?->id,
                    'view_phone_pin' => $data['accountViewPhonePin'] ?: null,
                ];

                if (blank($user->invite_code)) {
                    $updates['invite_code'] = $inviter ? ($inviter->invite_code . $user->id) : $this->accountRootInviteCode;
                }

                $user->update($updates);

                if ($inviter && $oldInviterId !== $inviter->id && Schema::hasTable('user_invites')) {
                    UserInvite::create([
                        'inviter_user_id' => $inviter->id,
                        'invited_user_id' => $user->id,
                        'inviter_code' => $inviter->invite_code,
                    ]);
                }

                $this->selectedAccountId = $user->id;
            } else {
                $user = User::create([
                    'name' => $data['accountName'],
                    'phone' => $data['accountPhone'],
                    'role' => $data['accountRoleValue'],
                    'password' => bcrypt(Str::random(16)),
                    'property_types' => $data['accountPropertyTypes'] ?: [],
                    'invited_by_user_id' => $inviter?->id,
                    'view_phone_pin' => $data['accountViewPhonePin'] ?: null,
                ]);

                $user->update([
                    'invite_code' => $inviter ? ($inviter->invite_code . $user->id) : $this->accountRootInviteCode,
                ]);

                if ($inviter && Schema::hasTable('user_invites')) {
                    UserInvite::create([
                        'inviter_user_id' => $inviter->id,
                        'invited_user_id' => $user->id,
                        'inviter_code' => $inviter->invite_code,
                    ]);
                }

                $this->selectedAccountId = $user->id;
            }
        });

        session()->flash('message', 'Đã lưu tài khoản người dùng.');
        $this->closeAccountModal();
    }

    public function confirmDeleteAccount($id)
    {
        $this->accountEditingId = $id;
        $this->showAccountDeleteModal = true;
    }

    public function deleteAccount()
    {
        if ($this->accountEditingId) {
            User::where('id', $this->accountEditingId)->delete();
            if ((int) $this->selectedAccountId === (int) $this->accountEditingId) {
                $this->selectedAccountId = null;
            }
            session()->flash('message', 'Đã xóa tài khoản người dùng.');
        }

        $this->showAccountDeleteModal = false;
        $this->accountEditingId = null;
    }

    public function closeAccountModal()
    {
        $this->showAccountModal = false;
        $this->resetAccountForm();
    }

    public function closeAccountDeleteModal()
    {
        $this->showAccountDeleteModal = false;
        $this->accountEditingId = null;
    }

    private function resetCategoryForm()
    {
        $this->categoryEditing = false;
        $this->categoryId = '';
        $this->categoryName = '';
        $this->categorySlug = '';
        $this->categoryTransactionType = 'both';
        $this->categoryPropertyType = '';
        $this->categoryIcon = '';
        $this->categorySortOrder = 0;
    }

    private function resetBlogForm()
    {
        $this->blogEditingId = null;
        $this->blogTitle = '';
        $this->blogSlug = '';
        $this->blogExcerpt = '';
        $this->blogContent = '';
        $this->blogCoverImage = '';
        $this->blogAuthorName = 'BDS Việt';
        $this->blogCategoryTag = 'Tin tức';
        $this->blogTags = '';
        $this->blogReadingMinutes = 5;
        $this->blogStatusValue = 'published';
        $this->blogPublishedAt = '';
    }

    private function resetAccountForm()
    {
        $this->accountEditingId = null;
        $this->accountName = '';
        $this->accountPhone = '';
        $this->accountRoleValue = 'buyer';
        $this->accountPropertyTypes = [];
        $this->accountInviterUserId = '';
        $this->accountRootInviteCode = '';
        $this->accountExistingInviteCode = '';
        $this->accountViewPhonePin = '';
        $this->resetValidation();
    }

    private function stats()
    {
        return [
            'public_listings' => $this->countListings(),
            'pending_listings' => $this->countListingStatus('pending'),
            'vehicles' => $this->countTable('vehicle_listings'),
            'pending_vehicles' => $this->countVehicleStatus('pending'),
            'categories' => $this->countTable('listing_categories'),
            'blogs' => $this->countTable('blog_posts'),
            'leads' => $this->countTable('listing_contact_requests'),
            'open_leads' => $this->countLeadStatus('new'),
            'accounts' => $this->countTable('users'),
            'open_reports' => $this->countReportStatus('pending'),
            'favorites' => $this->countTable('listing_favorites'),
            'saved_searches' => $this->countTable('saved_searches'),
            'views' => $this->countTable('listing_view_events'),
        ];
    }

    private function countListings()
    {
        try {
            return RealEstateListing::query()
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'active');
                })
                ->where('is_sold', false)
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countListingStatus($status)
    {
        if (! Schema::hasColumn('real_estate_listings', 'status')) {
            return 0;
        }

        try {
            return RealEstateListing::where('status', $status)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countVehicleStatus($status)
    {
        if (! Schema::hasTable('vehicle_listings')) {
            return 0;
        }

        try {
            return VehicleModel::where('status', $status)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countLeadStatus($status)
    {
        if (! Schema::hasTable('listing_contact_requests') || ! Schema::hasColumn('listing_contact_requests', 'status')) {
            return 0;
        }

        try {
            return ListingContactRequest::where('status', $status)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countTable($table)
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        try {
            return DB::table($table)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function homeSectionCount($section): int
    {
        try {
            if (! $section || $section->section_type !== 'listings') {
                return 0;
            }

            return $this->homeSectionListingQuery($section)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function homeSections()
    {
        if (! Schema::hasTable('website_home_sections')) {
            return collect();
        }

        return WebsiteHomeSection::query()
            ->orderBy('sort_order_index')
            ->orderBy('id')
            ->get();
    }

    private function homeSectionListingQuery($section)
    {
        $query = RealEstateListing::query()
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            });

        if ($section->source_type === 'manual') {
            $ids = collect($section->manual_listing_ids ?: [])->map(fn ($id) => (int) $id)->filter()->values()->all();
            if ($ids) {
                return $query->whereIn('id', $ids);
            }
        }

        if ($section->source_type === 'vip') {
            $query->where('vip_tier', '<>', 'normal');
        }

        if ($section->source_type === 'property' && $section->property_kind) {
            $codes = match ($section->property_kind) {
                'apartment' => [103],
                'room' => [115],
                'land' => [104, 105, 109],
                'office' => [106, 107, 111, 112, 113],
                'house' => [102, 108, 114],
                default => [],
            };
            if ($codes) {
                $query->whereIn('property_type', $codes);
            }
        }

        if ($section->source_type === 'category' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        }

        if ($section->source_type === 'province' && $section->province_name) {
            $province = $section->province_name;
            $query->where(function ($q) use ($province) {
                $q->where('province_id', $province)->orWhere('province_name', 'like', '%' . $province . '%');
            });
        }

        if ($section->transaction_type === 'sale') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%ban%')->orWhere('type', 'like', '%bán%')->orWhere('type', 'like', '%Cần bán%');
            });
        }
        if ($section->transaction_type === 'rent') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%thue%')->orWhere('type', 'like', '%thuê%')->orWhere('type', 'like', '%Cho thuê%');
            });
        }

        return $query;
    }

    private function listings()
    {
        try {
            $query = RealEstateListing::query()
                ->with('user:id,name,phone')
                ->when($this->listingSearch, function ($query) {
                    $query->where(function ($q) {
                        $q->where('title', 'like', '%' . $this->listingSearch . '%')
                            ->orWhere('code', 'like', '%' . $this->listingSearch . '%')
                            ->orWhere('contact_phone', 'like', '%' . $this->listingSearch . '%');
                    });
                })
                ->when($this->listingStatus !== 'all', function ($query) {
                    if ($this->listingStatus === 'sold') {
                        $query->where('is_sold', true);
                    } else {
                        $query->where('status', $this->listingStatus);
                    }
                })
                ->when($this->listingVip !== 'all', function ($query) {
                    $query->where('vip_tier', $this->listingVip);
                })
                ->latest();

            return $query->paginate(10, ['*'], 'listingsPage');
        } catch (\Throwable $e) {
            return $this->emptyPaginator('listingsPage');
        }
    }

    private function categories()
    {
        if (! Schema::hasTable('listing_categories')) {
            return $this->emptyPaginator('categoriesPage');
        }

        return ListingCategory::query()
            ->when($this->categorySearch, function ($query) {
                $query->where('name', 'like', '%' . $this->categorySearch . '%')
                    ->orWhere('slug', 'like', '%' . $this->categorySearch . '%')
                    ->orWhere('id', 'like', '%' . $this->categorySearch . '%');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10, ['*'], 'categoriesPage');
    }

    private function categoryOptions()
    {
        if (! Schema::hasTable('listing_categories')) {
            return collect();
        }

        try {
            return ListingCategory::query()->orderBy('name')->get(['id', 'name']);
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function provinceOptions()
    {
        if (! Schema::hasColumn('real_estate_listings', 'province_name')) {
            return collect();
        }

        try {
            return RealEstateListing::query()
                ->whereNotNull('province_name')
                ->where('province_name', '<>', '')
                ->distinct()
                ->orderBy('province_name')
                ->limit(100)
                ->pluck('province_name');
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function blogs()
    {
        if (! Schema::hasTable('blog_posts')) {
            return $this->emptyPaginator('blogsPage');
        }

        return BlogPost::query()
            ->when($this->blogSearch, function ($query) {
                $query->where('title', 'like', '%' . $this->blogSearch . '%')
                    ->orWhere('slug', 'like', '%' . $this->blogSearch . '%')
                    ->orWhere('category_tag', 'like', '%' . $this->blogSearch . '%');
            })
            ->when($this->blogStatus !== 'all', function ($query) {
                $query->where('status', $this->blogStatus);
            })
            ->latest()
            ->paginate(10, ['*'], 'blogsPage');
    }

    private function leads()
    {
        if (! Schema::hasTable('listing_contact_requests')) {
            return $this->emptyPaginator('leadsPage');
        }

        return ListingContactRequest::query()
            ->when($this->leadSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->leadSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->leadSearch . '%')
                    ->orWhere('message', 'like', '%' . $this->leadSearch . '%');
            })
            ->when($this->leadStatus !== 'all' && Schema::hasColumn('listing_contact_requests', 'status'), function ($query) {
                $query->where('status', $this->leadStatus);
            })
            ->latest()
            ->paginate(10, ['*'], 'leadsPage');
    }

    private function reports()
    {
        if (! Schema::hasTable('listing_reports')) {
            return $this->emptyPaginator('reportsPage');
        }

        return ListingReport::query()
            ->with(['listing:id,title,code,status', 'reportedUser:id,name,phone', 'reporter:id,name,phone'])
            ->when($this->reportSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('detail', 'like', '%' . $this->reportSearch . '%')
                        ->orWhere('reporter_name', 'like', '%' . $this->reportSearch . '%')
                        ->orWhere('reporter_phone', 'like', '%' . $this->reportSearch . '%')
                        ->orWhereHas('listing', fn ($l) => $l->where('title', 'like', '%' . $this->reportSearch . '%')->orWhere('code', 'like', '%' . $this->reportSearch . '%'));
                });
            })
            ->when($this->reportStatus !== 'all', fn ($q) => $q->where('status', $this->reportStatus))
            ->when($this->reportTarget !== 'all', fn ($q) => $q->where('target_type', $this->reportTarget))
            ->latest()
            ->paginate(12, ['*'], 'reportsPage');
    }

    private function selectedReport()
    {
        if (! $this->reportEditingId || ! Schema::hasTable('listing_reports')) {
            return null;
        }

        return ListingReport::query()
            ->with(['listing', 'reportedUser:id,name,phone', 'reporter:id,name,phone', 'handler:id,name'])
            ->find($this->reportEditingId);
    }

    private function countReportStatus($status)
    {
        if (! Schema::hasTable('listing_reports')) {
            return 0;
        }

        try {
            return ListingReport::where('status', $status)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function accounts()
    {
        return User::query()
            ->with('inviter')
            ->withCount(['invitees', 'sentInviteLogs'])
            ->when($this->accountSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->accountSearch . '%')
                        ->orWhere('phone', 'like', '%' . $this->accountSearch . '%')
                        ->orWhere('invite_code', 'like', '%' . $this->accountSearch . '%');
                });
            })
            ->when($this->accountRole !== 'all', function ($query) {
                $query->where('role', $this->accountRole);
            })
            ->orderByDesc('sent_invite_logs_count')
            ->latest()
            ->paginate(10, ['*'], 'accountsPage');
    }

    private function accountInviters()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'invite_code']);
    }

    private function selectedAccount()
    {
        if (! $this->selectedAccountId) {
            return null;
        }

        return User::query()
            ->with('inviter')
            ->withCount(['invitees', 'sentInviteLogs'])
            ->find($this->selectedAccountId);
    }

    private function selectedAccountStats()
    {
        $user = $this->selectedAccount();
        if (! $user) {
            return [];
        }

        return [
            'total_revenue' => $user->total_revenue,
            'invitees' => (int) $user->invitees_count,
            'invite_uses' => (int) $user->sent_invite_logs_count,
            'listings' => $this->countUserListings($user->id),
            'direct_leads' => $this->countUserDirectLeads($user->id),
            'listing_leads' => $this->countUserListingLeads($user->id),
            'customers' => $this->countUserCustomers($user->id),
            'favorites' => $this->countUserFavorites($user->id),
            'saved_searches' => $this->countUserSavedSearches($user->id),
        ];
    }

    public function countUserListings($userId)
    {
        try {
            return $this->userListingIdQuery($userId)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countUserCustomers($userId)
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'assigned_user_id')) {
            return 0;
        }

        return DB::table('customers')->where('assigned_user_id', $userId)->count();
    }

    private function countUserDirectLeads($userId)
    {
        if (! Schema::hasTable('listing_contact_requests') || ! Schema::hasColumn('listing_contact_requests', 'user_id')) {
            return 0;
        }

        return DB::table('listing_contact_requests')->where('user_id', $userId)->count();
    }

    private function countUserListingLeads($userId)
    {
        if (! Schema::hasTable('listing_contact_requests') || ! Schema::hasColumn('listing_contact_requests', 'listing_id')) {
            return 0;
        }

        try {
            $listingIds = $this->userListingIdQuery($userId)->pluck('id');
            if ($listingIds->isEmpty()) {
                return 0;
            }

            return DB::table('listing_contact_requests')->whereIn('listing_id', $listingIds)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countUserFavorites($userId)
    {
        if (! Schema::hasTable('listing_favorites')) {
            return 0;
        }

        return DB::table('listing_favorites')->where('user_id', $userId)->count();
    }

    private function countUserSavedSearches($userId)
    {
        if (! Schema::hasTable('saved_searches')) {
            return 0;
        }

        return DB::table('saved_searches')->where('user_id', $userId)->count();
    }

    private function selectedAccountTransactions()
    {
        if (! $this->selectedAccountId || ! Schema::hasTable('real_estate_listing_sales') || ! Schema::hasTable('real_estate_listing_sale_members')) {
            return collect();
        }

        try {
            return DB::table('real_estate_listing_sale_members')
                ->join('real_estate_listing_sales', 'real_estate_listing_sale_members.sale_id', '=', 'real_estate_listing_sales.id')
                ->join('real_estate_listings', 'real_estate_listing_sales.listing_id', '=', 'real_estate_listings.id')
                ->where('real_estate_listing_sale_members.user_id', $this->selectedAccountId)
                ->select(
                    'real_estate_listings.title as listing_title',
                    'real_estate_listing_sales.project_name',
                    'real_estate_listing_sales.actual_price',
                    'real_estate_listing_sales.revenue_amount',
                    'real_estate_listing_sale_members.received_amount',
                    'real_estate_listing_sales.sold_at'
                )
                ->orderByDesc('sold_at')
                ->limit(8)
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function selectedAccountReferrals()
    {
        if (! $this->selectedAccountId) {
            return collect();
        }

        return User::query()
            ->where('invited_by_user_id', $this->selectedAccountId)
            ->latest()
            ->limit(8)
            ->get(['id', 'name', 'phone', 'invite_code', 'created_at']);
    }

    private function selectedAccountListings()
    {
        if (! $this->selectedAccountId) {
            return collect();
        }

        try {
            return $this->userListingIdQuery($this->selectedAccountId)->latest()->limit(8)->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function userListingIdQuery($userId)
    {
        $query = RealEstateListing::query();
        if (Schema::hasColumn('real_estate_listings', 'reporter_id') && Schema::hasColumn('real_estate_listings', 'user_id')) {
            return $query->where(function ($q) use ($userId) {
                $q->where('reporter_id', $userId)->orWhere('user_id', $userId);
            });
        }

        if (Schema::hasColumn('real_estate_listings', 'reporter_id')) {
            return $query->where('reporter_id', $userId);
        }

        if (Schema::hasColumn('real_estate_listings', 'user_id')) {
            return $query->where('user_id', $userId);
        }

        return $query->whereRaw('1 = 0');
    }

    private function propertyTypeOptions()
    {
        return [
            110 => 'Bất động sản khác',
            102 => 'Biệt thự',
            103 => 'Căn hộ - chung cư',
            104 => 'Đất',
            105 => 'Đất nền dự án',
            106 => 'Mặt tiền',
            107 => 'Nhà mặt phố',
            111 => 'Nhà mặt phố (lộ giới 4m-5m)',
            108 => 'Nhà riêng',
            109 => 'Trang trại',
            112 => 'Khách sạn',
            113 => 'Nhà nghỉ',
            114 => 'Homestay',
            115 => 'Nhà trọ',
        ];
    }

    private function favorites()
    {
        if (! Schema::hasTable('listing_favorites')) {
            return $this->emptyPaginator('favoritesPage');
        }

        return DB::table('listing_favorites')
            ->leftJoin('users', 'users.id', '=', 'listing_favorites.user_id')
            ->leftJoin('real_estate_listings', 'real_estate_listings.id', '=', 'listing_favorites.listing_id')
            ->select('listing_favorites.*', 'users.name as user_name', 'users.phone as user_phone', 'real_estate_listings.title as listing_title', 'real_estate_listings.code as listing_code')
            ->orderByDesc('listing_favorites.created_at')
            ->paginate(10, ['*'], 'favoritesPage');
    }

    private function savedSearches()
    {
        if (! Schema::hasTable('saved_searches')) {
            return $this->emptyPaginator('savedSearchesPage');
        }

        return DB::table('saved_searches')
            ->leftJoin('users', 'users.id', '=', 'saved_searches.user_id')
            ->select('saved_searches.*', 'users.name as user_name', 'users.phone as user_phone')
            ->orderByDesc('saved_searches.created_at')
            ->paginate(10, ['*'], 'savedSearchesPage');
    }

    private function topViewedListings()
    {
        try {
            return RealEstateListing::query()
                ->orderByDesc('view_count')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function dailyViews()
    {
        if (! Schema::hasTable('listing_view_events')) {
            return collect();
        }

        try {
            return DB::table('listing_view_events')
                ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', now()->subDays(14))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('day')
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function recentListings()
    {
        try {
            return RealEstateListing::query()->latest()->limit(8)->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function recentLeads()
    {
        if (! Schema::hasTable('listing_contact_requests')) {
            return collect();
        }

        return ListingContactRequest::latest()->limit(8)->get();
    }

    private function emptyPaginator($pageName)
    {
        return new LengthAwarePaginator(collect(), 0, 10, 1, [
            'path' => request()->url(),
            'pageName' => $pageName,
        ]);
    }
}
