@php
    $statusLabels = [
        'pending' => ['Chờ duyệt', 'warning'],
        'active' => ['Đã đăng', 'success'],
        'expired' => ['Hết hạn', 'muted'],
        'rejected' => ['Từ chối', 'danger'],
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

    $roleLabels = [
        'admin' => ['Quản trị viên', 'danger'],
        'ctv' => ['Cộng tác viên', 'info'],
        'buyer' => ['Người đăng tin', 'success'],
    ];
@endphp

<div>
    @if (session()->has('message'))
        <div class="cms-flash" wire:key="cms-flash-{{ md5(session('message')) }}" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3500)">
            <i class="fa-solid fa-circle-check"></i> {{ session('message') }}
        </div>
    @endif

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
                    <div class="cms-kpi-row"><span>Tài khoản người dùng</span><strong class="mono">{{ number_format($stats['accounts']) }}</strong><span></span></div>
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
                        <option value="rejected">Từ chối</option>
                        <option value="sold">Đã giao dịch</option>
                    </select>
                    <select class="cms-select" wire:model.live="listingVip">
                        <option value="all">Tất cả mức ưu tiên</option>
                        <option value="normal">Thường</option>
                        <option value="vip1">Ưu tiên 1</option>
                        <option value="vip2">Ưu tiên 2</option>
                        <option value="vip3">Ưu tiên 3</option>
                    </select>
                    <button class="cms-btn primary" wire:click="openCreateListing"><i class="fa-solid fa-plus"></i> Thêm tin đăng</button>
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
                            <th style="width:150px;">Người đăng</th>
                            <th style="width:125px;">Trạng thái</th>
                            <th style="width:88px;">Ưu tiên</th>
                            <th class="right" style="width:80px;">Lượt xem</th>
                            <th style="width:112px;">Ngày đăng</th>
                            <th class="right" style="width:104px;">Thao tác</th>
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
                                <td class="right mono" style="color: var(--success)" title="{{ number_format((float) $listing->price, 0, ',', '.') }} đ">{{ $this->formatMoneyShort($listing->price) }}</td>
                                <td>
                                    <div class="cms-truncate" style="color: var(--text-primary)">{{ optional($listing->user)->name ?: 'Ẩn danh' }}</div>
                                    <div class="mono cms-truncate" style="color: var(--text-muted); font-size:11px">{{ optional($listing->user)->phone ?: '' }}</div>
                                </td>
                                <td>
                                    <select class="cms-select" wire:change="updateListingStatus({{ $listing->id }}, $event.target.value)">
                                        @foreach (['pending' => 'Chờ duyệt', 'active' => 'Đã đăng', 'expired' => 'Hết hạn', 'rejected' => 'Từ chối', 'sold' => 'Đã giao dịch'] as $value => $label)
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
                                    <div style="display:flex; gap:6px; justify-content:flex-end;">
                                        <button class="cms-btn" wire:click="editListing({{ $listing->id }})" title="Sửa tin đăng"><i class="fa-solid fa-pen"></i></button>
                                        <button class="cms-btn danger" wire:click="deleteListing({{ $listing->id }})" wire:confirm="Xóa tin đăng này?" title="Xóa tin đăng"><i class="fa-solid fa-trash"></i></button>
                                    </div>
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
    @elseif ($activeTab === 'vehicles')
        <livewire:vehicle-listing />
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
    @elseif ($activeTab === 'accounts')
        <div class="cms-grid-2">
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Tài khoản người dùng</h2>
                    <div style="display:flex; gap:8px;">
                        <input class="cms-input" style="width: 280px;" wire:model.live.debounce.300ms="accountSearch" placeholder="Tìm tên, số điện thoại, mã mời">
                        <select class="cms-select" wire:model.live="accountRole">
                            <option value="all">Tất cả vai trò</option>
                            <option value="buyer">Người đăng tin</option>
                            <option value="ctv">Cộng tác viên</option>
                            <option value="admin">Quản trị viên</option>
                        </select>
                        <button class="cms-btn primary" wire:click="createAccount"><i class="fa-solid fa-plus"></i> Thêm</button>
                    </div>
                </div>
                <div class="cms-table-wrap cms-scrollbar">
                    <table class="cms-table">
                        <thead>
                            <tr>
                                <th>Tài khoản</th>
                                <th style="width:112px;">Vai trò</th>
                                <th style="width:120px;">Mã mời</th>
                                <th style="width:170px;">Người mời</th>
                                <th class="right" style="width:80px;">Đã mời</th>
                                <th class="right" style="width:80px;">Tin đăng</th>
                                <th style="width:112px;">Ngày tạo</th>
                                <th class="right" style="width:142px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accounts as $account)
                                @php
                                    $role = $account->role ?: 'buyer';
                                    $roleMeta = $roleLabels[$role] ?? ['Người dùng', 'muted'];
                                @endphp
                                <tr style="{{ (int) $selectedAccountId === (int) $account->id ? 'background: var(--accent-dim);' : '' }}">
                                    <td>
                                        <button type="button" wire:click="selectAccount({{ $account->id }})" style="display:block; width:100%; border:0; background:transparent; color:inherit; text-align:left; cursor:pointer;">
                                            <div class="cms-truncate" style="color: var(--text-primary); font-weight:700">{{ $account->name ?: 'Chưa đặt tên' }}</div>
                                            <div class="mono" style="color: var(--text-muted); font-size:11px">{{ $account->phone ?: '-' }}</div>
                                        </button>
                                    </td>
                                    <td><span class="cms-badge {{ $roleMeta[1] }}">{{ $roleMeta[0] }}</span></td>
                                    <td class="mono">{{ $account->invite_code ?: '-' }}</td>
                                    <td>
                                        @if ($account->inviter)
                                            <div class="cms-truncate">{{ $account->inviter->name }}</div>
                                            <div class="mono" style="color: var(--text-muted); font-size:11px">{{ $account->inviter->invite_code ?: '-' }}</div>
                                        @else
                                            <span style="color: var(--text-muted)">Tài khoản gốc</span>
                                        @endif
                                    </td>
                                    <td class="right mono">{{ number_format((int) $account->invitees_count) }}</td>
                                    <td class="right mono">{{ number_format($this->countUserListings($account->id)) }}</td>
                                    <td class="mono">{{ optional($account->created_at)->format('d/m/Y') }}</td>
                                    <td class="right">
                                        <button class="cms-btn" wire:click="selectAccount({{ $account->id }})">Xem</button>
                                        <button class="cms-btn" wire:click="editAccount({{ $account->id }})">Sửa</button>
                                        <button class="cms-btn danger" wire:click="confirmDeleteAccount({{ $account->id }})">Xóa</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" style="text-align:center; height:72px;">Không có tài khoản phù hợp.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="cms-pagination">{{ $accounts->links(data: ['scrollTo' => false]) }}</div>
            </section>

            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Hồ sơ và dữ liệu liên quan</h2>
                    @if ($selectedAccount)
                        <button class="cms-btn" wire:click="editAccount({{ $selectedAccount->id }})">Sửa hồ sơ</button>
                    @endif
                </div>
                @if ($selectedAccount)
                    @php
                        $selectedRole = $selectedAccount->role ?: 'buyer';
                        $selectedRoleMeta = $roleLabels[$selectedRole] ?? ['Người dùng', 'muted'];
                    @endphp
                    <div style="padding:10px; border-bottom:1px solid var(--border);">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                            <div>
                                <div style="font-weight:800; color:var(--text-primary);">{{ $selectedAccount->name ?: 'Chưa đặt tên' }}</div>
                                <div class="mono" style="color:var(--text-muted);">{{ $selectedAccount->phone ?: '-' }}</div>
                            </div>
                            <span class="cms-badge {{ $selectedRoleMeta[1] }}">{{ $selectedRoleMeta[0] }}</span>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-top:10px;">
                            <div><span class="cms-label">Mã mời</span><div class="mono">{{ $selectedAccount->invite_code ?: '-' }}</div></div>
                            <div><span class="cms-label">Người mời</span><div>{{ $selectedAccount->inviter?->name ?: 'Tài khoản gốc' }}</div></div>
                            <div><span class="cms-label">PIN xem SĐT</span><div class="mono">{{ $selectedAccount->view_phone_pin ?: '-' }}</div></div>
                            <div><span class="cms-label">Ngày tạo</span><div class="mono">{{ optional($selectedAccount->created_at)->format('d/m/Y H:i') }}</div></div>
                        </div>
                    </div>

                    <div class="cms-data-list">
                        <div class="cms-kpi-row"><span>Tổng doanh thu cá nhân</span><strong class="mono" title="{{ number_format((float) ($selectedAccountStats['total_revenue'] ?? 0), 0, ',', '.') }} đ">{{ $this->formatMoneyShort($selectedAccountStats['total_revenue'] ?? 0) }}</strong><span></span></div>
                        <div class="cms-kpi-row"><span>Tin đã đăng/phụ trách</span><strong class="mono">{{ number_format((int) ($selectedAccountStats['listings'] ?? 0)) }}</strong><span></span></div>
                        <div class="cms-kpi-row"><span>Lead gửi bởi tài khoản</span><strong class="mono">{{ number_format((int) ($selectedAccountStats['direct_leads'] ?? 0)) }}</strong><span></span></div>
                        <div class="cms-kpi-row"><span>Lead phát sinh trên tin của tài khoản</span><strong class="mono">{{ number_format((int) ($selectedAccountStats['listing_leads'] ?? 0)) }}</strong><span></span></div>
                        <div class="cms-kpi-row"><span>Khách hàng được phân công</span><strong class="mono">{{ number_format((int) ($selectedAccountStats['customers'] ?? 0)) }}</strong><span></span></div>
                        <div class="cms-kpi-row"><span>Tin đã lưu yêu thích</span><strong class="mono">{{ number_format((int) ($selectedAccountStats['favorites'] ?? 0)) }}</strong><span></span></div>
                        <div class="cms-kpi-row"><span>Tìm kiếm đã lưu</span><strong class="mono">{{ number_format((int) ($selectedAccountStats['saved_searches'] ?? 0)) }}</strong><span></span></div>
                        <div class="cms-kpi-row"><span>CTV/người dùng đã mời</span><strong class="mono">{{ number_format((int) ($selectedAccountStats['invitees'] ?? 0)) }}</strong><span></span></div>
                    </div>

                    <div class="cms-panel-head"><h3 class="cms-panel-title">Tin gần đây của tài khoản</h3></div>
                    <div class="cms-data-list">
                        @forelse ($selectedAccountListings as $listing)
                            <div class="cms-data-row">
                                <span class="cms-truncate">{{ $listing->title }}</span>
                                <span class="mono">{{ $listing->code ?: '#' . $listing->id }}</span>
                                <span class="mono" style="text-align:right">{{ optional($listing->created_at)->format('d/m') }}</span>
                            </div>
                        @empty
                            <div class="cms-data-row"><span>Chưa có tin đăng liên quan.</span><span></span><span></span></div>
                        @endforelse
                    </div>

                    <div class="cms-panel-head"><h3 class="cms-panel-title">Giao dịch/hoa hồng gần đây</h3></div>
                    <div class="cms-data-list">
                        @forelse ($selectedAccountTransactions as $tx)
                            <div class="cms-data-row">
                                <span class="cms-truncate">{{ $tx->listing_title }}</span>
                                <span class="mono" title="{{ number_format((float) $tx->received_amount, 0, ',', '.') }} đ">{{ $this->formatMoneyShort($tx->received_amount) }}</span>
                                <span class="mono" style="text-align:right">{{ $tx->sold_at ? \Carbon\Carbon::parse($tx->sold_at)->format('d/m') : '-' }}</span>
                            </div>
                        @empty
                            <div class="cms-data-row"><span>Chưa có giao dịch ghi nhận.</span><span></span><span></span></div>
                        @endforelse
                    </div>

                    <div class="cms-panel-head"><h3 class="cms-panel-title">Người được mời gần đây</h3></div>
                    <div class="cms-data-list">
                        @forelse ($selectedAccountReferrals as $referral)
                            <div class="cms-data-row">
                                <span class="cms-truncate">{{ $referral->name }}</span>
                                <span class="mono">{{ $referral->phone ?: '-' }}</span>
                                <span class="mono" style="text-align:right">{{ optional($referral->created_at)->format('d/m') }}</span>
                            </div>
                        @empty
                            <div class="cms-data-row"><span>Chưa mời tài khoản nào.</span><span></span><span></span></div>
                        @endforelse
                    </div>
                @else
                    <div style="padding:32px; text-align:center; color:var(--text-secondary);">
                        Chọn một tài khoản để xem toàn bộ dữ liệu liên quan: tin đăng, lead, yêu thích, tìm kiếm lưu, người mời và giao dịch.
                    </div>
                @endif
            </section>
        </div>
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
                                <td>{{ ($item->user_name ?? null) ?: ($item->user_phone ?? null) ?: 'Người dùng' }}</td>
                                <td>{{ ($item->name ?? $item->title ?? null) ?: 'Bộ lọc' }}</td>
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
    @elseif ($activeTab === 'reports')
        @php
            $reasonLabels = \App\Models\ListingReport::REASONS;
            $reportStatusLabels = ['pending' => ['Chờ xử lý', 'warning'], 'resolved_removed' => ['Đã gỡ', 'danger'], 'resolved_kept' => ['Giữ bài', 'success']];
        @endphp
        <section class="cms-panel">
            <div class="cms-panel-head">
                <h2 class="cms-panel-title">Báo cáo vi phạm</h2>
                <div style="display:flex; gap:8px;">
                    <input class="cms-input" style="width: 260px;" wire:model.live.debounce.300ms="reportSearch" placeholder="Tìm tin, người báo cáo, nội dung">
                    <select class="cms-select" wire:model.live="reportTarget">
                        <option value="all">Tất cả đối tượng</option>
                        <option value="listing">Bài đăng</option>
                        <option value="user">Tài khoản</option>
                    </select>
                    <select class="cms-select" wire:model.live="reportStatus">
                        <option value="pending">Chờ xử lý</option>
                        <option value="resolved_removed">Đã gỡ</option>
                        <option value="resolved_kept">Giữ bài</option>
                        <option value="all">Tất cả</option>
                    </select>
                </div>
            </div>
            <div class="cms-table-wrap cms-scrollbar">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Đối tượng</th>
                            <th>Bài đăng / Tài khoản bị báo cáo</th>
                            <th style="width:140px;">Lý do</th>
                            <th style="width:160px;">Người báo cáo</th>
                            <th style="width:120px;">Trạng thái</th>
                            <th style="width:120px;">Ngày</th>
                            <th class="right" style="width:150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            @php $rsMeta = $reportStatusLabels[$report->status] ?? ['—', 'muted']; @endphp
                            <tr>
                                <td><span class="cms-badge {{ $report->target_type === 'listing' ? 'info' : 'warning' }}">{{ $report->target_type === 'listing' ? 'Bài đăng' : 'Tài khoản' }}</span></td>
                                <td>
                                    @if ($report->target_type === 'listing')
                                        <div class="cms-truncate" style="color: var(--text-primary); font-weight:700">{{ $report->listing?->title ?: 'Tin đã xóa' }}</div>
                                        <div class="mono cms-truncate" style="color: var(--text-muted); font-size:11px">{{ $report->listing?->code ?: ($report->listing_id ? '#' . $report->listing_id : '-') }}</div>
                                    @else
                                        <div class="cms-truncate" style="color: var(--text-primary); font-weight:700">{{ $report->reportedUser?->name ?: 'Tài khoản' }}</div>
                                        <div class="mono cms-truncate" style="color: var(--text-muted); font-size:11px">{{ $report->reportedUser?->phone ?: '-' }}</div>
                                    @endif
                                </td>
                                <td><div class="cms-truncate" title="{{ $report->detail }}">{{ $reasonLabels[$report->reason] ?? $report->reason }}</div></td>
                                <td>
                                    <div class="cms-truncate">{{ $report->reporter?->name ?: $report->reporter_name ?: 'Ẩn danh' }}</div>
                                    <div class="mono" style="color: var(--text-muted); font-size:11px">{{ $report->reporter?->phone ?: $report->reporter_phone ?: '-' }}</div>
                                </td>
                                <td><span class="cms-badge {{ $rsMeta[1] }}">{{ $rsMeta[0] }}</span></td>
                                <td class="mono">{{ optional($report->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="right">
                                    <button class="cms-btn primary" wire:click="openReport({{ $report->id }})">Xử lý</button>
                                    <button class="cms-btn danger" wire:click="deleteReport({{ $report->id }})" wire:confirm="Xóa báo cáo này?"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center; height:72px;">Không có báo cáo nào khớp bộ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="cms-pagination">{{ $reports->links(data: ['scrollTo' => false]) }}</div>
        </section>
    @elseif ($activeTab === 'settings')
        <div class="cms-grid-2">
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Liên hệ &amp; Zalo</h2>
                    <span style="color: var(--text-secondary)">Hiển thị trên website &amp; nút mua gói</span>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full"><span class="cms-label">Tên website</span><input class="cms-input" wire:model="settings.contact.site_name"></label>
                    <label class="cms-field"><span class="cms-label">Hotline</span><input class="cms-input mono" wire:model="settings.contact.hotline"></label>
                    <label class="cms-field"><span class="cms-label">Số Zalo (nhận liên hệ mua gói)</span><input class="cms-input mono" wire:model="settings.contact.zalo_phone"></label>
                    <label class="cms-field"><span class="cms-label">Email</span><input class="cms-input" wire:model="settings.contact.email"></label>
                    <label class="cms-field"><span class="cms-label">Giờ hỗ trợ</span><input class="cms-input" wire:model="settings.contact.support_hours"></label>
                    @error('settings.contact.zalo_phone') <div class="cms-field full" style="color:var(--danger); font-size:12px;">{{ $message }}</div> @enderror
                </div>

                <div class="cms-panel-head"><h3 class="cms-panel-title">Gói đăng tin</h3></div>
                @php
                    $quotaOptions = [10, 15, 20, 30, 50, 100];
                    $priceOptions = [199000, 299000, 399000, 499000, 599000, 699000, 799000, 999000, 1499000, 1999000];
                @endphp
                <div class="cms-form-grid">
                    <label class="cms-field"><span class="cms-label">Hạn mức gói Free</span>
                        <select class="cms-select" wire:model="settings.packages.free_daily_quota">
                            @foreach ($quotaOptions as $q)<option value="{{ $q }}">{{ $q }} tin/ngày</option>@endforeach
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Thanh toán online</span>
                        <select class="cms-select" wire:model="settings.packages.online_payment_enabled">
                            <option value="0">Tắt - liên hệ Zalo</option>
                            <option value="1">Bật</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Gói 1 - hạn mức</span>
                        <select class="cms-select" wire:model="settings.packages.tier_30_quota">
                            @foreach ($quotaOptions as $q)<option value="{{ $q }}">{{ $q }} tin/ngày</option>@endforeach
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Gói 1 - giá / tháng</span>
                        <select class="cms-select" wire:model="settings.packages.tier_30_price">
                            @foreach ($priceOptions as $p)<option value="{{ $p }}">{{ number_format($p, 0, ',', '.') }}đ</option>@endforeach
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Gói 2 - hạn mức</span>
                        <select class="cms-select" wire:model="settings.packages.tier_50_quota">
                            @foreach ($quotaOptions as $q)<option value="{{ $q }}">{{ $q }} tin/ngày</option>@endforeach
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Gói 2 - giá / tháng</span>
                        <select class="cms-select" wire:model="settings.packages.tier_50_price">
                            @foreach ($priceOptions as $p)<option value="{{ $p }}">{{ number_format($p, 0, ',', '.') }}đ</option>@endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Watermark ảnh</h2>
                    <span style="color: var(--text-secondary)">Tự động chèn khi upload ảnh tin</span>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field"><span class="cms-label">Bật watermark</span>
                        <select class="cms-select" wire:model="settings.watermark.enabled">
                            <option value="1">Bật</option>
                            <option value="0">Tắt</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Vị trí</span>
                        <select class="cms-select" wire:model="settings.watermark.position">
                            <option value="bottom-right">Dưới phải</option>
                            <option value="bottom-left">Dưới trái</option>
                            <option value="top-right">Trên phải</option>
                            <option value="top-left">Trên trái</option>
                            <option value="center">Giữa ảnh</option>
                        </select>
                    </label>
                    <label class="cms-field full"><span class="cms-label">Chữ watermark</span><input class="cms-input" wire:model="settings.watermark.text"></label>
                    <label class="cms-field"><span class="cms-label">Độ đậm chữ</span>
                        <select class="cms-select" wire:model="settings.watermark.opacity">
                            <option value="25">Nhạt</option>
                            <option value="40">Hơi nhạt</option>
                            <option value="55">Vừa</option>
                            <option value="70">Đậm</option>
                            <option value="90">Rất đậm</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Cỡ chữ</span>
                        <select class="cms-select" wire:model="settings.watermark.font_size">
                            <option value="16">Nhỏ</option>
                            <option value="22">Vừa</option>
                            <option value="32">Lớn</option>
                            <option value="44">Rất lớn</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Màu chữ</span>
                        <select class="cms-select" wire:model="settings.watermark.color">
                            <option value="#FFFFFF">Trắng</option>
                            <option value="#000000">Đen</option>
                            <option value="#FFC21C">Vàng</option>
                            <option value="#07366B">Xanh navy</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Khoảng cách viền</span>
                        <select class="cms-select" wire:model="settings.watermark.margin">
                            <option value="8">Sát viền</option>
                            <option value="16">Vừa</option>
                            <option value="24">Xa</option>
                            <option value="32">Rất xa</option>
                        </select>
                    </label>
                </div>

                <div class="cms-panel-head"><h3 class="cms-panel-title">Giới hạn upload ảnh</h3></div>
                <div class="cms-form-grid">
                    <label class="cms-field"><span class="cms-label">Dung lượng tối đa mỗi ảnh</span>
                        <select class="cms-select" wire:model="settings.upload.max_size_mb">
                            <option value="2">2 MB</option>
                            <option value="3">3 MB</option>
                            <option value="5">5 MB</option>
                            <option value="8">8 MB</option>
                            <option value="10">10 MB</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Số ảnh tối đa mỗi tin</span>
                        <select class="cms-select" wire:model="settings.upload.max_count">
                            <option value="10">10 ảnh</option>
                            <option value="15">15 ảnh</option>
                            <option value="20">20 ảnh</option>
                            <option value="30">30 ảnh</option>
                            <option value="40">40 ảnh</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Chất lượng ảnh sau nén</span>
                        <select class="cms-select" wire:model="settings.upload.compress_quality">
                            <option value="60">Tiết kiệm dung lượng</option>
                            <option value="80">Cân bằng</option>
                            <option value="90">Nét cao</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Kích thước ảnh tối đa</span>
                        <select class="cms-select" wire:model="settings.upload.max_dimension">
                            <option value="1280">HD - 1280px</option>
                            <option value="1600">1600px</option>
                            <option value="1920">Full HD - 1920px</option>
                            <option value="2560">2K - 2560px</option>
                        </select>
                    </label>
                </div>

                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn primary" wire:click="saveSettings"><i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình</button>
                </div>
            </section>
        </div>

        <div class="cms-grid-2" style="margin-top:12px;">
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Nhận diện thương hiệu</h2>
                    <span style="color: var(--text-secondary)">Logo &amp; favicon hiển thị trên website</span>
                </div>
                <div class="cms-form-grid">
                    <x-cms-media-field label="Logo" :value="$settings['branding']['logo'] ?? ''" target="settings.branding.logo" />
                    <x-cms-media-field label="Logo nền tối (tùy chọn)" :value="$settings['branding']['logo_dark'] ?? ''" target="settings.branding.logo_dark" />
                    <x-cms-media-field label="Favicon" :value="$settings['branding']['favicon'] ?? ''" target="settings.branding.favicon" />
                    <label class="cms-field full"><span class="cms-label">Slogan / Tagline</span><input class="cms-input" wire:model="settings.branding.tagline"></label>
                </div>
            </section>

            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">SEO &amp; chia sẻ</h2>
                    <span style="color: var(--text-secondary)">Tiêu đề, mô tả, ảnh share, tracking</span>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full"><span class="cms-label">Tiêu đề mặc định (trang chủ)</span><input class="cms-input" wire:model="settings.seo.default_title"></label>
                    <label class="cms-field full"><span class="cms-label">Mẫu tiêu đề (dùng %s cho tên trang)</span><input class="cms-input mono" wire:model="settings.seo.title_template" placeholder="%s | BDS Việt"></label>
                    <label class="cms-field full"><span class="cms-label">Mô tả mặc định</span><textarea class="cms-textarea" style="min-height:64px" wire:model="settings.seo.default_description"></textarea></label>
                    <label class="cms-field full"><span class="cms-label">Từ khóa (cách nhau dấu phẩy)</span><input class="cms-input" wire:model="settings.seo.keywords"></label>
                    <x-cms-media-field label="Ảnh chia sẻ mạng xã hội (OG image)" :value="$settings['seo']['og_image'] ?? ''" target="settings.seo.og_image" />
                    <label class="cms-field"><span class="cms-label">Cho phép Google lập chỉ mục</span>
                        <select class="cms-select" wire:model="settings.seo.robots_index">
                            <option value="1">Có (index)</option>
                            <option value="0">Không (noindex)</option>
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Tên miền chuẩn (canonical)</span><input class="cms-input mono" wire:model="settings.seo.canonical_base" placeholder="https://..."></label>
                    <label class="cms-field full"><span class="cms-label">Google Site Verification</span><input class="cms-input mono" wire:model="settings.seo.google_site_verification"></label>
                    <label class="cms-field"><span class="cms-label">Facebook App ID</span><input class="cms-input mono" wire:model="settings.seo.facebook_app_id"></label>
                    <label class="cms-field"><span class="cms-label">Twitter/X handle</span><input class="cms-input mono" wire:model="settings.seo.twitter_handle" placeholder="@bdsviet"></label>
                    <label class="cms-field full"><span class="cms-label">Mã Analytics (GA4 G-... hoặc GTM-...)</span><input class="cms-input mono" wire:model="settings.seo.analytics_id"></label>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn primary" wire:click="saveSettings"><i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình</button>
                </div>
            </section>
        </div>
    @endif

    @if ($showAccountModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $accountEditingId ? 'Sửa tài khoản' : 'Thêm tài khoản' }}</h2>
                    <button class="cms-icon-btn" wire:click="closeAccountModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field"><span class="cms-label">Họ và tên *</span><input class="cms-input" wire:model="accountName"></label>
                    <label class="cms-field"><span class="cms-label">Số điện thoại *</span><input class="cms-input mono" wire:model="accountPhone"></label>
                    <label class="cms-field"><span class="cms-label">Vai trò</span><select class="cms-select" wire:model="accountRoleValue"><option value="buyer">Người đăng tin</option><option value="ctv">Cộng tác viên</option><option value="admin">Quản trị viên</option></select></label>
                    <label class="cms-field"><span class="cms-label">PIN xem SĐT</span><input class="cms-input mono" wire:model="accountViewPhonePin"></label>
                    <label class="cms-field full">
                        <span class="cms-label">Người mời</span>
                        <select class="cms-select" wire:model="accountInviterUserId">
                            <option value="">Tài khoản gốc, không có người mời</option>
                            @foreach ($accountInviters as $inviter)
                                <option value="{{ $inviter->id }}">{{ $inviter->name }}{{ $inviter->phone ? ' - ' . $inviter->phone : '' }}{{ $inviter->invite_code ? ' - Mã: ' . $inviter->invite_code : ' - chưa có mã' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="cms-field full">
                        <span class="cms-label">Mã mời tài khoản</span>
                        @if ($accountEditingId && $accountExistingInviteCode)
                            <input class="cms-input mono" value="{{ $accountExistingInviteCode }}" disabled>
                        @else
                            <input class="cms-input mono" wire:model="accountRootInviteCode" placeholder="Nhập mã gốc nếu không chọn người mời">
                        @endif
                    </label>
                    <div class="cms-field full">
                        <span class="cms-label">Loại BĐS được phân công</span>
                        <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px; max-height:180px; overflow:auto; border:1px solid var(--border); padding:8px; background:var(--bg-raised);">
                            @foreach ($propertyTypeOptions as $id => $name)
                                <label style="display:flex; align-items:center; gap:6px; color:var(--text-secondary);">
                                    <input type="checkbox" wire:model="accountPropertyTypes" value="{{ $id }}">
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div style="color:var(--text-muted); font-size:11px;">Dùng cho CTV/nhân sự nội bộ. Người đăng tin thường có thể để trống nếu không cần giới hạn.</div>
                    </div>
                    @if ($errors->any())
                        <div class="cms-field full" style="color:var(--danger); font-size:12px;">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeAccountModal">Hủy</button>
                    <button class="cms-btn primary" wire:click="saveAccount">Lưu tài khoản</button>
                </div>
            </section>
        </div>
    @endif

    @if ($showAccountDeleteModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal" style="width: min(480px, calc(100vw - 48px));">
                <div class="cms-panel-head"><h2 class="cms-panel-title">Xác nhận xóa tài khoản</h2></div>
                <div style="padding:14px; color:var(--text-secondary);">
                    Hành động này sẽ xóa tài khoản người dùng khỏi hệ thống. Các dữ liệu liên quan có thể bị ảnh hưởng theo ràng buộc cơ sở dữ liệu.
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeAccountDeleteModal">Hủy</button>
                    <button class="cms-btn danger" wire:click="deleteAccount">Xóa tài khoản</button>
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
                    <label class="cms-field"><span class="cms-label">Danh mục</span>
                        <select class="cms-select" wire:model="homeSectionCategoryId">
                            <option value="">— Tất cả danh mục —</option>
                            @foreach($categoryOptions as $opt)<option value="{{ $opt->id }}">{{ $opt->name }}</option>@endforeach
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Tỉnh/Thành</span>
                        <select class="cms-select" wire:model="homeSectionProvinceName">
                            <option value="">— Tất cả tỉnh/thành —</option>
                            @foreach($provinceOptions as $province)<option value="{{ $province }}">{{ $province }}</option>@endforeach
                        </select>
                    </label>
                    <label class="cms-field"><span class="cms-label">Sắp xếp theo</span><select class="cms-select" wire:model="homeSectionSortBy"><option value="created_at">Ngày đăng</option><option value="price">Giá</option><option value="area">Diện tích</option><option value="view_count">Lượt xem</option></select></label>
                    <label class="cms-field"><span class="cms-label">Chiều sắp xếp</span><select class="cms-select" wire:model="homeSectionSortOrder"><option value="desc">Giảm dần</option><option value="asc">Tăng dần</option></select></label>
                    <label class="cms-field"><span class="cms-label">Số tin hiển thị</span>
                        <select class="cms-select" wire:model="homeSectionLimit">
                            @foreach([4, 6, 8, 10, 12, 16, 20, 24] as $n)<option value="{{ $n }}">{{ $n }} tin</option>@endforeach
                        </select>
                    </label>
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
                    <label class="cms-field"><span class="cms-label">Loại BĐS</span>
                        <select class="cms-select" wire:model="categoryPropertyType">
                            <option value="">— Không —</option>
                            @foreach($propertyKindLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </label>
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
                    <div class="cms-field full" wire:ignore wire:key="blog-editor-{{ $blogEditingId ?? 'new' }}">
                        <span class="cms-label">Nội dung *</span>
                        <div x-data="{
                                editor: null,
                                init() {
                                    if (typeof ClassicEditor === 'undefined') { return; }
                                    ClassicEditor.create(this.$refs.ed).then((editor) => {
                                        this.editor = editor;
                                        editor.setData(@js($blogContent ?? ''));
                                        let t;
                                        editor.model.document.on('change:data', () => {
                                            clearTimeout(t);
                                            t = setTimeout(() => $wire.set('blogContent', editor.getData(), false), 300);
                                        });
                                    }).catch((e) => console.error('CKEditor:', e));
                                },
                                destroy() { if (this.editor) { this.editor.destroy().catch(() => {}); this.editor = null; } }
                            }">
                            <textarea x-ref="ed"></textarea>
                        </div>
                    </div>
                    <x-cms-media-field label="Ảnh đại diện" :value="$blogCoverImage" target="blogCoverImage" />
                    <label class="cms-field"><span class="cms-label">Tác giả</span><input class="cms-input" wire:model="blogAuthorName"></label>
                    <label class="cms-field"><span class="cms-label">Tag chính</span>
                        <select class="cms-select" wire:model="blogCategoryTag">
                            @foreach(['Tin tức', 'Hướng dẫn', 'Phân tích thị trường', 'Kinh nghiệm', 'Pháp lý', 'Phong thủy', 'Dự án'] as $tag)<option value="{{ $tag }}">{{ $tag }}</option>@endforeach
                        </select>
                    </label>
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

    @if ($showReportModal && $selectedReport)
        @php
            $reasonLabels = \App\Models\ListingReport::REASONS;
        @endphp
        <div class="cms-modal-backdrop">
            <section class="cms-modal" style="width: min(640px, calc(100vw - 48px));">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Xử lý báo cáo vi phạm</h2>
                    <button class="cms-icon-btn" wire:click="closeReportModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <div class="cms-field"><span class="cms-label">Đối tượng</span><div style="color:var(--text-primary); font-weight:700">{{ $selectedReport->target_type === 'listing' ? 'Bài đăng' : 'Tài khoản' }}</div></div>
                    <div class="cms-field"><span class="cms-label">Lý do</span><div>{{ $reasonLabels[$selectedReport->reason] ?? $selectedReport->reason }}</div></div>

                    @if ($selectedReport->target_type === 'listing')
                        <div class="cms-field full"><span class="cms-label">Tin bị báo cáo</span><div style="color:var(--text-primary)">{{ $selectedReport->listing?->title ?: 'Tin đã xóa' }} <span class="mono" style="color:var(--text-muted)">{{ $selectedReport->listing?->code ? '· ' . $selectedReport->listing->code : '' }}</span></div></div>
                    @else
                        <div class="cms-field full"><span class="cms-label">Tài khoản bị báo cáo</span><div style="color:var(--text-primary)">{{ $selectedReport->reportedUser?->name ?: '-' }} <span class="mono" style="color:var(--text-muted)">{{ $selectedReport->reportedUser?->phone ?: '' }}</span></div></div>
                    @endif

                    <div class="cms-field full"><span class="cms-label">Nội dung báo cáo</span><div style="color:var(--text-secondary)">{{ $selectedReport->detail ?: 'Không có mô tả thêm.' }}</div></div>
                    <div class="cms-field"><span class="cms-label">Người báo cáo</span><div>{{ $selectedReport->reporter?->name ?: $selectedReport->reporter_name ?: 'Ẩn danh' }}</div></div>
                    <div class="cms-field"><span class="cms-label">Liên hệ</span><div class="mono">{{ $selectedReport->reporter?->phone ?: $selectedReport->reporter_phone ?: '-' }}</div></div>

                    <label class="cms-field full">
                        <span class="cms-label">Lý do phản hồi (hiển thị cho người dùng) *</span>
                        <textarea class="cms-textarea" wire:model="reportAdminReason" placeholder="Gỡ: nêu rõ bài vi phạm điều gì. Giữ: giải thích vì sao không gỡ."></textarea>
                    </label>
                    @error('reportAdminReason') <div class="cms-field full" style="color:var(--danger); font-size:12px;">{{ $message }}</div> @enderror

                    <div class="cms-field full" style="color:var(--text-muted); font-size:11px;">
                        “Gỡ bài” sẽ chuyển tin sang trạng thái <strong>Từ chối</strong> kèm lý do và hiển thị ở mục bị từ chối của người đăng. “Giữ bài” lưu phản hồi gửi tới người báo cáo.
                    </div>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeReportModal">Hủy</button>
                    <button class="cms-btn success" wire:click="resolveReport('keep')">Giữ bài</button>
                    <button class="cms-btn danger" wire:click="resolveReport('remove')">Gỡ bài</button>
                </div>
            </section>
        </div>
    @endif

    @if ($showListingModal)
        @php
            $listingProvinces = \App\Livewire\RealEstateListing::PROVINCES;
            $listingDirections = \App\Livewire\RealEstateListing::DIRECTIONS;
        @endphp
        <div class="cms-modal-backdrop">
            <section class="cms-modal" style="width: min(960px, calc(100vw - 48px));">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $listingFormId ? 'Sửa tin đăng' : 'Thêm tin đăng' }}</h2>
                    <button class="cms-icon-btn" wire:click="closeListingModal"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="cms-scrollbar" style="max-height:72vh; overflow:auto; padding:2px 4px 2px 2px;">
                    {{-- Thông tin cơ bản --}}
                    <div class="cms-label" style="margin:4px 0 8px; color:var(--text-primary); font-weight:700;">Thông tin cơ bản</div>
                    <div class="cms-form-grid">
                        <label class="cms-field full"><span class="cms-label">Tiêu đề *</span>
                            <input class="cms-input" wire:model="listingTitle" placeholder="VD: Bán nhà mặt tiền Quận 1...">
                            @error('listingTitle') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                        </label>
                        <label class="cms-field"><span class="cms-label">Loại giao dịch</span>
                            <select class="cms-select" wire:model.live="listingType">
                                <option value="Cần bán">Cần bán</option>
                                <option value="Cho thuê">Cho thuê</option>
                                <option value="Cần mua">Cần mua</option>
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">Loại BĐS</span>
                            <select class="cms-select" wire:model.live="listingPropertyType">
                                @foreach ($propertyTypeOptions as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">Mã tin</span><input class="cms-input mono" wire:model="listingCode" placeholder="Tự sinh theo loại BĐS"></label>
                        <label class="cms-field"><span class="cms-label">Liên hệ</span>
                            <select class="cms-select" wire:model="listingContactType">
                                <option value="">Chọn loại liên hệ</option>
                                <option value="Chủ">Chủ</option>
                                <option value="Môi giới">Môi giới</option>
                                <option value="Công ty">Công ty</option>
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">SĐT liên hệ</span><input class="cms-input mono" wire:model="listingContactPhone" placeholder="090..."></label>
                        <label class="cms-field"><span class="cms-label">Mật khẩu nhà</span><input class="cms-input mono" wire:model="listingHousePassword"></label>
                        <label class="cms-field"><span class="cms-label">Trạng thái</span>
                            <select class="cms-select" wire:model="listingState">
                                <option value="pending">Chờ duyệt</option>
                                <option value="active">Đã đăng</option>
                                <option value="expired">Hết hạn</option>
                                <option value="rejected">Từ chối</option>
                                <option value="sold">Đã giao dịch</option>
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">Ưu tiên</span>
                            <select class="cms-select" wire:model="listingTier">
                                <option value="normal">Thường</option>
                                <option value="vip1">Ưu tiên 1</option>
                                <option value="vip2">Ưu tiên 2</option>
                                <option value="vip3">Ưu tiên 3</option>
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">Người đưa tin</span>
                            <select class="cms-select" wire:model="listingReporterId">
                                <option value="">— Không —</option>
                                @foreach ($accountInviters as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}{{ $u->phone ? ' — ' . $u->phone : '' }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    {{-- Vị trí --}}
                    <div class="cms-label" style="margin:16px 0 8px; color:var(--text-primary); font-weight:700;">Vị trí</div>
                    <div class="cms-form-grid">
                        <label class="cms-field"><span class="cms-label">Tỉnh / Thành {{ $listingType !== 'Cần mua' ? '*' : '' }}</span>
                            <select class="cms-select" wire:model.live="listingProvinceId">
                                <option value="">Chọn tỉnh/thành</option>
                                @foreach ($listingProvinces as $pid => $pname)
                                    <option value="{{ $pid }}">{{ $pname }}</option>
                                @endforeach
                            </select>
                            @error('listingProvinceId') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                        </label>
                        <label class="cms-field"><span class="cms-label">Quận / Huyện</span>
                            <select class="cms-select" wire:model.live="listingDistrictId" @disabled(empty($listingDistricts))>
                                <option value="">Chọn quận/huyện</option>
                                @foreach ($listingDistricts as $did => $dname)
                                    <option value="{{ $did }}">{{ $dname }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">Phường / Xã</span>
                            <select class="cms-select" wire:model="listingWardId" @disabled(empty($listingWards))>
                                <option value="">Chọn phường/xã</option>
                                @foreach ($listingWards as $wid => $wname)
                                    <option value="{{ $wid }}">{{ $wname }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="cms-field full"><span class="cms-label">Địa chỉ</span><input class="cms-input" wire:model="listingAddress" placeholder="Số nhà, tên đường..."></label>
                    </div>

                    {{-- Chi tiết --}}
                    <div class="cms-label" style="margin:16px 0 8px; color:var(--text-primary); font-weight:700;">Chi tiết</div>
                    <div class="cms-form-grid">
                        <label class="cms-field"><span class="cms-label">Diện tích (m²)</span><input class="cms-input mono" wire:model="listingArea" placeholder="VD: 80"></label>
                        <label class="cms-field"><span class="cms-label">Giá {{ $listingType !== 'Cần mua' ? '*' : '' }}</span>
                            <input class="cms-input mono" wire:model="listingPrice" placeholder="VD: 3.150.000.000">
                            @error('listingPrice') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                        </label>
                        <label class="cms-field"><span class="cms-label">Đơn vị giá</span>
                            <select class="cms-select" wire:model="listingPriceUnit">
                                <option value="1">VNĐ</option>
                                <option value="2">VNĐ/tháng</option>
                                <option value="3">VNĐ/m²</option>
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">Số tầng</span><input class="cms-input mono" type="number" wire:model="listingFloors"></label>
                        <label class="cms-field"><span class="cms-label">Phòng ngủ</span><input class="cms-input mono" type="number" wire:model="listingBedrooms"></label>
                        <label class="cms-field"><span class="cms-label">Toilet</span><input class="cms-input mono" type="number" wire:model="listingToilets"></label>
                        <label class="cms-field"><span class="cms-label">Hướng</span>
                            <select class="cms-select" wire:model="listingDirection">
                                <option value="">Chọn hướng nhà</option>
                                @foreach ($listingDirections as $dirId => $dirName)
                                    <option value="{{ $dirId }}">{{ $dirName }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="cms-field"><span class="cms-label">Mặt tiền (m)</span><input class="cms-input mono" wire:model="listingFrontWidth"></label>
                        <label class="cms-field"><span class="cms-label">Đường (m)</span><input class="cms-input mono" wire:model="listingRoadWidth"></label>
                    </div>

                    {{-- Hình ảnh --}}
                    <div class="cms-label" style="margin:16px 0 8px; color:var(--text-primary); font-weight:700;">Hình ảnh</div>
                    <div class="cms-form-grid">
                        <x-cms-media-field label="Ảnh đại diện" :value="$listingAvatar" target="listingAvatar" :full="false" />
                        <div class="cms-field">
                            <span class="cms-label">Thư viện ảnh ({{ count($listingImages) }})</span>
                            <button type="button" class="cms-btn" wire:click="openMediaPicker('listingImages')"><i class="fa-solid fa-images"></i> Thêm / chọn ảnh</button>
                        </div>
                        <div class="cms-field full">
                            @if (count($listingImages))
                                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                    @foreach ($listingImages as $i => $img)
                                        <div wire:key="limg-{{ $i }}-{{ md5($img) }}" style="position:relative; width:96px; height:96px; border:1px solid {{ $listingAvatar === $img ? 'var(--success)' : 'var(--border)' }}; background:var(--bg-raised); overflow:hidden;">
                                            <img src="{{ $img }}" alt="" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
                                            @if ($listingAvatar === $img)
                                                <span class="cms-badge success" style="position:absolute; top:2px; left:2px; font-size:9px;">Đại diện</span>
                                            @endif
                                            <div style="position:absolute; bottom:2px; right:2px; display:flex; gap:3px;">
                                                <button type="button" class="cms-icon-btn" title="Đặt làm ảnh đại diện" wire:click="setListingAvatarFromImage({{ $i }})" style="width:22px; height:22px; background:rgba(0,0,0,.55); color:#fff;"><i class="fa-solid fa-star" style="font-size:10px;"></i></button>
                                                <button type="button" class="cms-icon-btn" title="Xóa ảnh" wire:click="removeListingImage({{ $i }})" style="width:22px; height:22px; background:rgba(220,38,38,.85); color:#fff;"><i class="fa-solid fa-xmark" style="font-size:10px;"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:var(--text-muted); font-size:12px;">Chưa có ảnh nào. Bấm "Thêm / chọn ảnh" để tải lên hoặc chọn từ thư viện.</span>
                            @endif
                        </div>
                    </div>

                    {{-- Liên kết & mô tả --}}
                    <div class="cms-label" style="margin:16px 0 8px; color:var(--text-primary); font-weight:700;">Liên kết & mô tả</div>
                    <div class="cms-form-grid">
                        <label class="cms-field"><span class="cms-label">Youtube</span><input class="cms-input mono" wire:model="listingYoutubeLink"></label>
                        <label class="cms-field"><span class="cms-label">Facebook</span>
                            <input class="cms-input mono" wire:model="listingFacebookLink">
                            @error('listingFacebookLink') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                        </label>
                        <label class="cms-field"><span class="cms-label">Facebook video</span>
                            <input class="cms-input mono" wire:model="listingFacebookVideoLink">
                            @error('listingFacebookVideoLink') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                        </label>
                        <label class="cms-field"><span class="cms-label">Google Map</span>
                            <input class="cms-input mono" wire:model="listingGoogleMapLink">
                            @error('listingGoogleMapLink') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                        </label>
                        <label class="cms-field"><span class="cms-label">Tiktok</span>
                            <input class="cms-input mono" wire:model="listingTiktokLink">
                            @error('listingTiktokLink') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                        </label>
                        <label class="cms-field full"><span class="cms-label">Mô tả</span><textarea class="cms-textarea" style="min-height:120px" wire:model="listingDescription"></textarea></label>
                    </div>
                </div>

                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button class="cms-btn" wire:click="closeListingModal">Hủy</button>
                    <button class="cms-btn primary" wire:click="saveListing" wire:loading.attr="disabled" wire:target="saveListing">
                        <span wire:loading.remove wire:target="saveListing">{{ $listingFormId ? 'Lưu thay đổi' : 'Thêm tin đăng' }}</span>
                        <span wire:loading wire:target="saveListing"><i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...</span>
                    </button>
                </div>
            </section>
        </div>
    @endif

    @if ($showMediaPicker)
        <div class="cms-modal-backdrop">
            <section class="cms-modal" style="width: min(780px, calc(100vw - 48px));">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $mediaTarget === 'listingImages' ? 'Thêm ảnh cho tin đăng' : 'Chọn ảnh từ thư viện' }}</h2>
                    <button class="cms-icon-btn" wire:click="closeMediaPicker"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div style="padding:12px;">
                    <div class="cms-field full" style="margin-bottom:12px;">
                        <span class="cms-label">Tải ảnh mới (tối đa 3MB)</span>
                        <input type="file" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" wire:model="mediaUpload" class="cms-input" style="height:auto; padding:6px;">
                        <div wire:loading wire:target="mediaUpload" style="color:var(--text-secondary); font-size:12px; margin-top:4px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải lên...</div>
                        @error('mediaUpload') <span style="color:var(--danger); font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="cms-label" style="margin-bottom:6px;">Hoặc chọn từ thư viện đã có</div>
                    <div class="cms-scrollbar" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(110px, 1fr)); gap:8px; max-height:360px; overflow:auto; padding:2px;">
                        @forelse ($mediaImages as $img)
                            <button type="button" wire:click="selectExistingMedia(@js($img['url']))" title="{{ $img['name'] }}" style="border:1px solid var(--border); background:var(--bg-raised); padding:0; cursor:pointer; aspect-ratio:1; overflow:hidden;">
                                <img src="{{ $img['url'] }}" alt="{{ $img['name'] }}" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
                            </button>
                        @empty
                            <div style="grid-column:1 / -1; text-align:center; color:var(--text-secondary); padding:24px;">Thư viện chưa có ảnh. Hãy tải ảnh mới ở trên.</div>
                        @endforelse
                    </div>
                </div>
                @if ($mediaTarget === 'listingImages')
                    <div class="cms-panel-head" style="justify-content:space-between;">
                        <span style="color:var(--text-secondary); font-size:13px;">Đã chọn {{ count($listingImages) }} ảnh cho tin đăng. Bấm ảnh để thêm.</span>
                        <button class="cms-btn primary" wire:click="closeMediaPicker">Xong</button>
                    </div>
                @endif
            </section>
        </div>
    @endif
</div>
