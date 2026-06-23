<div class="user-page">
    @php
        $imageUrl = fn ($path) => $path ? (str_starts_with((string) $path, 'http') ? $path : asset('storage/' . ltrim((string) $path, '/'))) : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=70';
        $initial = mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1));
        $effectivePlan = $user->posting_plan_expires_at && $user->posting_plan_expires_at->isPast() ? 'free' : ($user->posting_plan ?: 'free');
        $salesPhone = $siteContact['phone'] ?? '';
        $salesHref = ($siteContact['zaloHref'])($salesPhone) ?: ($salesPhone ? 'tel:' . preg_replace('/\D+/', '', $salesPhone) : route('site.home') . '#lien-he');
        $salesTarget = str_starts_with($salesHref, 'http') ? '_blank' : null;
    @endphp

    <div class="site-shell user-grid">
        {{-- 3 phần: hồ sơ cá nhân --}}
        <aside class="user-profile">
            @php $avatarUrl = $user->avatar ? (str_starts_with($user->avatar,'http') ? $user->avatar : asset('storage/'.ltrim($user->avatar,'/'))) : null; @endphp
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" class="user-avatar-img" alt="{{ $user->name }}">
            @else
                <div class="user-avatar">{{ $initial }}</div>
            @endif

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
                @if($user->birth_year)<div><span>Năm sinh</span><strong>{{ $user->birth_year }}</strong></div>@endif
                <div><span>Gói đăng tin</span><strong>{{ $user->postingPlanLabel() }}</strong></div>
                <div><span>Giới hạn/ngày</span><strong>{{ $user->postingLimitPerDay() }} tin</strong></div>
            </div>

            <div class="user-sidebar-actions">
                <a href="#cai-dat" class="user-sidebar-link">⚙️ Cài đặt tài khoản</a>
                <button type="button" onclick="window.__openLogoutConfirm()" class="user-sidebar-link danger">🚪 Đăng xuất</button>
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

            <section class="user-plan-box">
                <div class="user-plan-head">
                    <div><small>Gói đăng tin</small><h2>Chọn số lượng tin phù hợp</h2></div>
                    @if($user->posting_plan_expires_at && $user->posting_plan_expires_at->isFuture())
                        <span>Hết hạn {{ $user->posting_plan_expires_at->format('d/m/Y') }}</span>
                    @endif
                </div>
                <div class="user-plan-grid">
                    @foreach($postingPlans as $planKey => $package)
                        @php $isCurrent = $effectivePlan === $planKey; @endphp
                        <article class="user-plan-card {{ $isCurrent ? 'is-current' : '' }}">
                            <strong>{{ $package['name'] }}</strong>
                            <div><b>{{ $package['limit'] }}</b> tin/ngày</div>
                            <p>{{ $package['price'] ? number_format($package['price'], 0, ',', '.') . 'đ / tháng' : 'Miễn phí' }}</p>
                            @if($planKey === 'free')
                                <span class="user-plan-state">{{ $isCurrent ? 'Gói hiện tại' : 'Gói mặc định' }}</span>
                            @else
                                @if($isCurrent)<span class="user-plan-state">Đang sử dụng</span>@endif
                                <a class="user-plan-buy" href="{{ $salesHref }}" @if($salesTarget) target="{{ $salesTarget }}" rel="noopener" @endif>{{ $isCurrent ? 'Liên hệ gia hạn' : 'Liên hệ mua gói' }}</a>
                            @endif
                        </article>
                    @endforeach
                </div>
                <p class="user-plan-contact">Chưa hỗ trợ thanh toán trực tuyến. Bấm liên hệ để được admin kích hoạt gói{{ $salesPhone ? ' · SĐT ' . $salesPhone : '' }}.</p>
            </section>

            <div class="user-tabs">
                <button type="button" class="{{ $tab === 'all' ? 'is-active' : '' }}" wire:click="setTab('all')">Tất cả <span>{{ $counts['all'] }}</span></button>
                <button type="button" class="{{ $tab === 'pending' ? 'is-active' : '' }}" wire:click="setTab('pending')">Đang chờ <span>{{ $counts['pending'] }}</span></button>
                <button type="button" class="{{ $tab === 'active' ? 'is-active' : '' }}" wire:click="setTab('active')">Đang hiện <span>{{ $counts['active'] }}</span></button>
                <button type="button" class="{{ $tab === 'rejected' ? 'is-active' : '' }}" wire:click="setTab('rejected')">Bị từ chối <span>{{ $counts['rejected'] }}</span></button>
                <button type="button" class="{{ $tab === 'hidden' ? 'is-active' : '' }}" wire:click="setTab('hidden')">Đã ẩn <span>{{ $counts['hidden'] }}</span></button>
                <button type="button" class="{{ $tab === 'boosting' ? 'is-active' : '' }}" wire:click="setTab('boosting')">Đang đẩy tin <span>{{ $counts['boosting'] }}</span></button>
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
                                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                    @php $mod = $listing->moderation_status ?: 'approved'; @endphp
                                    @if($mod === 'pending')
                                        <span class="user-badge warning" title="Tin đang chờ admin xét duyệt">⏳ Chờ duyệt</span>
                                    @elseif($mod === 'rejected')
                                        <span class="user-badge danger" title="{{ $listing->rejection_reason ? 'Lý do: '.$listing->rejection_reason : 'Tin bị từ chối' }}">✗ Bị từ chối</span>
                                    @endif
                                    <span class="user-badge {{ $listing->is_sold ? 'muted' : 'ok' }}">{{ $listing->is_sold ? 'Đã ẩn' : 'Đang hiện' }}</span>
                                </div>
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
                                @if(!$listing->is_sold && ($listing->moderation_status ?: 'approved') === 'approved' && (!$listing->status || $listing->status === 'active'))
                                    <a class="boost" href="{{ $salesHref }}" @if($salesTarget) target="{{ $salesTarget }}" rel="noopener" @endif title="Mức 1: 10k/1 ngày · Mức 2: 20k/3 ngày · Mức 3: 50k/7 ngày">🔥 Liên hệ đẩy tin</a>
                                @endif
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

        {{-- ===== CÀI ĐẶT TÀI KHOẢN ===== --}}
        <section class="user-main user-settings-section" id="cai-dat">
            <div class="user-settings-head">
                <h2>⚙️ Cài đặt tài khoản</h2>
            </div>

            {{-- Tab điều hướng --}}
            <div class="user-tabs" style="margin-bottom:16px;">
                <button type="button" class="{{ $settingsTab === 'profile' ? 'is-active' : '' }}" wire:click="setSettingsTab('profile')">👤 Thông tin cá nhân</button>
                <button type="button" class="{{ $settingsTab === 'password' ? 'is-active' : '' }}" wire:click="setSettingsTab('password')">🔑 Đổi mật khẩu</button>
                <button type="button" class="{{ $settingsTab === 'delete' ? 'is-active' : '' }}" wire:click="setSettingsTab('delete')">🗑️ Xóa tài khoản</button>
            </div>

            {{-- Flash message --}}
            @if(session('message'))
                <div class="user-flash">{{ session('message') }}</div>
            @endif

            {{-- TAB: Thông tin cá nhân --}}
            @if($settingsTab === 'profile')
            <form wire:submit="saveProfile" class="user-settings-form" enctype="multipart/form-data">
                {{-- Avatar --}}
                <div class="user-settings-avatar-row">
                    <div class="user-settings-ava-wrap">
                        @if($profileAvatarFile)
                            <img src="{{ $profileAvatarFile->temporaryUrl() }}" class="user-settings-ava" alt="Preview">
                        @elseif($profileAvatar)
                            <img src="{{ str_starts_with($profileAvatar,'http') ? $profileAvatar : asset('storage/'.ltrim($profileAvatar,'/')) }}" class="user-settings-ava" alt="Avatar">
                        @else
                            <div class="user-settings-ava-ph">{{ mb_strtoupper(mb_substr($profileName ?: 'U', 0, 1)) }}</div>
                        @endif
                    </div>
                    <div class="user-settings-ava-actions">
                        <label class="user-btn-outline" style="cursor:pointer;">
                            📷 Chọn ảnh đại diện
                            <input type="file" wire:model="profileAvatarFile" accept="image/*" style="display:none;">
                        </label>
                        @if($profileAvatar || $profileAvatarFile)
                            <button type="button" wire:click="removeAvatar" class="user-btn-ghost danger">Xóa ảnh</button>
                        @endif
                    </div>
                    @error('profileAvatarFile') <em class="ff-err">{{ $message }}</em> @enderror
                </div>

                <div class="user-settings-grid">
                    <label class="user-settings-field">
                        <span>Họ và tên <i>*</i></span>
                        <input type="text" wire:model="profileName" placeholder="Nguyễn Văn A">
                        @error('profileName') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="user-settings-field">
                        <span>Số điện thoại <i>*</i></span>
                        <input type="text" inputmode="tel" wire:model="profilePhone" placeholder="098...">
                        @error('profilePhone') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="user-settings-field">
                        <span>Email</span>
                        <input type="email" wire:model="profileEmail" placeholder="example@gmail.com">
                        @error('profileEmail') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="user-settings-field">
                        <span>Năm sinh</span>
                        <input type="number" inputmode="numeric" wire:model="profileBirthYear" placeholder="VD: 1995" min="1900" max="{{ date('Y') - 5 }}">
                        @error('profileBirthYear') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                </div>

                <button type="submit" class="user-settings-save">
                    <span wire:loading.remove wire:target="saveProfile">💾 Lưu thông tin</span>
                    <span wire:loading wire:target="saveProfile">Đang lưu...</span>
                </button>
            </form>
            @endif

            {{-- TAB: Đổi mật khẩu --}}
            @if($settingsTab === 'password')
            <form wire:submit="savePassword" class="user-settings-form">
                <div class="user-settings-grid" style="max-width:480px;">
                    <label class="user-settings-field full">
                        <span>Mật khẩu hiện tại <i>*</i></span>
                        <input type="password" wire:model="currentPassword" placeholder="Nhập mật khẩu hiện tại" autocomplete="current-password">
                        @error('currentPassword') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="user-settings-field full">
                        <span>Mật khẩu mới <i>*</i></span>
                        <input type="password" wire:model="newPassword" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password">
                        @error('newPassword') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="user-settings-field full">
                        <span>Xác nhận mật khẩu mới <i>*</i></span>
                        <input type="password" wire:model="newPasswordConfirm" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password">
                        @error('newPasswordConfirm') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                </div>
                <button type="submit" class="user-settings-save">
                    <span wire:loading.remove wire:target="savePassword">🔑 Đổi mật khẩu</span>
                    <span wire:loading wire:target="savePassword">Đang lưu...</span>
                </button>
            </form>
            @endif

            {{-- TAB: Xóa tài khoản --}}
            @if($settingsTab === 'delete')
            <div class="user-settings-form">
                @if(!$showDeleteConfirm)
                <div class="user-danger-zone">
                    <div class="user-danger-icon">⚠️</div>
                    <h3>Xóa tài khoản vĩnh viễn</h3>
                    <p>Sau khi xóa, toàn bộ dữ liệu bao gồm tin đăng, thông tin tài khoản sẽ bị xóa và <strong>không thể khôi phục</strong>.</p>
                    <button type="button" wire:click="confirmDeleteAccount" class="user-settings-save danger">
                        🗑️ Tôi muốn xóa tài khoản
                    </button>
                </div>
                @else
                <form wire:submit="deleteAccount" class="user-danger-confirm">
                    <div class="user-danger-icon">😟</div>
                    <h3>Xác nhận xóa tài khoản</h3>
                    <p>Nhập mật khẩu để xác nhận. Thao tác này <strong>không thể hoàn tác</strong>.</p>
                    <label class="user-settings-field" style="max-width:360px; margin: 0 auto 16px;">
                        <span>Mật khẩu xác nhận</span>
                        <input type="password" wire:model="deleteConfirmPassword" placeholder="Nhập mật khẩu của bạn" autocomplete="current-password">
                        @error('deleteConfirmPassword') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <div class="user-danger-btns">
                        <button type="button" wire:click="cancelDelete" class="user-btn-outline">Hủy bỏ</button>
                        <button type="submit" class="user-settings-save danger">
                            <span wire:loading.remove wire:target="deleteAccount">🗑️ Xác nhận xóa</span>
                            <span wire:loading wire:target="deleteAccount">Đang xóa...</span>
                        </button>
                    </div>
                </form>
                @endif
            </div>
            @endif
        </section>
    </div>

</div>
