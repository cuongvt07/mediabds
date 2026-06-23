@extends('site.layout')

@section('title', 'Phòng trọ TP.HCM - Tìm phòng nhanh, giá rõ ràng')

@php
    $imageUrl = function ($listing, $index = 0) {
        $images = is_array($listing->images) ? $listing->images : [];
        $path = $images[$index] ?? ($index === 0 ? $listing->avatar : null);
        if (! $path) {
            return 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1400&q=85';
        }
        return \App\Support\Watermark::url($path);
    };
    $slideImageUrl = fn ($slide) => $slide->image_url ?? $imageUrl($slide);
    $roomLabels = ['duplex' => 'Duplex', 'studio' => 'Studio', 'loft' => 'Phòng có gác', 'balcony' => 'Phòng ban công'];
    $furnishLabels = ['full' => 'Đầy đủ nội thất', 'basic' => 'Nội thất cơ bản', 'empty' => 'Phòng trống'];
    $priceLabels = [
        'low_high' => 'Từ thấp đến cao',
        'high_low' => 'Từ cao đến thấp',
        'under_3' => 'Dưới 3tr',
        '3_4' => 'Từ 3-4tr',
        '4_5' => 'Từ 4-5tr',
        '5_6' => '5tr-6tr',
        'over_6' => 'Trên 6tr',
    ];
    $selectedAmenities = array_filter((array) request('amenities', []));
    $selectedFurniture = array_filter((array) request('furniture', []));
    $furnitureNameByKey = $furnitureItems->pluck('name', 'key');
    $selectedFurnitureText = collect($selectedFurniture)
        ->map(fn ($value) => $furnitureNameByKey[$value] ?? null)
        ->filter()
        ->implode(', ');
    // Icon động từ CMS: dùng ảnh upload nếu có, không thì rơi về chữ cái đầu.
    $amenityIconHtml = function ($item) {
        if (! empty($item->icon)) {
            return '<img src="' . e($item->icon) . '" alt="' . e($item->name) . '" class="site-amenity-img" loading="lazy">';
        }
        return '<i>' . e(mb_substr($item->name, 0, 1)) . '</i>';
    };
    $currentDistrict = $districts->firstWhere('district_id', request('district'));
    $formatPrice = fn ($listing) => number_format((float) $listing->price, 0, ',', '.');
@endphp

@section('content')
    {{-- HERO: Banner slider --}}
    <section class="site-hero">
        <div class="site-shell">
            <div class="site-slider" data-slider>
                @forelse($slides as $slide)
                    <div class="site-slide {{ $usingSiteBanners ? 'is-banner' : '' }} {{ $loop->first ? 'is-active' : '' }}" data-slide>
                        @if($usingSiteBanners && $slide->link_url)
                            <a href="{{ $slide->link_url }}" target="_blank" rel="noopener">
                                <img src="{{ $slideImageUrl($slide) }}" alt="{{ $slide->title ?: 'Banner nhà trọ' }}">
                            </a>
                        @else
                            <img src="{{ $slideImageUrl($slide) }}" alt="{{ $slide->title ?: 'Phòng trọ TP.HCM' }}">
                        @endif
                    </div>
                @empty
                    <div class="site-slide is-active" data-slide>
                        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1600&q=85" alt="Phòng trọ TP.HCM">
                    </div>
                @endforelse
                @unless($usingSiteBanners)
                    <div class="site-hero-copy">
                        <span class="site-eyebrow"><i></i> Phòng thật · Giá thật · TP.HCM</span>
                        <h1>Tìm căn phòng <em>vừa ý</em>, bắt đầu cuộc sống mới.</h1>
                        <p>Danh sách phòng trọ được cập nhật trực tiếp từ quản trị viên, tập trung tại các quận và phường của TP.HCM.</p>
                    </div>
                @endunless
                <div class="site-slider-dots" data-dots>
                    @foreach($slides->isEmpty() ? [1] : $slides as $slide)
                        <button class="{{ $loop->first ? 'is-active' : '' }}" type="button" aria-label="Xem slide {{ $loop->iteration }}"></button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- FILTER: sticky bar bên ngoài hero section để position:sticky hoạt động --}}
    <div class="site-filter-wrap" id="tim-phong">
        <div class="site-shell">
            {{-- Desktop filter --}}
            <form class="site-filter" method="GET" action="{{ route('site.home') }}#danh-sach">
                <input type="hidden" name="tab" value="{{ request('tab') }}">
                <div class="site-field">
                    <label for="district">Quận</label>
                    <select id="district" name="district" onchange="this.form.ward.value=''; this.form.submit()">
                        <option value="">Tất cả quận</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->district_id }}" @selected(request('district') == $district->district_id)>{{ $district->district_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="site-field">
                    <label for="ward">Phường</label>
                    <select id="ward" name="ward">
                        <option value="">Tất cả phường</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->ward_id }}" @selected(request('ward') == $ward->ward_id)>{{ $ward->ward_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="site-field">
                    <label for="price">Giá phòng</label>
                    <select id="price" name="price">
                        <option value="">Mới cập nhật</option>
                        <option value="low_high" @selected(request('price') === 'low_high')>Từ thấp đến cao</option>
                        <option value="high_low" @selected(request('price') === 'high_low')>Từ cao đến thấp</option>
                        <option value="under_3" @selected(request('price') === 'under_3')>Dưới 3 triệu</option>
                        <option value="3_4" @selected(request('price') === '3_4')>Từ 3 - 4 triệu</option>
                        <option value="4_5" @selected(request('price') === '4_5')>Từ 4 - 5 triệu</option>
                        <option value="5_6" @selected(request('price') === '5_6')>Từ 5 - 6 triệu</option>
                        <option value="over_6" @selected(request('price') === 'over_6')>Trên 6 triệu</option>
                    </select>
                </div>
                <div class="site-field">
                    <label for="room_type">Dạng phòng</label>
                    <select id="room_type" name="room_type">
                        <option value="">Tất cả dạng phòng</option>
                        @foreach($roomLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('room_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="site-field site-multi-field">
                    <label>Nội thất</label>
                    <details>
                        <summary>
                            <span>{{ $selectedFurnitureText ?: 'Tất cả nội thất' }}</span>
                            <i>⌄</i>
                        </summary>
                        <div class="site-multi-options">
                            @foreach($furnitureItems as $item)
                                <label>
                                    <input type="checkbox" name="furniture[]" value="{{ $item->key }}" @checked(in_array($item->key, $selectedFurniture, true))>
                                    <span>{!! $amenityIconHtml($item) !!}{{ $item->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                </div>
                <button class="site-search-btn" type="submit">Tìm phòng</button>
                <div class="site-amenity-filter">
                    <h3>Tiện ích phòng trọ</h3>
                    <div class="site-amenity-options">
                        @foreach($amenityItems as $item)
                            <label class="site-amenity-card">
                                <input type="checkbox" name="amenities[]" value="{{ $item->key }}" @checked(in_array($item->key, $selectedAmenities, true))>
                                <span>
                                    {!! $amenityIconHtml($item) !!}
                                    <b>{{ $item->name }}</b>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>

            {{-- Mobile filter trigger --}}
            <div class="site-mobile-filter" aria-label="Bộ lọc phòng trọ">
                <button class="site-mobile-search" type="button" data-filter-open>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m21 21-4.3-4.3m1.3-5.2a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Tìm phòng trọ...</span>
                </button>
                <div class="site-mobile-filter-row">
                    <button class="site-filter-chip site-filter-chip-icon" type="button" data-filter-open>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 7h14M8 12h8m-5 5h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Lọc
                    </button>
                    <button class="site-filter-chip is-active" type="button" data-filter-open>Thành phố Hồ Chí Minh</button>
                    <button class="site-filter-chip" type="button" data-filter-open>{{ $currentDistrict->district_name ?? 'Quận/Huyện' }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile filter bottom-sheet modal --}}
    <div class="site-filter-modal" data-filter-modal aria-hidden="true">
        <button class="site-filter-backdrop" type="button" data-filter-close aria-label="Đóng bộ lọc"></button>
        <form class="site-filter-sheet" method="GET" action="{{ route('site.home') }}#danh-sach" role="dialog" aria-modal="true" aria-labelledby="mobile-filter-title">
            <input type="hidden" name="tab" value="{{ request('tab') }}">
            <span class="site-filter-handle" aria-hidden="true"></span>
            <div class="site-filter-sheet-head">
                <h2 id="mobile-filter-title">Bộ lọc tìm kiếm</h2>
                <button type="button" data-filter-close aria-label="Đóng bộ lọc">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="site-field">
                <label for="mobile_province">Tỉnh/Thành phố</label>
                <select id="mobile_province" disabled>
                    <option>Thành phố Hồ Chí Minh</option>
                </select>
            </div>
            <div class="site-field">
                <label for="mobile_district">Quận/Huyện</label>
                <select id="mobile_district" name="district" data-mobile-district>
                    <option value="">Tất cả</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->district_id }}" @selected(request('district') == $district->district_id)>{{ $district->district_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="site-field">
                <label for="mobile_ward">Phường</label>
                <select id="mobile_ward" name="ward" data-mobile-ward data-selected="{{ request('ward') }}">
                    <option value="">Tất cả</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->ward_id }}" @selected(request('ward') == $ward->ward_id)>{{ $ward->ward_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="site-field">
                <label for="mobile_room_type">Dạng phòng</label>
                <select id="mobile_room_type" name="room_type">
                    <option value="">Tất cả</option>
                    @foreach($roomLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('room_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="site-field site-multi-field">
                <label>Nội thất</label>
                <details>
                    <summary>
                        <span>{{ $selectedFurnitureText ?: 'Tất cả nội thất' }}</span>
                        <i>⌄</i>
                    </summary>
                    <div class="site-multi-options">
                        @foreach($furnitureItems as $item)
                            <label>
                                <input type="checkbox" name="furniture[]" value="{{ $item->key }}" @checked(in_array($item->key, $selectedFurniture, true))>
                                <span>{!! $amenityIconHtml($item) !!}{{ $item->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>
            </div>
            <div class="site-amenity-filter">
                <h3>Tiện ích phòng trọ</h3>
                <div class="site-amenity-options">
                    @foreach($amenityItems as $item)
                        <label class="site-amenity-card">
                            <input type="checkbox" name="amenities[]" value="{{ $item->key }}" @checked(in_array($item->key, $selectedAmenities, true))>
                            <span>
                                {!! $amenityIconHtml($item) !!}
                                <b>{{ $item->name }}</b>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="site-price-filter">
                <div class="site-price-title">Mức giá <span>(bỏ chọn để xem tất cả)</span></div>
                <div class="site-price-options">
                    @foreach($priceLabels as $value => $label)
                        <label>
                            <input type="radio" name="price" value="{{ $value }}" @checked(request('price') === $value)>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button class="site-search-btn" type="submit">Tìm kiếm</button>
        </form>
    </div>

    <section class="site-section" id="danh-sach">
        <div class="site-shell">
            <div class="site-section-head">
                <div>
                    <small>Phòng đang có sẵn</small>
                    <h2>Chọn phòng hợp với bạn</h2>
                </div>
                <p>Có {{ number_format($listings->total()) }} phòng phù hợp. Thông tin vị trí chỉ hiển thị đến phường để bảo vệ địa chỉ căn phòng.</p>
            </div>

            <div class="site-listing-tabs">
                <a class="{{ request('tab') === 'hot' ? 'is-active' : '' }}" href="{{ route('site.home', array_merge(request()->except('page'), ['tab' => 'hot'])) }}#danh-sach">Tin hot 🔥</a>
                <a class="{{ request('tab') !== 'hot' ? 'is-active' : '' }}" href="{{ route('site.home', request()->except(['page', 'tab'])) }}#danh-sach">Tất cả</a>
            </div>

            <div class="site-listing-grid">
                @forelse($listings as $listing)
                    <article class="site-card">
                        <a class="site-card-link" href="{{ route('site.listings.show', $listing) }}" aria-label="Xem {{ $listing->title }}">
                            <div class="site-card-media">
                                <img src="{{ $imageUrl($listing) }}" alt="{{ $listing->title }}" loading="lazy">
                                <span class="site-badge">{{ $roomLabels[$listing->room_type] ?? 'Phòng trọ' }}</span>
                                @if($listing->isBoosted())
                                    <span class="site-hot-badge">🔥 Tin hot</span>
                                @endif
                                <span class="site-photo-count">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 4.5 10.4 3h3.2L15 4.5h3A3 3 0 0 1 21 7.5v9a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-9a3 3 0 0 1 3-3h3Zm3 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0-2a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/></svg>
                                    {{ count($listing->images ?? []) }}
                                </span>
                            </div>
                            <div class="site-card-body">
                                <span class="site-card-type">Cho thuê phòng trọ</span>
                                <h3 class="site-card-title">{{ $listing->title }}</h3>
                                <div class="site-card-location">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8Zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/></svg>
                                    <span>{{ implode(', ', array_filter([$listing->ward_name, $listing->district_name, 'TP.HCM'])) }}</span>
                                </div>
                                <div class="site-card-meta">
                                    <span class="site-chip">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 5h16v14H4V5Zm2 2v10h12V7H6Zm2 2h8v2H8V9Zm0 4h5v2H8v-2Z"/></svg>
                                        {{ $roomLabels[$listing->room_type] ?? 'Phòng trọ' }}
                                    </span>
                                    @if($listing->furnish)
                                        <span class="site-chip">
                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 3h10v6h2a2 2 0 0 1 2 2v7h-2v3h-2v-3H7v3H5v-3H3v-7a2 2 0 0 1 2-2h2V3Zm2 2v4h6V5H9Zm-4 6v5h14v-5H5Z"/></svg>
                                            {{ $furnishLabels[$listing->furnish] ?? $listing->furnish }}
                                        </span>
                                    @endif
                                </div>
                                @if($listing->created_at)
                                    <div class="site-card-time">🕐 {{ $listing->created_at->locale('vi')->diffForHumans() }}</div>
                                @endif
                                <div class="site-card-foot">
                                    <strong class="site-price">{{ $formatPrice($listing) }} <span>VNĐ/tháng</span></strong>
                                    <span class="site-detail-link">Xem chi tiết →</span>
                                </div>
                            </div>
                        </a>
                        <button class="site-favorite" type="button" data-favorite="{{ $listing->id }}" aria-label="Lưu tin {{ $listing->title }}" aria-pressed="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5S4 16 4 9.7A4.2 4.2 0 0 1 11.2 6L12 7l.8-1A4.2 4.2 0 0 1 20 9.7c0 6.3-8 10.8-8 10.8Z"/></svg>
                        </button>
                    </article>
                @empty
                    <div class="site-empty">
                        <h3>Chưa có phòng phù hợp</h3>
                        <p>Hãy bỏ bớt điều kiện lọc để xem thêm phòng đang có sẵn.</p>
                        <a class="site-search-btn" href="{{ route('site.home') }}#danh-sach">Xóa bộ lọc</a>
                    </div>
                @endforelse
            </div>

            @if($listings->hasPages())
                <nav class="site-pagination" aria-label="Phân trang">
                    @if($listings->onFirstPage())<span>‹</span>@else<a href="{{ $listings->previousPageUrl() }}#danh-sach">‹</a>@endif
                    @foreach($listings->getUrlRange(max(1, $listings->currentPage() - 2), min($listings->lastPage(), $listings->currentPage() + 2)) as $page => $url)
                        @if($page === $listings->currentPage())<span class="is-current">{{ $page }}</span>@else<a href="{{ $url }}#danh-sach">{{ $page }}</a>@endif
                    @endforeach
                    @if($listings->hasMorePages())<a href="{{ $listings->nextPageUrl() }}#danh-sach">›</a>@else<span>›</span>@endif
                </nav>
            @endif
        </div>
    </section>

    <section class="site-section site-about" id="gioi-thieu">
        <div class="site-shell site-about-grid">
            <div class="site-about-art">
                <div class="site-about-stat"><strong>{{ number_format($listings->total()) }}+</strong><span>phòng trọ đang được quản lý trên hệ thống</span></div>
            </div>
            <div class="site-about-copy">
                <span class="site-eyebrow" style="color:#453600;border-color:#e2bd43;background:#fff4c8"><i></i> Giới thiệu</span>
                <h2>Tìm trọ bớt vòng vo, thông tin vừa đủ để quyết định.</h2>
                <p>Website tập trung riêng cho nhu cầu thuê trọ tại TP.HCM. Mỗi tin có giá thuê, dạng phòng, nội thất, khu vực và hình ảnh rõ ràng trước khi bạn liên hệ xem phòng.</p>
                <div class="site-points">
                    <div class="site-point"><i>✓</i> Không yêu cầu đăng nhập để xem tin</div>
                    <div class="site-point"><i>✓</i> Bộ lọc theo quận, phường và giá thuê</div>
                    <div class="site-point"><i>✓</i> Liên hệ trực tiếp với người quản lý</div>
                </div>
            </div>
        </div>
    </section>

    <section class="site-section site-contact" id="lien-he">
        <div class="site-shell site-contact-grid">
            <div>
                <h2>Bạn cần hỗ trợ tìm phòng?</h2>
                <p>Liên hệ trực tiếp để được kiểm tra phòng còn trống và sắp xếp lịch xem phòng tại TP.HCM.</p>
            </div>
            @php
                $contactPhone = $siteContact['phone'] ?: config('app.contact_phone', '0900000000');
                $zaloUrl = $siteContact['zaloHref']($contactPhone);
            @endphp
            <div class="site-contact-actions">
                <a href="tel:{{ preg_replace('/\D+/', '', $contactPhone) }}"><small>Điện thoại</small><strong>{{ $contactPhone }}</strong></a>
                @if($zaloUrl)
                    <a class="site-contact-zalo" href="{{ $zaloUrl }}" target="_blank" rel="noopener">
                        <img class="site-zalo-mark" src="https://img.icons8.com/?size=100&id=0m71tmRjlxEe&format=png&color=000000" alt="Zalo" loading="lazy">
                        <span><small>Zalo</small><strong>Chat Zalo</strong></span>
                    </a>
                @endif
                @php($contactEmail = $siteContact['email'] ?: config('app.contact_email', 'hello@nhatrosv.com'))
                <a href="mailto:{{ $contactEmail }}"><small>Email</small><strong>{{ $contactEmail }}</strong></a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    (() => {
        const slides = [...document.querySelectorAll('[data-slide]')];
        const dots = [...document.querySelectorAll('[data-dots] button')];
        if (slides.length < 2) return;
        let current = 0;
        const show = (index) => {
            current = index;
            slides.forEach((slide, i) => slide.classList.toggle('is-active', i === current));
            dots.forEach((dot, i) => dot.classList.toggle('is-active', i === current));
        };
        dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));
        setInterval(() => show((current + 1) % slides.length), 2000);
    })();

    (() => {
        const storageKey = 'site-favorite-listings';
        let favorites = [];
        try { favorites = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (error) {}

        document.querySelectorAll('[data-favorite]').forEach((button) => {
            const id = String(button.dataset.favorite);
            const render = () => {
                const active = favorites.includes(id);
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
            };
            render();
            button.addEventListener('click', () => {
                favorites = favorites.includes(id) ? favorites.filter((item) => item !== id) : [...favorites, id];
                localStorage.setItem(storageKey, JSON.stringify(favorites));
                render();
            });
        });
    })();

    (() => {
        const modal = document.querySelector('[data-filter-modal]');
        if (! modal) return;

        const openButtons = document.querySelectorAll('[data-filter-open]');
        const closeButtons = modal.querySelectorAll('[data-filter-close]');
        const districtSelect = modal.querySelector('[data-mobile-district]');
        const wardSelect = modal.querySelector('[data-mobile-ward]');
        const wardOptions = @json($wardOptions);

        const open = () => {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('site-filter-lock');
        };
        const close = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('site-filter-lock');
        };

        const renderWards = (districtId, selected = '') => {
            const options = wardOptions[districtId] || [];
            wardSelect.innerHTML = '<option value="">Tất cả</option>';
            options.forEach((ward) => {
                const option = document.createElement('option');
                option.value = ward.id;
                option.textContent = ward.name;
                option.selected = String(ward.id) === String(selected);
                wardSelect.appendChild(option);
            });
        };

        openButtons.forEach((button) => button.addEventListener('click', open));
        closeButtons.forEach((button) => button.addEventListener('click', close));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });

        districtSelect?.addEventListener('change', () => {
            renderWards(districtSelect.value);
        });

        modal.querySelectorAll('input[type="radio"][name="price"]').forEach((radio) => {
            radio.dataset.wasChecked = radio.checked ? 'true' : 'false';
            radio.addEventListener('click', () => {
                if (radio.dataset.wasChecked === 'true') {
                    radio.checked = false;
                    radio.dataset.wasChecked = 'false';
                    return;
                }

                modal.querySelectorAll('input[type="radio"][name="price"]').forEach((item) => {
                    item.dataset.wasChecked = 'false';
                });
                radio.dataset.wasChecked = 'true';
            });
        });
    })();
</script>
@endpush
