<div class="user-page">
    @php
        $imageUrl = fn ($path) => $path ? (str_starts_with((string) $path, 'http') ? $path : asset('storage/' . ltrim((string) $path, '/'))) : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=70';
        $initial = mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1));
    @endphp

    <div class="site-shell user-grid">
        {{-- 3 phần: hồ sơ cá nhân --}}
        <aside class="user-profile">
            <div class="user-avatar">{{ $initial }}</div>
            <h2 class="user-name">{{ $user->name ?: 'Người dùng' }}</h2>
            <div class="user-phone">{{ $user->phone }}</div>
            <span class="user-role">{{ $user->isAdmin() ? 'Quản trị' : 'Thành viên' }}</span>

            <div class="user-stats">
                <div><strong>{{ $counts['all'] }}</strong><span>Tổng tin</span></div>
                <div><strong>{{ $counts['active'] }}</strong><span>Đang hiện</span></div>
                <div><strong>{{ $counts['hidden'] }}</strong><span>Đã ẩn</span></div>
            </div>

            <a class="user-post-btn" href="{{ route('user.listing.create') }}">+ Đăng tin mới</a>

            <div class="user-profile-meta">
                <div><span>Ngày tham gia</span><strong>{{ optional($user->created_at)->format('d/m/Y') ?: '-' }}</strong></div>
                <div><span>Email</span><strong>{{ $user->email ?: 'Chưa có' }}</strong></div>
            </div>
        </aside>

        {{-- 7 phần: khối tin đăng --}}
        <section class="user-main">
            <div class="user-main-head">
                <h1>Tin đăng của tôi</h1>
                <a class="user-post-btn sm" href="{{ route('user.listing.create') }}">+ Đăng tin</a>
            </div>

            @if(session('message'))
                <div class="user-flash">{{ session('message') }}</div>
            @endif

            <div class="user-tabs">
                <button type="button" class="{{ $tab === 'all' ? 'is-active' : '' }}" wire:click="setTab('all')">Tất cả <span>{{ $counts['all'] }}</span></button>
                <button type="button" class="{{ $tab === 'active' ? 'is-active' : '' }}" wire:click="setTab('active')">Đang hiện <span>{{ $counts['active'] }}</span></button>
                <button type="button" class="{{ $tab === 'hidden' ? 'is-active' : '' }}" wire:click="setTab('hidden')">Đã ẩn <span>{{ $counts['hidden'] }}</span></button>
            </div>

            <div class="user-filters">
                <input type="text" wire:model.live.debounce.350ms="search" placeholder="Tìm theo tiêu đề, mã tin, khu vực...">
                <select wire:model.live="priceFilter">
                    <option value="">Tất cả mức giá</option>
                    <option value="under_3">Dưới 3 triệu</option>
                    <option value="3_4">Từ 3 - 4 triệu</option>
                    <option value="4_5">Từ 4 - 5 triệu</option>
                    <option value="5_6">Từ 5 - 6 triệu</option>
                    <option value="over_6">Trên 6 triệu</option>
                </select>
            </div>

            <div class="user-listings">
                @forelse($listings as $listing)
                    <article class="user-listing {{ $listing->is_sold ? 'is-hidden' : '' }}">
                        <a class="user-listing-img" href="{{ route('site.listings.show', $listing) }}" target="_blank">
                            <img src="{{ $imageUrl($listing->avatar ?: ($listing->images[0] ?? null)) }}" alt="{{ $listing->title }}" loading="lazy">
                        </a>
                        <div class="user-listing-body">
                            <div class="user-listing-top">
                                <h3>{{ $listing->title }}</h3>
                                <span class="user-badge {{ $listing->is_sold ? 'muted' : 'ok' }}">{{ $listing->is_sold ? 'Đã ẩn' : 'Đang hiện' }}</span>
                            </div>
                            <div class="user-listing-meta">
                                <span class="user-price">{{ number_format((float) $listing->price, 0, ',', '.') }}đ/tháng</span>
                                <span>{{ $roomTypes[$listing->room_type] ?? 'Phòng trọ' }}</span>
                                <span>{{ implode(', ', array_filter([$listing->ward_name, $listing->district_name])) ?: 'TP.HCM' }}</span>
                                <span class="mono">{{ $listing->code }}</span>
                                @if($listing->created_at)<span>🕐 {{ $listing->created_at->locale('vi')->diffForHumans() }}</span>@endif
                            </div>
                            <div class="user-listing-actions">
                                <a href="{{ route('user.listing.edit', $listing) }}">Sửa</a>
                                <button type="button" wire:click="toggleListing({{ $listing->id }})">{{ $listing->is_sold ? 'Hiện lại' : 'Ẩn tin' }}</button>
                                <button type="button" class="danger" wire:click="deleteListing({{ $listing->id }})" wire:confirm="Xóa tin đăng này?">Xóa</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="user-empty">
                        <p>Bạn chưa có tin đăng nào ở mục này.</p>
                        <a class="user-post-btn" href="{{ route('user.listing.create') }}">+ Đăng tin đầu tiên</a>
                    </div>
                @endforelse
            </div>

            <div class="user-pagination">{{ $listings->links() }}</div>
        </section>
    </div>
</div>
