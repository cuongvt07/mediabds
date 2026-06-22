<?php

namespace App\Livewire;

use App\Models\BlogPost;
use App\Models\ListingCategory;
use App\Models\ListingContactRequest;
use App\Models\ListingReport;
use App\Models\RealEstateListing;
use App\Models\User;
use App\Models\UserInvite;
use App\Models\WebsiteHomeSection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class WebsiteAdmin extends Component
{
    use WithPagination;

    public $activeTab = 'overview';

    public $listingSearch = '';
    public $listingStatus = 'all';
    public $listingVip = 'all';

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
        $allowed = ['overview', 'home', 'listings', 'categories', 'blogs', 'accounts', 'leads', 'reports', 'favorites', 'saved-searches', 'analytics', 'settings'];
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

    public function updated($property)
    {
        if (Str::contains($property, ['Search', 'Status', 'Vip', 'Role', 'Target'])) {
            $this->resetPage();
        }
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

    public function saveSettings()
    {
        $data = $this->validate([
            'settings.contact.site_name' => 'required|string|max:120',
            'settings.contact.hotline' => 'required|string|max:40',
            'settings.contact.zalo_phone' => 'required|string|max:40',
            'settings.contact.email' => 'nullable|email|max:160',
            'settings.contact.support_hours' => 'nullable|string|max:120',

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

    public function render()
    {
        return view('livewire.website-admin', [
            'stats' => $this->stats(),
            'recentListings' => $this->recentListings(),
            'recentLeads' => $this->recentLeads(),
            'homeSections' => $this->homeSections(),
            'listings' => $this->listings(),
            'categories' => $this->categories(),
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
