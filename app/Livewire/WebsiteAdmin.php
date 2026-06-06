<?php

namespace App\Livewire;

use App\Models\BlogPost;
use App\Models\ListingCategory;
use App\Models\ListingContactRequest;
use App\Models\RealEstateListing;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class WebsiteAdmin extends Component
{
    use WithPagination;

    public $activeTab = 'overview';

    public $listingSearch = '';
    public $listingStatus = 'all';
    public $listingVip = 'all';

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
    ];

    public function setTab($tab)
    {
        $allowed = ['overview', 'listings', 'categories', 'blogs', 'leads', 'favorites', 'saved-searches', 'analytics'];
        if (in_array($tab, $allowed, true)) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function updated($property)
    {
        if (Str::contains($property, ['Search', 'Status', 'Vip'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        return view('livewire.website-admin', [
            'stats' => $this->stats(),
            'recentListings' => $this->recentListings(),
            'recentLeads' => $this->recentLeads(),
            'listings' => $this->listings(),
            'categories' => $this->categories(),
            'blogs' => $this->blogs(),
            'leads' => $this->leads(),
            'favorites' => $this->favorites(),
            'savedSearches' => $this->savedSearches(),
            'topViewedListings' => $this->topViewedListings(),
            'dailyViews' => $this->dailyViews(),
        ])->layout('components.layouts.app', ['title' => 'Website Public']);
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
        if (! in_array($status, ['active', 'pending', 'expired', 'sold'], true)) {
            return;
        }

        $listing = RealEstateListing::findOrFail($id);
        $listing->status = $status;
        $listing->is_sold = $status === 'sold';
        $listing->save();
    }

    public function updateListingVip($id, $vip)
    {
        if (! in_array($vip, ['normal', 'vip1', 'vip2', 'vip3'], true)) {
            return;
        }

        RealEstateListing::where('id', $id)->update(['vip_tier' => $vip]);
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

    private function stats()
    {
        return [
            'public_listings' => $this->countListings(),
            'pending_listings' => $this->countListingStatus('pending'),
            'categories' => $this->countTable('listing_categories'),
            'blogs' => $this->countTable('blog_posts'),
            'leads' => $this->countTable('listing_contact_requests'),
            'open_leads' => $this->countLeadStatus('new'),
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
