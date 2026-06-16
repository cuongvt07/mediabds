@extends('site.layout')

@php
    $images = is_array($listing->images) ? $listing->images : [];
    if ($listing->avatar && ! in_array($listing->avatar, $images, true)) array_unshift($images, $listing->avatar);
    $imageUrl = fn ($path) => str_starts_with((string) $path, 'http') ? $path : asset('storage/' . ltrim((string) $path, '/'));
    if (empty($images)) $images = ['https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1400&q=85'];
    $roomLabels = ['duplex' => 'Duplex', 'studio' => 'Studio', 'loft' => 'Phòng có gác', 'balcony' => 'Phòng ban công'];
    $furnishLabels = ['full' => 'Đầy đủ nội thất', 'basic' => 'Nội thất cơ bản', 'empty' => 'Phòng trống'];
    // $amenities (truyền từ controller) = danh mục CMS; $listingAmenities = key đã chọn trên tin.
    $listingAmenities = is_array($listing->amenities) ? $listing->amenities : [];
    $amenityIconHtml = function ($item) {
        if (! empty($item->icon)) {
            return '<img src="' . e($item->icon) . '" alt="' . e($item->name) . '" class="site-amenity-img" loading="lazy">';
        }
        return '<i>' . e(mb_substr($item->name, 0, 1)) . '</i>';
    };
    $location = implode(', ', array_filter([$listing->address, $listing->ward_name, $listing->district_name, 'TP.HCM']));
@endphp

@section('title', $listing->title . ' - nhatrosv.com')

@section('content')
    <section class="site-detail-page">
        <div class="site-shell">
            <a class="site-back" href="{{ route('site.home') }}#danh-sach">← Quay lại danh sách phòng</a>

            <div class="site-detail-layout">
                <div class="site-detail-content">
                    <div class="site-detail-gallery">
                        <div class="site-detail-main">
                            <img src="{{ $imageUrl($images[0]) }}" alt="Hình ảnh {{ $listing->title }}">
                        </div>
                        <div class="site-detail-main">
                            <img src="{{ $imageUrl($images[1] ?? $images[0]) }}" alt="Hình ảnh {{ $listing->title }}">
                        </div>
                        <div class="site-detail-thumbs">
                            @foreach(array_slice($images, 0, 5) as $path)
                                <img class="{{ $loop->first ? 'is-active' : '' }}" src="{{ $imageUrl($path) }}" alt="Ảnh nhỏ {{ $loop->iteration }}">
                            @endforeach
                        </div>
                    </div>

                    <section class="site-detail-box">
                        <h1>{{ $listing->title }}</h1>
                        <div class="site-detail-line">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8Zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/></svg>
                            <span>{{ $location ?: 'TP.HCM' }}</span>
                        </div>
                        <div class="site-detail-price-row">
                            <span>Giá</span>
                            <strong>{{ number_format((float) $listing->price, 0, ',', '.') }}</strong>
                            <small>VNĐ/tháng</small>
                        </div>
                        <div class="site-detail-tags">
                            @if($listing->code)<span>Mã {{ $listing->code }}</span>@endif
                            <span>{{ $roomLabels[$listing->room_type] ?? 'Phòng trọ' }}</span>
                            <span>{{ $furnishLabels[$listing->furnish] ?? 'Nội thất: liên hệ' }}</span>
                            <span>Đã xác thực</span>
                        </div>
                        <div class="site-detail-amenities">
                            @foreach($amenities as $item)
                                <div class="{{ in_array($item->key, $listingAmenities, true) ? 'is-active' : '' }}">
                                    {!! $amenityIconHtml($item) !!}
                                    <span>{{ $item->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="site-detail-box">
                        <h2>Chi phí & điều kiện</h2>
                        <div class="site-detail-costs">
                            <div><span>Điện</span><strong>Liên hệ</strong></div>
                            <div><span>Nước</span><strong>Liên hệ</strong></div>
                            <div><span>Internet</span><strong>{{ in_array('wifi', $listingAmenities, true) ? 'Có Wifi' : 'Liên hệ' }}</strong></div>
                            <div><span>Giữ xe</span><strong>Liên hệ</strong></div>
                        </div>
                    </section>

                    <section class="site-detail-box">
                        <h2>Thông tin chi tiết <span>{{ $roomLabels[$listing->room_type] ?? 'Phòng trọ' }}</span></h2>
                        <div class="site-detail-info-grid">
                            <div>Toilet: <strong>{{ $listing->toilets ?: 'Liên hệ' }}</strong></div>
                            <div>Giờ giấc: <strong>Tự do</strong></div>
                            <div>Máy lạnh: <strong>{{ in_array('air_conditioner', $listingAmenities, true) ? 'Có' : 'Không' }}</strong></div>
                            <div>Cửa sổ: <strong>Liên hệ</strong></div>
                            <div>Ban công: <strong>{{ $listing->room_type === 'balcony' ? 'Có' : 'Không' }}</strong></div>
                            <div>Thú cưng: <strong>Liên hệ</strong></div>
                            <div>Để xe: <strong>Liên hệ</strong></div>
                            <div>Phòng ngủ: <strong>{{ $listing->bedrooms ?: 'Liên hệ' }}</strong></div>
                            <div>Nội thất: <strong>{{ $furnishLabels[$listing->furnish] ?? 'Liên hệ' }}</strong></div>
                        </div>
                    </section>

                    <section class="site-detail-box">
                        <h2>Mô tả tóm tắt</h2>
                        <div class="site-description">{{ $listing->description ?: 'Liên hệ để nhận thêm thông tin và lịch xem phòng.' }}</div>
                        @if($listing->google_map_link)
                            <a class="site-call-button site-map-button" href="{{ $listing->google_map_link }}" target="_blank" rel="noopener">Mở Google Maps</a>
                        @endif
                    </section>
                </div>

                <aside class="site-detail-sidebar">
                    <div class="site-detail-contact-card">
                        <span>Thông tin liên hệ</span>
                        <strong>{{ $listing->contact_type ?: 'Quản lý phòng' }}</strong>
                        @if($listing->contact_phone)
                            @php $zaloUrl = $siteContact['zaloHref']($listing->contact_phone); @endphp
                            <div class="site-contact-phone-row">
                                <a href="tel:{{ preg_replace('/\D+/', '', $listing->contact_phone) }}">{{ $listing->contact_phone }}</a>
                                @if($zaloUrl)
                                    <a class="site-zalo-btn" href="{{ $zaloUrl }}" target="_blank" rel="noopener" title="Chat Zalo" aria-label="Chat Zalo">
                                        <img src="https://img.icons8.com/?size=100&id=0m71tmRjlxEe&format=png&color=000000" alt="Zalo" loading="lazy">
                                    </a>
                                @endif
                            </div>
                        @else
                            <p>Liên hệ để được tư vấn xem phòng.</p>
                        @endif
                        <div>
                            <small>Giá thuê</small>
                            <b>{{ number_format((float) $listing->price, 0, ',', '.') }} VNĐ/tháng</b>
                        </div>
                        @if($listing->contact_phone)
                            <a class="site-detail-call" href="tel:{{ preg_replace('/\D+/', '', $listing->contact_phone) }}">Gọi ngay</a>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="site-section site-about">
            <div class="site-shell">
                <div class="site-section-head"><div><small>Cùng khu vực</small><h2>Phòng khác bạn có thể xem</h2></div></div>
                <div class="site-listing-grid">
                    @foreach($related as $item)
                        @php $itemImages = is_array($item->images) ? $item->images : []; $cover = $itemImages[0] ?? $item->avatar ?? $images[0]; @endphp
                        <a class="site-card" href="{{ route('site.listings.show', $item) }}">
                            <div class="site-card-media"><img src="{{ $imageUrl($cover) }}" alt="{{ $item->title }}" loading="lazy"><span class="site-badge">{{ $roomLabels[$item->room_type] ?? 'Phòng trọ' }}</span></div>
                            <div class="site-card-body"><h3 class="site-card-title">{{ $item->title }}</h3><div class="site-card-foot"><strong class="site-price">{{ number_format((float) $item->price, 0, ',', '.') }} <span>VNĐ/tháng</span></strong><span class="site-detail-link">Xem →</span></div></div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
