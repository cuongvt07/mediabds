<div class="site-cms">
    <aside class="site-cms-sidebar">
        <div class="site-cms-sidebar-head">
            <span class="site-cms-sidebar-mark"><i class="fa-solid fa-house"></i></span>
            <div>
                <strong>Nhà trọ</strong>
                <span>CMS riêng</span>
            </div>
        </div>

        <nav class="site-cms-nav-list" role="tablist" aria-label="CMS nhà trọ">
            <button type="button" role="tab" class="site-cms-nav {{ $activeTab === 'dashboard' ? 'is-active' : '' }}" wire:click="setTab('dashboard')">
                <i class="fa-solid fa-chart-simple"></i>
                <span>Dashboard</span>
            </button>
            <button type="button" role="tab" class="site-cms-nav {{ $activeTab === 'listings' ? 'is-active' : '' }}" wire:click="setTab('listings')">
                <i class="fa-regular fa-rectangle-list"></i>
                <span>Tin đăng</span>
            </button>
            <button type="button" role="tab" class="site-cms-nav {{ $activeTab === 'banners' ? 'is-active' : '' }}" wire:click="setTab('banners')">
                <i class="fa-regular fa-images"></i>
                <span>Banner slider</span>
            </button>
            <button type="button" role="tab" class="site-cms-nav {{ $activeTab === 'amenities' ? 'is-active' : '' }}" wire:click="setTab('amenities')">
                <i class="fa-solid fa-couch"></i>
                <span>Tiện ích &amp; nội thất</span>
            </button>
            <button type="button" role="tab" class="site-cms-nav {{ $activeTab === 'accounts' ? 'is-active' : '' }}" wire:click="setTab('accounts')">
                <i class="fa-solid fa-users"></i>
                <span>Tài khoản</span>
            </button>
            <button type="button" role="tab" class="site-cms-nav {{ $activeTab === 'identity' ? 'is-active' : '' }}" wire:click="setTab('identity')">
                <i class="fa-solid fa-palette"></i>
                <span>Logo</span>
            </button>
        </nav>

        <div class="site-cms-sidebar-note">
            CMS này chỉ quản lý chức năng của trang nhà trọ: tin đăng, banner và nhận diện.
        </div>
    </aside>

    <section class="site-cms-main">
        <div class="site-cms-toolbar">
            <div>
                <p class="site-cms-kicker">CMS nhà trọ</p>
                <h1>
                    @if($activeTab === 'listings')
                        Tin đăng
                    @elseif($activeTab === 'banners')
                        Banner slider
                    @elseif($activeTab === 'amenities')
                        Tiện ích & nội thất
                    @elseif($activeTab === 'accounts')
                        Tài khoản
                    @elseif($activeTab === 'identity')
                        Logo & nhận diện
                    @else
                        Dashboard
                    @endif
                </h1>
            </div>
            <div class="site-cms-toolbar-actions">
                <a class="cms-btn" href="{{ route('site.home') }}" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem trang chủ
                </a>
                @if($activeTab === 'listings')
                    <button type="button" class="cms-btn primary" wire:click="createListing">
                        <i class="fa-solid fa-plus"></i> Thêm tin
                    </button>
                @elseif($activeTab === 'banners')
                    <button type="button" class="cms-btn primary" wire:click="createBanner">
                        <i class="fa-solid fa-plus"></i> Thêm banner
                    </button>
                @elseif($activeTab === 'accounts')
                    <button type="button" class="cms-btn primary" wire:click="createAccount">
                        <i class="fa-solid fa-user-plus"></i> Thêm tài khoản
                    </button>
                @elseif($activeTab === 'amenities')
                    <button type="button" class="cms-btn primary" wire:click="createAmenity">
                        <i class="fa-solid fa-plus"></i> Thêm tiện ích
                    </button>
                @endif
            </div>
        </div>

        @if($activeTab === 'dashboard')
            <section class="site-cms-stat-grid">
                <article class="site-cms-stat">
                    <span class="site-cms-stat-icon"><i class="fa-regular fa-rectangle-list"></i></span>
                    <small>Tổng tin đăng</small>
                    <strong>{{ number_format($listingCount) }}</strong>
                    <em>Tin phòng trọ trong CMS này.</em>
                </article>
                <article class="site-cms-stat">
                    <span class="site-cms-stat-icon"><i class="fa-solid fa-door-open"></i></span>
                    <small>Tin đang hiển thị</small>
                    <strong>{{ number_format($availableListingCount) }}</strong>
                    <em>Chưa giao dịch và đang bật.</em>
                </article>
                <article class="site-cms-stat">
                    <span class="site-cms-stat-icon"><i class="fa-regular fa-images"></i></span>
                    <small>Banner slider</small>
                    <strong>{{ number_format($activeBannerCount) }}/{{ number_format($bannerCount) }}</strong>
                    <em>Đang bật / tổng banner.</em>
                </article>
                <article class="site-cms-stat">
                    <span class="site-cms-stat-icon"><i class="fa-solid fa-house"></i></span>
                    <small>Logo</small>
                    <strong>{{ $logoUrl ? 'Đã có' : 'Chưa có' }}</strong>
                    <em>Upload local storage.</em>
                </article>
            </section>

            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Chức năng</h2>
                </div>
                <div class="site-cms-action-grid">
                    <button type="button" class="site-cms-action" wire:click="setTab('listings')">
                        <span><i class="fa-regular fa-rectangle-list"></i></span>
                        <strong>Tin đăng</strong>
                        <em>CRUD tin phòng trọ riêng trong CMS này.</em>
                    </button>
                    <button type="button" class="site-cms-action" wire:click="setTab('banners')">
                        <span><i class="fa-regular fa-images"></i></span>
                        <strong>Banner slider</strong>
                        <em>Quản lý banner riêng, không bị filter đè.</em>
                    </button>
                    <button type="button" class="site-cms-action" wire:click="setTab('identity')">
                        <span><i class="fa-solid fa-palette"></i></span>
                        <strong>Logo & nhận diện</strong>
                        <em>Đổi logo và tên hiển thị ngoài trang chủ.</em>
                    </button>
                </div>
            </section>
        @elseif($activeTab === 'listings')
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Quản lý tin đăng</h2>
                    <div class="site-cms-inline-actions">
                        <input class="cms-input site-cms-search" wire:model.live.debounce.350ms="listingSearch" placeholder="Tìm tiêu đề, mã tin, SĐT, quận/phường">
                        <button type="button" class="cms-btn primary" wire:click="createListing">
                            <i class="fa-solid fa-plus"></i> Thêm tin
                        </button>
                    </div>
                </div>
                <div class="site-cms-seg" style="margin-bottom:14px;">
                    <button type="button" class="site-cms-seg-btn {{ $listingModeration === 'all' ? 'is-active' : '' }}" wire:click="setListingModeration('all')">Tất cả <span>{{ $moderationCounts['all'] }}</span></button>
                    <button type="button" class="site-cms-seg-btn {{ $listingModeration === 'pending' ? 'is-active' : '' }}" wire:click="setListingModeration('pending')">Chờ duyệt <span>{{ $moderationCounts['pending'] }}</span></button>
                    <button type="button" class="site-cms-seg-btn {{ $listingModeration === 'approved' ? 'is-active' : '' }}" wire:click="setListingModeration('approved')">Đã duyệt <span>{{ $moderationCounts['approved'] }}</span></button>
                    <button type="button" class="site-cms-seg-btn {{ $listingModeration === 'rejected' ? 'is-active' : '' }}" wire:click="setListingModeration('rejected')">Từ chối <span>{{ $moderationCounts['rejected'] }}</span></button>
                </div>
                <div class="cms-table-wrap cms-scrollbar">
                    <table class="cms-table">
                        <thead>
                            <tr>
                                <th style="width:90px;">Hiển thị</th>
                                <th style="width:130px;">Duyệt</th>
                                <th style="width:120px;">Ảnh</th>
                                <th>Tin phòng</th>
                                <th style="width:120px;">Giá</th>
                                <th style="width:110px;" class="right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siteListings as $listing)
                                @php($mod = $listing->moderation_status ?: 'approved')
                                <tr style="{{ $listing->is_sold ? 'opacity:.58' : '' }}">
                                    <td>
                                        <button type="button" class="cms-badge {{ $listing->is_sold ? 'muted' : 'success' }}" wire:click="toggleListing({{ $listing->id }})" title="Hiện/ẩn tin">
                                            <i class="fa-solid {{ $listing->is_sold ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i> {{ $listing->is_sold ? 'Ẩn' : 'Hiện' }}
                                        </button>
                                    </td>
                                    <td>
                                        <select class="cms-mod-select {{ $mod === 'approved' ? 'ok' : ($mod === 'pending' ? 'warn' : 'err') }}"
                                            wire:change="setListingMod({{ $listing->id }}, $event.target.value)"
                                            @if($mod === 'rejected' && $listing->rejection_reason) title="Lý do: {{ $listing->rejection_reason }}" @endif>
                                            <option value="pending"  {{ $mod === 'pending'  ? 'selected' : '' }}>⏳ Chờ duyệt</option>
                                            <option value="approved" {{ $mod === 'approved' ? 'selected' : '' }}>✓ Đã duyệt</option>
                                            <option value="rejected" {{ $mod === 'rejected' ? 'selected' : '' }}>✗ Từ chối</option>
                                        </select>
                                    </td>
                                    <td>
                                        @php($cover = $listing->avatar ?: collect($listing->images ?: [])->first())
                                        @if($cover)
                                            <img src="{{ $cover }}" alt="{{ $listing->title }}" class="site-cms-listing-cover">
                                        @else
                                            <div class="site-cms-empty-cover"><i class="fa-regular fa-image"></i></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="cms-truncate" style="color:var(--text-primary);font-weight:900;">{{ $listing->title }}</div>
                                        <div class="cms-truncate">{{ $listing->ward_name ?: '-' }}, {{ $listing->district_name ?: '-' }}</div>
                                        <div class="cms-truncate mono" style="font-size:11px;color:var(--text-muted);">
                                            {{ $listing->code ?: '#NT' }} · {{ $roomTypes[$listing->room_type] ?? $listing->room_type }}
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $listing->price ? number_format((float) $listing->price, 0, ',', '.') : '-' }}</strong>
                                        <span style="color:var(--text-muted);">/tháng</span>
                                    </td>
                                    <td class="right">
                                        <div class="cms-row-actions">
                                            <a class="cms-act" href="{{ route('site.listings.show', $listing) }}" target="_blank" title="Xem tin" aria-label="Xem tin"><i class="fa-regular fa-eye"></i></a>
                                            <button type="button" class="cms-act" wire:click="editListing({{ $listing->id }})" title="Sửa tin" aria-label="Sửa tin"><i class="fa-solid fa-pen"></i></button>
                                            <button type="button" class="cms-act danger" wire:click="deleteListing({{ $listing->id }})" wire:confirm="Xóa tin đăng này?" title="Xóa tin" aria-label="Xóa tin"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="height:72px;text-align:center;">Chưa có tin phòng trọ.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="cms-pagination">{{ $siteListings->links(data: ['scrollTo' => false]) }}</div>
            </section>
        @elseif($activeTab === 'identity')
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Logo & nhận diện</h2>
                    <button type="button" class="cms-btn primary" wire:click="saveSiteIdentity">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu logo
                    </button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field">
                        <span class="cms-label">Tên site</span>
                        <input class="cms-input" wire:model="siteName">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Upload logo local</span>
                        <input class="cms-input" type="file" wire:model="logoFile" accept="image/*">
                        @error('logoFile') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Số điện thoại liên hệ</span>
                        <input class="cms-input mono" wire:model="contactPhone" placeholder="VD: 0981847977">
                        @error('contactPhone') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Zalo (số hoặc link)</span>
                        <input class="cms-input" wire:model="contactZalo" placeholder="VD: 0981847977 hoặc https://zalo.me/...">
                        @error('contactZalo') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Email liên hệ</span>
                        <input class="cms-input" wire:model="contactEmail" placeholder="VD: lienhe@nhatrosv.com">
                        @error('contactEmail') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Link Facebook</span>
                        <input class="cms-input" wire:model="contactFacebook" placeholder="https://facebook.com/...">
                        @error('contactFacebook') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Vị trí nút liên hệ nổi</span>
                        <select class="cms-select" wire:model="contactPosition">
                            <option value="right">Góc dưới bên phải</option>
                            <option value="left">Góc dưới bên trái</option>
                        </select>
                    </label>
                    <div class="cms-field full">
                        <span class="cms-label">Preview</span>
                        <div class="site-cms-logo-preview">
                            @if($logoFile)
                                <img src="{{ $logoFile->temporaryUrl() }}" alt="Logo preview">
                            @elseif($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo hiện tại">
                            @else
                                <span><i class="fa-solid fa-house"></i></span>
                            @endif
                            <strong>{{ $siteName }}</strong>
                            <em>Ảnh được lưu vào local storage.</em>
                        </div>
                    </div>
                </div>
            </section>
        @elseif($activeTab === 'banners')
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Banner slider</h2>
                    <button type="button" class="cms-btn primary" wire:click="createBanner">
                        <i class="fa-solid fa-plus"></i> Thêm banner
                    </button>
                </div>
                <div class="site-cms-panel-note">
                    Banner này nằm riêng cho trang chủ nhà trọ, không nằm chung bộ lọc và không bị filter đè.
                </div>
                <div class="cms-table-wrap cms-scrollbar">
                    <table class="cms-table">
                        <thead>
                            <tr>
                                <th style="width:86px;">Trạng thái</th>
                                <th style="width:170px;">Ảnh</th>
                                <th>Thông tin</th>
                                <th style="width:90px;" class="right">Thứ tự</th>
                                <th style="width:100px;" class="right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                                <tr style="{{ $banner->is_active ? '' : 'opacity:.58' }}">
                                    <td>
                                        <button type="button" class="cms-badge {{ $banner->is_active ? 'success' : 'muted' }}" wire:click="toggleBanner({{ $banner->id }})" title="Bật/tắt banner">
                                            <i class="fa-solid {{ $banner->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i> {{ $banner->is_active ? 'Bật' : 'Tắt' }}
                                        </button>
                                    </td>
                                    <td>
                                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?: 'Banner' }}" class="site-cms-banner-thumb">
                                    </td>
                                    <td>
                                        <div class="cms-truncate" style="color:var(--text-primary);font-weight:800;">{{ $banner->title ?: 'Không tiêu đề' }}</div>
                                        <div class="cms-truncate">{{ $banner->subtitle ?: '-' }}</div>
                                        <div class="cms-truncate mono" style="font-size:11px;color:var(--text-muted);">{{ $banner->link_url ?: $banner->image_url }}</div>
                                    </td>
                                    <td class="right mono">{{ $banner->sort_order }}</td>
                                    <td class="right">
                                        <div class="cms-row-actions">
                                            <button type="button" class="cms-act" wire:click="editBanner({{ $banner->id }})" title="Sửa banner" aria-label="Sửa banner"><i class="fa-solid fa-pen"></i></button>
                                            <button type="button" class="cms-act danger" wire:click="deleteBanner({{ $banner->id }})" wire:confirm="Xóa banner này?" title="Xóa banner" aria-label="Xóa banner"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="height:72px;text-align:center;">Chưa có banner.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="cms-pagination">{{ $banners->links(data: ['scrollTo' => false]) }}</div>
            </section>
        @elseif($activeTab === 'accounts')
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">Quản lý tài khoản</h2>
                    <div class="site-cms-inline-actions">
                        <input class="cms-input site-cms-search" wire:model.live.debounce.350ms="accountSearch" placeholder="Tìm tên, số điện thoại">
                        <button type="button" class="cms-btn primary" wire:click="createAccount">
                            <i class="fa-solid fa-user-plus"></i> Thêm tài khoản
                        </button>
                    </div>
                </div>
                <div class="site-cms-panel-note">
                    Tài khoản đăng nhập bằng số điện thoại. Tài khoản vai trò <strong>Quản trị</strong> mới vào được CMS này.
                </div>
                <div class="cms-table-wrap cms-scrollbar">
                    <table class="cms-table">
                        <thead>
                            <tr>
                                <th>Tài khoản</th>
                                <th style="width:160px;">Số điện thoại</th>
                                <th style="width:150px;">Vai trò</th>
                                <th style="width:130px;">Ngày tạo</th>
                                <th style="width:100px;" class="right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siteAccounts as $account)
                                <tr>
                                    <td>
                                        <div class="cms-truncate" style="color:var(--text-primary);font-weight:900;">{{ $account->name ?: '(Chưa đặt tên)' }}</div>
                                        <div class="cms-truncate" style="font-size:11px;color:var(--text-muted);">{{ $account->email ?: 'Không có email' }}</div>
                                    </td>
                                    <td class="mono">{{ $account->phone ?: '-' }}</td>
                                    <td>
                                        <span class="cms-badge {{ $account->isAdmin() ? 'success' : 'muted' }}">
                                            {{ $roleOptions[$account->role] ?? ($account->role ?: '-') }}
                                        </span>
                                    </td>
                                    <td class="mono" style="font-size:11px;">{{ optional($account->created_at)->format('d/m/Y') ?: '-' }}</td>
                                    <td class="right">
                                        <div class="cms-row-actions">
                                            <button type="button" class="cms-act" wire:click="editAccount({{ $account->id }})" title="Sửa tài khoản" aria-label="Sửa tài khoản"><i class="fa-solid fa-pen"></i></button>
                                            @if($account->id !== auth()->id())
                                                <button type="button" class="cms-act danger" wire:click="deleteAccount({{ $account->id }})" wire:confirm="Xóa tài khoản này?" title="Xóa tài khoản" aria-label="Xóa tài khoản"><i class="fa-solid fa-trash-can"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="height:72px;text-align:center;">Chưa có tài khoản.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="cms-pagination">{{ $siteAccounts->links(data: ['scrollTo' => false]) }}</div>
            </section>
        @elseif($activeTab === 'amenities')
            <section class="cms-panel">
                <div class="cms-panel-head">
                    <div class="site-cms-seg">
                        <button type="button" class="site-cms-seg-btn {{ $amenityType === 'amenity' ? 'is-active' : '' }}" wire:click="setAmenityType('amenity')">
                            Tiện ích <span>{{ $amenityCounts['amenity'] }}</span>
                        </button>
                        <button type="button" class="site-cms-seg-btn {{ $amenityType === 'furniture' ? 'is-active' : '' }}" wire:click="setAmenityType('furniture')">
                            Nội thất <span>{{ $amenityCounts['furniture'] }}</span>
                        </button>
                    </div>
                    <button type="button" class="cms-btn primary" wire:click="createAmenity">
                        <i class="fa-solid fa-plus"></i> Thêm {{ $amenityType === 'furniture' ? 'nội thất' : 'tiện ích' }}
                    </button>
                </div>
                <div class="site-cms-panel-note">
                    Hai danh mục <strong>riêng biệt</strong>: <strong>Tiện ích</strong> và <strong>Nội thất</strong>. Mỗi mục tải ảnh làm icon + đặt tên; người dùng lấy đúng danh sách của từng loại ngoài trang chủ.
                </div>
                <div class="cms-table-wrap cms-scrollbar">
                    <table class="cms-table">
                        <thead>
                            <tr>
                                <th style="width:90px;">Icon</th>
                                <th>Tên hiển thị</th>
                                <th style="width:170px;">Mã (key)</th>
                                <th style="width:90px;" class="right">Thứ tự</th>
                                <th style="width:96px;">Trạng thái</th>
                                <th style="width:100px;" class="right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siteAmenities as $amenity)
                                <tr style="{{ $amenity->is_active ? '' : 'opacity:.58' }}">
                                    <td>
                                        @if($amenity->icon)
                                            <img src="{{ $amenity->icon }}" alt="{{ $amenity->name }}" class="site-cms-amenity-icon">
                                        @else
                                            <div class="site-cms-empty-cover" style="width:44px;height:44px;border-radius:12px;"><i class="fa-regular fa-image"></i></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="cms-truncate" style="color:var(--text-primary);font-weight:900;">{{ $amenity->name }}</div>
                                    </td>
                                    <td class="mono" style="font-size:11px;color:var(--text-muted);">{{ $amenity->key }}</td>
                                    <td class="right mono">{{ $amenity->sort_order }}</td>
                                    <td>
                                        <button type="button" class="cms-badge {{ $amenity->is_active ? 'success' : 'muted' }}" wire:click="toggleAmenity({{ $amenity->id }})" title="Bật/tắt">
                                            <i class="fa-solid {{ $amenity->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i> {{ $amenity->is_active ? 'Hiện' : 'Ẩn' }}
                                        </button>
                                    </td>
                                    <td class="right">
                                        <div class="cms-row-actions">
                                            <button type="button" class="cms-act" wire:click="editAmenity({{ $amenity->id }})" title="Sửa" aria-label="Sửa"><i class="fa-solid fa-pen"></i></button>
                                            <button type="button" class="cms-act danger" wire:click="deleteAmenity({{ $amenity->id }})" wire:confirm="Xóa mục này?" title="Xóa" aria-label="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="height:72px;text-align:center;">Chưa có {{ $amenityType === 'furniture' ? 'nội thất' : 'tiện ích' }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="cms-pagination">{{ $siteAmenities->links(data: ['scrollTo' => false]) }}</div>
            </section>
        @endif
    </section>

    @if($showListingModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal wide">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $listingEditingId ? 'Sửa tin đăng' : 'Thêm tin đăng' }}</h2>
                    <button type="button" class="cms-icon-btn" wire:click="closeListingModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full">
                        <span class="cms-label">Tiêu đề tin đăng</span>
                        <input class="cms-input" wire:model="listingTitle" placeholder="VD: Studio giá tốt gần công viên Gia Định">
                        @error('listingTitle') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Mã tin</span>
                        <input class="cms-input mono" wire:model="listingCode" placeholder="Tự sinh nếu để trống">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Số điện thoại liên hệ</span>
                        <input class="cms-input" wire:model="listingContactPhone" placeholder="090...">
                        @error('listingContactPhone') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Tỉnh / Thành phố</span>
                        <select class="cms-select" wire:model.live="listingProvinceId">
                            <option value="">Chọn tỉnh / thành</option>
                            @foreach($provinces as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('listingProvinceId') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Quận / Huyện</span>
                        <select class="cms-select" wire:model.live="listingDistrictId">
                            <option value="">Chọn quận / huyện</option>
                            @foreach($districts as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('listingDistrictId') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Phường / Xã</span>
                        <select class="cms-select" wire:model="listingWardId">
                            <option value="">Chọn phường / xã</option>
                            @foreach($wards as $id => $name)
                                <option value="{{ $id }}">{{ is_array($name) ? ($name['name'] ?? $id) : $name }}</option>
                            @endforeach
                        </select>
                        @error('listingWardId') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Giá phòng</span>
                        <input class="cms-input" wire:model="listingPrice" placeholder="3200000 hoặc 3.200.000">
                        @error('listingPrice') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Dạng phòng</span>
                        <select class="cms-select" wire:model="listingRoomType">
                            @foreach($roomTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Nội thất</span>
                        <select class="cms-select" wire:model="listingFurnish">
                            @foreach($furnishTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Phòng ngủ</span>
                        <input class="cms-input" type="number" min="0" wire:model="listingBedrooms">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Toilet</span>
                        <input class="cms-input" type="number" min="0" wire:model="listingToilets">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Tiền điện</span>
                        <input class="cms-input" wire:model="listingElectricity" placeholder="VD: 3.500đ/kWh hoặc để trống">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Tiền nước</span>
                        <input class="cms-input" wire:model="listingWater" placeholder="VD: 100k/người">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Phí giữ xe</span>
                        <input class="cms-input" wire:model="listingParkingFee" placeholder="VD: 150k/xe/tháng">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Giờ giấc</span>
                        <input class="cms-input" wire:model="listingAccessHours" placeholder="VD: Tự do / Đóng cửa 23h">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Cửa sổ</span>
                        <select class="cms-select" wire:model="listingWindow">
                            @foreach($conditionOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Thú cưng</span>
                        <select class="cms-select" wire:model="listingPets">
                            @foreach($conditionOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Để xe / chỗ xe</span>
                        <select class="cms-select" wire:model="listingParking">
                            @foreach($conditionOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Mật khẩu nhà</span>
                        <input class="cms-input" wire:model="listingHousePassword">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Trạng thái</span>
                        <select class="cms-select" wire:model="listingIsSold">
                            <option value="0">Hiển thị</option>
                            <option value="1">Ẩn / đã giao dịch</option>
                        </select>
                    </label>
                    <div class="cms-field full">
                        <span class="cms-label">Tiện ích / nội thất chọn nhiều</span>
                        <div class="cms-checkbox-grid">
                            @foreach($amenityOptions as $value => $label)
                                <label>
                                    <input type="checkbox" wire:model="listingAmenities" value="{{ $value }}">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <label class="cms-field full">
                        <span class="cms-label">Mô tả chi tiết</span>
                        <textarea class="cms-textarea" wire:model="listingDescription" rows="5"></textarea>
                    </label>
                    <div class="cms-field full">
                        <span class="cms-label">Ảnh chính (bìa)</span>
                        <x-image-uploader name="listingAvatarFile" :images="$listingAvatar ? [$listingAvatar] : []" :previews="$listingAvatarFile ? [$listingAvatarFile] : []" on-remove="removeAvatar" :multiple="false" label="Tải ảnh chính" hint="1 ảnh đại diện — hiển thị ở thẻ tin & ảnh bìa" />
                        @error('listingAvatarFile') <span class="cms-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="cms-field full">
                        <span class="cms-label">Ảnh slider (bộ ảnh chi tiết)</span>
                        <x-image-uploader name="listingImageFiles" :images="$listingImages" :previews="$listingImageFiles" on-remove="removeListingImage" label="Tải ảnh slider" hint="Nhiều ảnh — hiển thị ở slider trang chi tiết" />
                        @error('listingImageFiles.*') <span class="cms-error">{{ $message }}</span> @enderror
                    </div>
                    <label class="cms-field">
                        <span class="cms-label">Link Youtube dài</span>
                        <input class="cms-input" wire:model="listingYoutubeLink" placeholder="https://...">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Link Youtube Shorts</span>
                        <input class="cms-input" wire:model="listingYoutubeShort" placeholder="https://...">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Link Facebook Post</span>
                        <input class="cms-input" wire:model="listingFacebookLink" placeholder="https://...">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Link Facebook Video</span>
                        <input class="cms-input" wire:model="listingFacebookVideoLink" placeholder="https://...">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Link TikTok</span>
                        <input class="cms-input" wire:model="listingTiktokLink" placeholder="https://...">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Link Google Map</span>
                        <input class="cms-input" wire:model="listingGoogleMapLink" placeholder="https://...">
                    </label>
                    @if($errors->any())
                        <div class="cms-field full cms-error">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button type="button" class="cms-btn" wire:click="closeListingModal"><i class="fa-solid fa-xmark"></i> Hủy</button>
                    <button type="button" class="cms-btn primary" wire:click="saveListing"><i class="fa-solid fa-floppy-disk"></i> Lưu tin đăng</button>
                </div>
            </section>
        </div>
    @endif

    @if($showBannerModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $bannerEditingId ? 'Sửa banner' : 'Thêm banner' }}</h2>
                    <button type="button" class="cms-icon-btn" wire:click="closeBannerModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full">
                        <span class="cms-label">Upload ảnh banner local</span>
                        <input class="cms-input" type="file" wire:model="bannerImageFile" accept="image/*">
                        @error('bannerImageFile') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field full">
                        <span class="cms-label">Hoặc nhập URL ảnh banner</span>
                        <input class="cms-input" wire:model="bannerImageUrl" placeholder="https://...">
                        @error('bannerImageUrl') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field full">
                        <span class="cms-label">Link khi bấm banner</span>
                        <input class="cms-input" wire:model="bannerLinkUrl" placeholder="https://... hoặc để trống">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Tiêu đề</span>
                        <input class="cms-input" wire:model="bannerTitle">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Mô tả ngắn</span>
                        <input class="cms-input" wire:model="bannerSubtitle">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Thứ tự</span>
                        <input class="cms-input mono" type="number" wire:model="bannerSortOrder">
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Trạng thái</span>
                        <select class="cms-select" wire:model="bannerIsActive">
                            <option value="1">Bật</option>
                            <option value="0">Tắt</option>
                        </select>
                    </label>
                    @if($errors->any())
                        <div class="cms-field full cms-error">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button type="button" class="cms-btn" wire:click="closeBannerModal"><i class="fa-solid fa-xmark"></i> Hủy</button>
                    <button type="button" class="cms-btn primary" wire:click="saveBanner"><i class="fa-solid fa-floppy-disk"></i> Lưu banner</button>
                </div>
            </section>
        </div>
    @endif

    @if($showAccountModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ $accountEditingId ? 'Sửa tài khoản' : 'Thêm tài khoản' }}</h2>
                    <button type="button" class="cms-icon-btn" wire:click="closeAccountModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full">
                        <span class="cms-label">Họ và tên</span>
                        <input class="cms-input" wire:model="accountName" placeholder="VD: Nguyễn Văn A">
                        @error('accountName') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Số điện thoại (đăng nhập)</span>
                        <input class="cms-input mono" wire:model="accountPhone" placeholder="098...">
                        @error('accountPhone') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Vai trò</span>
                        <select class="cms-select" wire:model="accountRole">
                            @foreach($roleOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('accountRole') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field full">
                        <span class="cms-label">Mật khẩu {{ $accountEditingId ? '(để trống nếu không đổi)' : '' }}</span>
                        <input class="cms-input" type="password" wire:model="accountPassword" placeholder="{{ $accountEditingId ? 'Nhập để đặt mật khẩu mới' : 'Tối thiểu 6 ký tự' }}" autocomplete="new-password">
                        @error('accountPassword') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button type="button" class="cms-btn" wire:click="closeAccountModal"><i class="fa-solid fa-xmark"></i> Hủy</button>
                    <button type="button" class="cms-btn primary" wire:click="saveAccount"><i class="fa-solid fa-floppy-disk"></i> Lưu tài khoản</button>
                </div>
            </section>
        </div>
    @endif

    @if($showAmenityModal)
        <div class="cms-modal-backdrop">
            <section class="cms-modal">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title">{{ ($amenityEditingId ? 'Sửa ' : 'Thêm ') . ($amenityTypes[$amenityFormType] ?? 'mục') }}</h2>
                    <button type="button" class="cms-icon-btn" wire:click="closeAmenityModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="cms-form-grid">
                    <label class="cms-field full">
                        <span class="cms-label">Tên hiển thị</span>
                        <input class="cms-input" wire:model="amenityName" placeholder="VD: Máy lạnh">
                        @error('amenityName') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Mã (key) — để trống sẽ tự sinh</span>
                        <input class="cms-input mono" wire:model="amenityKey" placeholder="vd: air_conditioner" {{ $amenityEditingId ? 'readonly' : '' }}>
                        @error('amenityKey') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Thứ tự</span>
                        <input class="cms-input mono" type="number" wire:model="amenitySortOrder">
                        @error('amenitySortOrder') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Loại</span>
                        <select class="cms-select" wire:model="amenityFormType">
                            @foreach($amenityTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('amenityFormType') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="cms-field">
                        <span class="cms-label">Trạng thái</span>
                        <select class="cms-select" wire:model="amenityIsActive">
                            <option value="1">Hiện</option>
                            <option value="0">Ẩn</option>
                        </select>
                    </label>
                    <label class="cms-field full">
                        <span class="cms-label">Ảnh icon (upload local)</span>
                        <input class="cms-input" type="file" wire:model="amenityIconFile" accept="image/*">
                        @error('amenityIconFile') <span class="cms-error">{{ $message }}</span> @enderror
                    </label>
                    <div class="cms-field full">
                        <span class="cms-label">Preview icon</span>
                        <div class="site-cms-logo-preview">
                            @if($amenityIconFile)
                                <img src="{{ $amenityIconFile->temporaryUrl() }}" alt="Icon preview">
                            @elseif($amenityIconUrl)
                                <img src="{{ $amenityIconUrl }}" alt="Icon hiện tại">
                            @else
                                <span><i class="fa-solid fa-couch"></i></span>
                            @endif
                            <strong>{{ $amenityName ?: 'Tên tiện ích' }}</strong>
                            <em>Icon hiển thị ngoài trang chủ cho người dùng.</em>
                        </div>
                    </div>
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button type="button" class="cms-btn" wire:click="closeAmenityModal"><i class="fa-solid fa-xmark"></i> Hủy</button>
                    <button type="button" class="cms-btn primary" wire:click="saveAmenity"><i class="fa-solid fa-floppy-disk"></i> Lưu tiện ích</button>
                </div>
            </section>
        </div>
    @endif

    @if($showRejectModal)
        <div class="cms-modal-backdrop" wire:click.self="closeRejectModal">
            <section class="cms-modal" style="max-width:480px;">
                <div class="cms-panel-head">
                    <h2 class="cms-panel-title"><i class="fa-solid fa-ban"></i> Từ chối tin đăng</h2>
                    <button type="button" class="cms-icon-btn" wire:click="closeRejectModal"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div style="padding:20px 24px;">
                    <label class="cms-label" for="rejectReason">Lý do từ chối <span style="color:var(--danger)">*</span></label>
                    <textarea
                        id="rejectReason"
                        class="cms-input"
                        wire:model="rejectReason"
                        rows="4"
                        placeholder="Nhập lý do từ chối để thông báo cho người đăng..."
                        style="resize:vertical;"
                    ></textarea>
                    @error('rejectReason') <p class="cms-error">{{ $message }}</p> @enderror
                </div>
                <div class="cms-panel-head" style="justify-content:flex-end;">
                    <button type="button" class="cms-btn" wire:click="closeRejectModal"><i class="fa-solid fa-xmark"></i> Hủy</button>
                    <button type="button" class="cms-btn danger" wire:click="confirmReject"><i class="fa-solid fa-ban"></i> Xác nhận từ chối</button>
                </div>
            </section>
        </div>
    @endif
</div>
