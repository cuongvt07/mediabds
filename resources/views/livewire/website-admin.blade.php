@php
    $statusLabels = [
        'pending' => ['Chờ duyệt', 'warning'],
        'active' => ['Đã đăng', 'success'],
        'expired' => ['Hết hạn', 'muted'],
        'sold' => ['Đã giao dịch', 'info'],
        'draft' => ['Nháp', 'muted'],
        'published' => ['Đã đăng', 'success'],
        'archived' => ['Lưu trữ', 'muted'],
        'new' => ['Mới', 'warning'],
        'contacted' => ['Đã liên hệ', 'info'],
        'qualified' => ['Tiềm năng', 'success'],
        'closed' => ['Đã chốt', 'success'],
        'spam' => ['Rác', 'danger'],
    ];

    $propertyKindLabels = [
        'apartment' => 'Căn hộ',
        'room' => 'Phòng trọ',
        'house' => 'Nhà ở',
        'office' => 'Văn phòng',
        'land' => 'Đất',
        'shared' => 'Ở ghép',
    ];

    $sectionTypeLabels = [
        'listings' => 'Danh sách tin',
        'regions' => 'Khu vực nổi bật',
        'tools' => 'Tiện ích',
        'recently_viewed' => 'Đã xem gần đây',
        'blogs' => 'Bài viết',
        'feature_descriptions' => 'Mô tả tính năng',
        'promo' => 'Khuyến mãi',
    ];

    $sourceTypeLabels = [
        'latest' => 'Mới nhất',
        'vip' => 'Tin ưu tiên',
        'property' => 'Theo loại BĐS',
        'category' => 'Theo danh mục',
        'province' => 'Theo tỉnh/thành',
        'manual' => 'Chọn thủ công',
        'regions' => 'Khu vực',
        'static' => 'Tĩnh',
        'client' => 'Trình duyệt',
    ];
@endphp

<div>
    @if ($activeTab === 'overview')
        <div class="cms-grid-2">
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Xử lý cần thiết</h2>
                    <span class="mono" style="color: var(--text-muted)">Hôm nay {{ now('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</span>
                </div>
                <div>
                    <div class="cms-kpi-row">
                        <span>Tin chờ duyệt</span>
                        <strong class="mono">{{ number_format($stats['pending_listings']) }}</strong>
                        <button class="cms-btn" wire:click="setTab('listings')">Xem</button>
                    </div>
                    <div class="cms-kpi-row">
                    <span>Khách liên hệ mới từ trang web</span>
                        <strong class="mono">{{ number_format($stats['open_leads']) }}</strong>
                        <button class="cms-btn" wire:click="setTab('leads')">Xử lý</button>
                    </div>
                    <div class="cms-kpi-row">
                        <span>Tổng tin đang hiển thị</span>
                        <strong class="mono">{{ number_format($stats['public_listings']) }}</strong>
                        <button class="cms-btn" wire:click="setTab('listings')">Lọc</button>
                    </div>
                    <div class="cms-kpi-row">
                        <span>Khối trang chủ đang cấu hình</span>
                        <strong class="mono">{{ number_format($homeSections->count()) }}</strong>
                        <button class="cms-btn" wire:click="setTab('home')">Cấu hình</button>
                    </div>
                </div>
            </section>

            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Hoạt động gần đây</h2>
                    <span class="cms-badge info">Thời gian thực</span>
                </div>
                <div class="cms-data-list">
                    @forelse ($recentLeads as $lead)
                        <button type="button" wire:click="openLead({{ $lead->id }})" class="cms-data-row" style="width:100%; border-left:0; border-right:0; border-top:0; background:transparent; color:inherit; text-align:left; cursor:pointer;">
                            <span class="cms-truncate">{{ $lead->name ?: 'Khách website' }} · {{ $lead->phone ?: '-' }}</span>
                            <span class="cms-badge {{ $statusLabels[$lead->status ?? 'new'][1] ?? 'muted' }}">{{ $statusLabels[$lead->status ?? 'new'][0] ?? 'Mới' }}</span>
                            <span class="mono">{{ optional($lead->created_at)->format('H:i') }}</span>
                        </button>
                    @empty
                        <div class="cms-data-row">
                            <span>Chưa có lead mới.</span>
                            <span></span>
                            <span></span>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="cms-grid-2" style="margin-top: 12px;">
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Thống kê vận hành</h2>
                    <span class="mono" style="color: var(--text-muted)">30 ngày gần đây</span>
                </div>
                <div>
                    <div class="cms-kpi-row"><span>Danh mục BĐS</span><strong class="mono">{{ number_format($stats['categories']) }}</strong><span></span></div>
                    <div class="cms-kpi-row"><span>Bài viết/SEO</span><strong class="mono">{{ number_format($stats['blogs']) }}</strong><span></span></div>
                    <div class="cms-kpi-row"><span>Yêu thích</span><strong class="mono">{{ number_format($stats['favorites']) }}</strong><span></span></div>
                    <div class="cms-kpi-row"><span>Tìm kiếm đã lưu</span><strong class="mono">{{ number_format($stats['saved_searches']) }}</strong><span></span></div>
                    <div class="cms-kpi-row"><span>Lượt xem được ghi nhận</span><strong class="mono">{{ number_format($stats['views']) }}</strong><span></span></div>
                </div>
            </section>

            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Tin mới từ hệ thống</h2>
                    <button class="cms-btn" wire:click="setTab('listings')">Tất cả</button>
                </div>
                <div class="cms-data-list">
                    @forelse ($recentListings as $listing)
                        @php $state = $listing->is_sold ? 'sold' : ($listing->status ?: 'active'); @endphp
                        <div class="cms-data-row">
                            <span class="cms-truncate">{{ $listing->title }}</span>
                            <span class="cms-badge {{ $statusLabels[$state][1] ?? 'muted' }}">{{ $statusLabels[$state][0] ?? $state }}</span>
                            <span class="mono">{{ $listing->code ?: '#' . $listing->id }}</span>
                        </div>
                    @empty
                        <div class="cms-data-row"><span>Chưa có dữ liệu tin đăng.</span><span></span><span></span></div>
                    @endforelse
                </div>
            </section>
        </div>
    @elseif ($activeTab === 'listings')
        <section class="cms-panel">
            <div class="cms-panel-head">
                <h2 class="cms-panel-title">Quản lý tin đăng</h2>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input class="cms-input" style="width: 300px;" wire:model.live.debounce.300ms="listingSearch" placeholder="Tìm mã tin, tiêu đề, số điện thoại">
                    <select class="cms-select" wire:model.live="listingStatus">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="pending">Chờ duyệt</option>
                        <option value="active">Đã đăng</option>
                        <option value="expired">Hết hạn</option>
                        <option value="sold">Đã giao dịch</option>
                    </select>
                    <select class="cms-select" wire:model.live="listingVip">
                        <option value="all">Tất cả mức ưu tiên</option>
                        <option value="normal">Thường</option>
                        <option value="vip1">Ưu tiên 1</option>
                        <option value="vip2">Ưu tiên 2</option>
                        <option value="vip3">Ưu tiên 3</option>
                    </select>
                </div>
            </div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th style="width:32px;"><input type="checkbox"></th>
                            <th style="width:90px;">Mã tin</th>
                            <th>Tiêu đề</th>
                            <th style="width:110px;">Khu vực</th>
                            <th class="right" style="width:110px;">Giá</th>
                            <th style="width:86px;">Diện tích</th>
                            <th style="width:125px;">Trạng thái</th>
                            <th style="width:88px;">Ưu tiên</th>
                            <th class="right" style="width:80px;">Lượt xem</th>
                            <th style="width:112px;">Ngày đăng</th>
                            <th class="right" style="width:76px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($listings as $listing)
                            @php $state = $listing->is_sold ? 'sold' : ($listing->status ?: 'active'); @endphp
                            <tr>
                                <td><input type="checkbox"></td>
                                <td class="mono" style="color: var(--text-primary)">{{ $listing->code ?: '#' . $listing->id }}</td>
                                <td><div class="cms-truncate" title="{{ $listing->title }}">{{ $listing->title }}</div></td>
                                <td><div class="cms-truncate">{{ $listing->district_name ?: $listing->province_name ?: '-' }}</div></td>
                                <td class="right mono" style="color: var(--success)">{{ number_format((float) $listing->price, 2) }} {{ $listing->price_unit }}</td>
                                <td class="mono">{{ $listing->area ? $listing->area . ' m²' : '-' }}</td>
                                <td>
                                    <select class="cms-select" wire:change="updateListingStatus({{ $listing->id }}, $event.target.value)">
                                        @foreach (['pending' => 'Chờ duyệt', 'active' => 'Đã đăng', 'expired' => 'Hết hạn', 'sold' => 'Đã giao dịch'] as $value => $label)
                                            <option value="{{ $value }}" @selected($state === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="cms-select" wire:change="updateListingVip({{ $listing->id }}, $event.target.value)">
                                        @foreach (['normal' => 'Thường', 'vip1' => 'Ưu tiên 1', 'vip2' => 'Ưu tiên 2', 'vip3' => 'Ưu tiên 3'] as $value => $label)
                                            <option value="{{ $value }}" @selected(($listing->vip_tier ?: 'normal') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="right mono">{{ number_format((int) ($listing->view_count ?? 0)) }}</td>
                                <td class="mono">{{ optional($listing->created_at)->format('d/m/Y') }}</td>
                                <td class="right">
                                    <button class="cms-btn danger" wire:click="deleteListing({{ $listing->id }})" wire:confirm="Xóa tin đăng này?"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" style="text-align:center; height:72px;">Không có tin nào khớp bộ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="cms-pagination">{{ $listings->links(data: ['scrollTo' => false]) }}</div>
        </section>
    @elseif ($activeTab === 'home')
        <section class="cms-panel">
            <div class="cms-panel-head">
                <h2 class="cms-panel-title">Cấu hình trang chủ website</h2>
                <span style="color: var(--text-secondary)">Nguồn dữ liệu: /api/v1/homepage</span>
            </div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">Bật</th>
                            <th>Khối hiển thị</th>
                            <th style="width:130px;">Loại</th>
                            <th style="width:130px;">Nguồn</th>
                            <th style="width:180px;">Điều kiện</th>
                            <th class="right" style="width:90px;">Tin khớp</th>
                            <th class="right" style="width:70px;">Giới hạn</th>
                            <th class="right" style="width:70px;">Thứ tự</th>
                            <th class="right" style="width:92px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($homeSections as $section)
                            <tr style="{{ $section->enabled ? '' : 'opacity:.58' }}">
                                <td><button class="cms-badge {{ $section->enabled ? 'success' : 'muted' }}" wire:click="toggleHomeSection({{ $section->id }})">{{ $section->enabled ? 'Bật' : 'Tắt' }}</button></td>
                                <td>
                                    <div class="cms-truncate" style="color: var(--text-primary); font-weight:700">{{ $section->title }}</div>
                                    <div class="mono cms-truncate" style="color: var(--text-muted); font-size:11px">{{ $section->key }}</div>
                                </td>
                                <td>{{ $sectionTypeLabels[$section->section_type] ?? $section->section_type }}</td>
                                <td>{{ $sourceTypeLabels[$section->source_type] ?? $section->source_type }}</td>
                                <td class="cms-truncate">
                                    {{ $section->transaction_type ?: 'Tất cả' }} · {{ $propertyKindLabels[$section->property_kind] ?? ($section->property_kind ?: 'mọi loại') }} · {{ $section->province_name ?: ($section->category_id ?: '-') }}
                                </td>
                                <td class="right mono">{{ $section->section_type === 'listings' ? number_format($this->homeSectionCount($section)) : '-' }}</td>
                                <td class="right mono">{{ $section->limit }}</td>
                                <td class="right mono">{{ $section->sort_order_index }}</td>
                                <td class="right"><button class="cms-btn" wire:click="editHomeSection({{ $section->id }})">Sửa</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" style="text-align:center; height:72px;">Chưa có bảng website_home_sections. Hãy chạy migration/seeder.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($activeTab === 'categories')
        <section class="cms-panel">
            <div class="cms-panel-head">
                <h2 class="cms-panel-title">Danh mục bất động sản</h2>
                <div style="display:flex; gap:8px;">
                    <input class="cms-input" style="width: 260px;" wire:model.live.debounce.300ms="categorySearch" placeholder="Tìm tên, slug, mã danh mục">
                    <button class="cms-btn primary" wire:click="createCategory"><i class="fa-solid fa-plus"></i> Thêm</button>
                </div>
            </div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th style="width:120px;">Mã</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th style="width:110px;">Giao dịch</th>
                            <th style="width:130px;">Loại BĐS</th>
                            <th class="right" style="width:90px;">Thứ tự</th>
                            <th class="right" style="width:130px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="mono">{{ $category->id }}</td>
                                <td style="color: var(--text-primary); font-weight:700">{{ $category->name }}</td>
                                <td class="mono cms-truncate">{{ $category->slug }}</td>
                                <td>{{ $category->transaction_type ?: '-' }}</td>
                                <td>{{ $category->property_type ?: '-' }}</td>
                                <td class="right mono">{{ $category->sort_order }}</td>
                                <td class="right">
                                    <button class="cms-btn" wire:click="editCategory('{{ $category->id }}')">Sửa</button>
                                    <button class="cms-btn danger" wire:click="deleteCategory('{{ $category->id }}')" wire:confirm="Xóa danh mục này?">Xóa</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center; height:72px;">Chưa có danh mục.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="cms-pagination">{{ $categories->links(data: ['scrollTo' => false]) }}</div>
        </section>
    @elseif ($activeTab === 'blogs')
        <section class="cms-panel">
            <div class="cms-panel-head">
                <h2 class="cms-panel-title">Bài viết và SEO</h2>
                <div style="display:flex; gap:8px;">
                    <input class="cms-input" style="width: 280px;" wire:model.live.debounce.300ms="blogSearch" placeholder="Tìm bài viết, slug, tag">
                    <select class="cms-select" wire:model.live="blogStatus">
                        <option value="all">Tất cả</option>
                        <option value="published">Đã đăng</option>
                        <option value="draft">Nháp</option>
                        <option value="archived">Lưu trữ</option>
                    </select>
                    <button class="cms-btn primary" wire:click="createBlog"><i class="fa-solid fa-plus"></i> Viết bài</button>
                </div>
            </div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th>Bài viết</th>
                            <th style="width:150px;">Tag</th>
                            <th style="width:110px;">Trạng thái</th>
                            <th style="width:120px;">Thời lượng</th>
                            <th style="width:130px;">Xuất bản</th>
                            <th class="right" style="width:140px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($blogs as $post)
                            <tr>
                                <td>
                                    <div class="cms-truncate" style="color: var(--text-primary); font-weight:700">{{ $post->title }}</div>
                                    <div class="mono cms-truncate" style="color: var(--text-muted); font-size:11px">{{ $post->slug }}</div>
                                </td>
                                <td>{{ $post->category_tag ?: '-' }}</td>
                                <td><button class="cms-badge {{ $statusLabels[$post->status ?? 'draft'][1] ?? 'muted' }}" wire:click="toggleBlogStatus({{ $post->id }})">{{ $statusLabels[$post->status ?? 'draft'][0] ?? $post->status }}</button></td>
                                <td class="mono">{{ $post->reading_minutes }} phút</td>
                                <td class="mono">{{ optional($post->published_at)->format('d/m/Y') ?: '-' }}</td>
                                <td class="right">
                                    <button class="cms-btn" wire:click="editBlog({{ $post->id }})">Sửa</button>
                                    <button class="cms-btn danger" wire:click="deleteBlog({{ $post->id }})" wire:confirm="Xóa bài viết này?">Xóa</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center; height:72px;">Chưa có bài viết.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="cms-pagination">{{ $blogs->links(data: ['scrollTo' => false]) }}</div>
        </section>
    @elseif ($activeTab === 'leads')
        <section class="cms-panel">
            <div class="cms-panel-head">
                <h2 class="cms-panel-title">Khách liên hệ từ trang web</h2>
                <div style="display:flex; gap:8px;">
                    <input class="cms-input" style="width: 300px;" wire:model.live.debounce.300ms="leadSearch" placeholder="Tìm khách, số điện thoại, nội dung">
                    <select class="cms-select" wire:model.live="leadStatus">
                        <option value="all">Tất cả lead</option>
                        <option value="new">Mới</option>
                        <option value="contacted">Đã liên hệ</option>
                        <option value="qualified">Tiềm năng</option>
                        <option value="closed">Đã chốt</option>
                        <option value="spam">Rác</option>
                    </select>
                </div>
            </div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th style="width:180px;">Khách</th>
                            <th>Nội dung</th>
                            <th style="width:130px;">Trạng thái</th>
                            <th style="width:120px;">Nguồn</th>
                            <th style="width:132px;">Ngày nhận</th>
                            <th class="right" style="width:130px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>
                                    <div style="color: var(--text-primary); font-weight:700">{{ $lead->name ?: 'Khách website' }}</div>
                                    <div class="mono" style="color: var(--text-muted); font-size:11px">{{ $lead->phone ?: '-' }}</div>
                                </td>
                                <td><div class="cms-truncate" title="{{ $lead->message }}">{{ $lead->message ?: '-' }}</div></td>
                                <td>
                                    <select class="cms-select" wire:change="quickLeadStatus({{ $lead->id }}, $event.target.value)">
                                        @foreach (['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'closed' => 'Đã chốt', 'spam' => 'Rác'] as $value => $label)
                                            <option value="{{ $value }}" @selected(($lead->status ?: 'new') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>{{ $lead->source ?: 'trang web' }}</td>
                                <td class="mono">{{ optional($lead->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="right">
                                    <button class="cms-btn" wire:click="openLead({{ $lead->id }})">Mở</button>
                                    <button class="cms-btn danger" wire:click="deleteLead({{ $lead->id }})" wire:confirm="Xóa khách liên hệ này?">Xóa</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center; height:72px;">Chưa có lead phù hợp.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="cms-pagination">{{ $leads->links(data: ['scrollTo' => false]) }}</div>
        </section>
    @elseif ($activeTab === 'favorites')
        <section class="cms-panel">
            <div class="cms-panel-head"><h2 class="cms-panel-title">Tin được yêu thích</h2></div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead><tr><th>Người dùng</th><th>Tin đăng</th><th style="width:160px;">Thời gian</th></tr></thead>
                    <tbody>
                        @forelse ($favorites as $item)
                            <tr>
                                <td>{{ $item->user_name ?: $item->user_phone ?: 'Người dùng' }}</td>
                                <td>{{ $item->listing_title ?: ($item->listing_code ?: '#' . $item->listing_id) }}</td>
                                <td class="mono">{{ $item->created_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; height:72px;">Chưa có dữ liệu yêu thích.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="cms-pagination">{{ $favorites->links(data: ['scrollTo' => false]) }}</div>
        </section>
    @elseif ($activeTab === 'saved-searches')
        <section class="cms-panel">
            <div class="cms-panel-head"><h2 class="cms-panel-title">Tìm kiếm đã lưu</h2></div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead><tr><th>Người dùng</th><th>Tên bộ lọc</th><th>Điều kiện</th><th style="width:160px;">Thời gian</th></tr></thead>
                    <tbody>
                        @forelse ($savedSearches as $item)
                            <tr>
                                <td>{{ $item->user_name ?: $item->user_phone ?: 'Người dùng' }}</td>
                                <td>{{ $item->name ?: 'Bộ lọc' }}</td>
                                <td><div class="cms-truncate mono">{{ json_encode($item->filters ?? [], JSON_UNESCAPED_UNICODE) }}</div></td>
                                <td class="mono">{{ $item->created_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center; height:72px;">Chưa có tìm kiếm đã lưu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="cms-pagination">{{ $savedSearches->links(data: ['scrollTo' => false]) }}</div>
        </section>
    @elseif ($activeTab === 'analytics')
        <div class="cms-grid-2">
            <section class="cms-panel">
                <div class="cms-panel-head"><h2 class="cms-panel-title">Tin có lượt xem cao</h2></div>
                <div class="cms-data-list">
                    @forelse ($topViewedListings as $listing)
                        <div class="cms-data-row">
                            <span class="cms-truncate">{{ $listing->title }}</span>
                            <span class="mono">{{ $listing->code ?: '#' . $listing->id }}</span>
                            <strong class="mono" style="text-align:right">{{ number_format((int) $listing->view_count) }}</strong>
                        </div>
                    @empty
                        <div class="cms-data-row"><span>Chưa có dữ liệu xem tin.</span><span></span><span></span></div>
                    @endforelse
                </div>
            </section>
            <section class="cms-panel">
                <div class="cms-panel-head"><h2 class="cms-panel-title">Lượt xem theo ngày</h2></div>
                <div class="cms-data-list">
                    @forelse ($dailyViews as $row)
                        <div class="cms-data-row">
                            <span class="mono">{{ $row->day }}</span>
                            <span></span>
                            <strong class="mono" style="text-align:right">{{ number_format((int) $row->total) }}</strong>
                        </div>
                    @empty
                        <div class="cms-data-row"><span>Chưa có dữ liệu lượt xem.</span><span></span><span></span></div>
                    @endforelse
                </div>
            </section>
        </div>
    @endif

    @if ($showHomeSectionModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Cấu hình khối trang chủ</h2>
                    <button class="cms-icon-btn" wire:click="closeHomeSectionModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full"><span class="cms-label">Tiêu đề *</span><input class="cms-input" wire:model="homeSectionTitle"></label>
                    <label class="cms-field full"><span class="cms-label">Mô tả</span><input class="cms-input" wire:model="homeSectionDescription"></label>
                    <label class="cms-field"><span class="cms-label">Loại khối</span><select class="cms-select" wire:model="homeSectionType">@foreach($sectionTypeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="cms-field"><span class="cms-label">Nguồn dữ liệu</span><select class="cms-select" wire:model="homeSectionSourceType">@foreach($sourceTypeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="cms-field"><span class="cms-label">Giao dịch</span><select class="cms-select" wire:model="homeSectionTransactionType"><option value="">Tất cả</option><option value="sale">Bán</option><option value="rent">Cho thuê</option></select></label>
                    <label class="cms-field"><span class="cms-label">Loại BĐS</span><select class="cms-select" wire:model="homeSectionPropertyKind"><option value="">Tất cả</option>@foreach($propertyKindLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="cms-field"><span class="cms-label">Danh mục</span><input class="cms-input" wire:model="homeSectionCategoryId"></label>
                    <label class="cms-field"><span class="cms-label">Tỉnh/Thành</span><input class="cms-input" wire:model="homeSectionProvinceName"></label>
                    <label class="cms-field"><span class="cms-label">Sắp xếp theo</span><select class="cms-select" wire:model="homeSectionSortBy"><option value="created_at">Ngày đăng</option><option value="price">Giá</option><option value="area">Diện tích</option><option value="view_count">Lượt xem</option></select></label>
                    <label class="cms-field"><span class="cms-label">Chiều sắp xếp</span><select class="cms-select" wire:model="homeSectionSortOrder"><option value="desc">Giảm dần</option><option value="asc">Tăng dần</option></select></label>
                    <label class="cms-field"><span class="cms-label">Giới hạn</span><input class="cms-input mono" type="number" wire:model="homeSectionLimit"></label>
                    <label class="cms-field"><span class="cms-label">Thứ tự</span><input class="cms-input mono" type="number" wire:model="homeSectionSortOrderIndex"></label>
                    <label class="cms-field full"><span class="cms-label">Đường dẫn</span><input class="cms-input" wire:model="homeSectionHref"></label>
                    <label class="cms-field full"><span class="cms-label">ID tin thủ công, cách nhau bằng dấu phẩy</span><input class="cms-input mono" wire:model="homeSectionManualIds"></label>
                    <label class="cms-field"><span class="cms-label">Trạng thái</span><select class="cms-select" wire:model="homeSectionEnabled"><option value="1">Bật</option><option value="0">Tắt</option></select></label>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeHomeSectionModal">Hủy</button>
                    <button class="cms-btn primary" wire:click="saveHomeSection">Lưu cấu hình</button>
                </div>
            </section>
        </div>
    @endif

    @if ($showCategoryModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $categoryEditing ? 'Sửa danh mục' : 'Thêm danh mục' }}</h2>
                    <button class="cms-icon-btn" wire:click="closeCategoryModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field"><span class="cms-label">Mã danh mục *</span><input class="cms-input mono" wire:model="categoryId" @disabled($categoryEditing)></label>
                    <label class="cms-field"><span class="cms-label">Tên *</span><input class="cms-input" wire:model="categoryName"></label>
                    <label class="cms-field"><span class="cms-label">Slug</span><input class="cms-input mono" wire:model="categorySlug"></label>
                    <label class="cms-field"><span class="cms-label">Giao dịch</span><select class="cms-select" wire:model="categoryTransactionType"><option value="both">Cả hai</option><option value="sale">Bán</option><option value="rent">Cho thuê</option></select></label>
                    <label class="cms-field"><span class="cms-label">Loại BĐS</span><input class="cms-input" wire:model="categoryPropertyType"></label>
                    <label class="cms-field"><span class="cms-label">Icon</span><input class="cms-input mono" wire:model="categoryIcon"></label>
                    <label class="cms-field"><span class="cms-label">Thứ tự</span><input class="cms-input mono" type="number" wire:model="categorySortOrder"></label>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeCategoryModal">Hủy</button>
                    <button class="cms-btn primary" wire:click="saveCategory">Lưu danh mục</button>
                </div>
            </section>
        </div>
    @endif

    @if ($showBlogModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $blogEditingId ? 'Sửa bài viết' : 'Viết bài mới' }}</h2>
                    <button class="cms-icon-btn" wire:click="closeBlogModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full"><span class="cms-label">Tiêu đề *</span><input class="cms-input" wire:model="blogTitle"></label>
                    <label class="cms-field full"><span class="cms-label">Slug</span><input class="cms-input mono" wire:model="blogSlug"></label>
                    <label class="cms-field full"><span class="cms-label">Tóm tắt</span><textarea class="cms-textarea" style="min-height:70px" wire:model="blogExcerpt"></textarea></label>
                    <label class="cms-field full"><span class="cms-label">Nội dung *</span><textarea class="cms-textarea" style="min-height:220px" wire:model="blogContent"></textarea></label>
                    <label class="cms-field full"><span class="cms-label">Ảnh đại diện</span><input class="cms-input" wire:model="blogCoverImage"></label>
                    <label class="cms-field"><span class="cms-label">Tác giả</span><input class="cms-input" wire:model="blogAuthorName"></label>
                    <label class="cms-field"><span class="cms-label">Tag chính</span><input class="cms-input" wire:model="blogCategoryTag"></label>
                    <label class="cms-field"><span class="cms-label">Tags</span><input class="cms-input" wire:model="blogTags"></label>
                    <label class="cms-field"><span class="cms-label">Phút đọc</span><input class="cms-input mono" type="number" wire:model="blogReadingMinutes"></label>
                    <label class="cms-field"><span class="cms-label">Trạng thái</span><select class="cms-select" wire:model="blogStatusValue"><option value="draft">Nháp</option><option value="published">Đã đăng</option><option value="archived">Lưu trữ</option></select></label>
                    <label class="cms-field"><span class="cms-label">Xuất bản lúc</span><input class="cms-input mono" type="datetime-local" wire:model="blogPublishedAt"></label>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeBlogModal">Hủy</button>
                    <button class="cms-btn primary" wire:click="saveBlog">Lưu bài viết</button>
                </div>
            </section>
        </div>
    @endif

    @if ($showLeadModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal" style="width: min(520px, calc(100vw - 48px));">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Xử lý lead</h2>
                    <button class="cms-icon-btn" wire:click="closeLeadModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <div class="cms-field"><span class="cms-label">Khách</span><div style="color:var(--text-primary); font-weight:700">{{ $leadName ?: 'Khách website' }}</div></div>
                    <div class="cms-field"><span class="cms-label">Điện thoại</span><div class="mono">{{ $leadPhone ?: '-' }}</div></div>
                    <div class="cms-field full"><span class="cms-label">Nội dung</span><div style="color:var(--text-secondary)">{{ $leadMessage ?: '-' }}</div></div>
                    <label class="cms-field full"><span class="cms-label">Trạng thái</span><select class="cms-select" wire:model="leadStatusValue"><option value="new">Mới</option><option value="contacted">Đã liên hệ</option><option value="qualified">Tiềm năng</option><option value="closed">Đã chốt</option><option value="spam">Rác</option></select></label>
                    <label class="cms-field full"><span class="cms-label">Ghi chú nội bộ</span><textarea class="cms-textarea" wire:model="leadAdminNote"></textarea></label>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeLeadModal">Hủy</button>
                    <button class="cms-btn primary" wire:click="saveLead">Lưu khách liên hệ</button>
                </div>
            </section>
        </div>
    @endif
</div>
