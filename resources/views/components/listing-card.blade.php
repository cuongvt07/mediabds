@props(['listing', 'isAdmin' => false, 'mode' => 'grid'])

<div wire:key="listing-{{ $listing['id'] }}-{{ $listing['updated_at'] ?? now() }}"
    @if($mode === 'grid')
        wire:click="viewListingDetail({{ $listing['id'] }})"
    @else
        wire:click="$set('userInput', 'xem chi tiết tin #{{ $listing['id'] }}'); sendMessage();"
    @endif
    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden flex flex-col md:flex-row h-auto {{ $mode === 'grid' ? 'md:h-52' : 'md:h-48' }} group cursor-pointer relative animate-[fadeIn_0.3s_ease-out] mb-3 last:mb-0">
    
    <!-- Left: Image (30%) -->
    <div class="w-full h-48 md:w-[30%] md:h-full bg-gray-200 relative overflow-hidden shrink-0">
        <img src="{{ !empty($listing['avatar']) ? $listing['avatar'] : (!empty($listing['images']) && count($listing['images']) > 0 ? $listing['images'][0] : 'https://placehold.co/400x300?text=No+Img') }}"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            loading="lazy" alt="{{ $listing['title'] }}">

        <!-- Type & Code Badges -->
        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
            <span class="bg-blue-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                {{ $listing['type'] }}
            </span>
            @if (!empty($listing['contact_type']))
                <span class="bg-emerald-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                    {{ $listing['contact_type'] }}
                </span>
            @endif
            @if (!empty($listing['code']))
                <span class="bg-slate-800 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                    #{{ $listing['code'] }}
                </span>
            @endif
            @if (!empty($listing['is_sold']))
                <span class="bg-red-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> ĐÃ BÁN
                </span>
            @endif
        </div>

        <!-- Video Overlay -->
        @if(!empty($listing['youtube_link']))
            <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/20 transition-colors pointer-events-none">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white ring-1 ring-white/50">
                    <i class="fa-solid fa-play ml-0.5"></i>
                </div>
            </div>
        @endif
    </div>

    <!-- Right: Info (70%) -->
    <div class="w-full md:w-[70%] p-3 md:p-4 flex flex-col justify-between relative overflow-hidden">
        <div>
            <div class="flex justify-between items-start gap-2 mb-1.5">
                <h3 class="font-bold text-slate-800 text-sm md:text-base leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors" title="{{ $listing['title'] }}">
                    {{ $listing['title'] }}
                </h3>
                
                {{-- Quick Actions - Only show in grid or if admin --}}
                @if($mode === 'grid')
                <div class="flex items-center gap-1.5 shrink-0" x-data="{ copied: false }">
                    <button @click.stop="
                                const text = `🏠 {{ $listing['title'] }} \n📍 Vị trí: {{ implode(', ', array_filter([$listing['address'] ?? '', $listing['ward_name'] ?? '', $listing['district_name'] ?? '', $listing['province_name'] ?? ''])) }} \n💰 Giá: {{ number_format($listing['price'], 0, ',', '.') }} {{ ($listing['price_unit'] ?? 1) == 1 ? 'VNĐ' : (($listing['price_unit'] ?? 1) == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }} \n📐 Diện tích: {{ floatval($listing['area']) }} m² \n------------------ \n📋 Thông tin chi tiết: \n- Tầng: {{ $listing['floors'] ?? 0 }} \n- Phòng ngủ: {{ $listing['bedrooms'] ?? 0 }} \n- Toilet: {{ $listing['toilets'] ?? 0 }} \n- Hướng: {{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction'] ?? ''] ?? 'N/A' }} \n- Mặt tiền: {{ floatval($listing['front_width'] ?? 0) }}m \n- Lộ giới: {{ floatval($listing['road_width'] ?? 0) }}m \n------------------ \n📝 Mô tả: \n{{ $listing['description'] ?? '' }}`;
                                navigator.clipboard.writeText(text);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors bg-gray-50 md:bg-transparent"
                            title="Copy QC">
                        <i class="fa-regular fa-copy" x-show="!copied"></i>
                        <i class="fa-solid fa-check" x-show="copied" style="display: none;"></i>
                    </button>
                    @if ($isAdmin)
                        <button wire:click.stop="editListing({{ $listing['id'] }})"
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 transition-colors bg-gray-50 md:bg-transparent">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button wire:click.stop="deleteListing({{ $listing['id'] }})"
                            wire:confirm="Bạn có chắc chắn muốn xóa tin đăng này? Thao tác này có thể hoàn tác bởi Admin."
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-red-600 hover:bg-red-50 transition-colors bg-gray-50 md:bg-transparent">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    @endif
                </div>
                @endif
            </div>

            <p class="text-xs text-gray-500 font-medium truncate flex items-center gap-1 mb-2">
                <i class="fa-solid fa-location-dot text-gray-400"></i>
                {{ implode(', ', array_filter([$listing['ward_name'] ?? '', $listing['district_name'] ?? '', $listing['province_name'] ?? ''])) }}
            </p>

            <div class="flex items-center gap-3 mb-2">
                <span class="font-black text-red-600 text-base">
                    {{ number_format($listing['price'], 0, ',', '.') }}
                    <span class="text-[10px] font-normal text-gray-400">
                        {{ ($listing['price_unit'] ?? 1) == 1 ? 'VNĐ' : (($listing['price_unit'] ?? 1) == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }}
                    </span>
                </span>
                <span class="w-px h-3 bg-gray-200"></span>
                <span class="text-sm font-bold text-slate-700">{{ floatval($listing['area']) }} m²</span>
            </div>

            {{-- Facts --}}
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-gray-500 font-medium mb-3">
                <span class="bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase transition-colors">
                    {{ \App\Livewire\RealEstateListing::PROPERTY_TYPES[$listing['property_type'] ?? ''] ?? 'Khác' }}
                </span>
                @if($listing['floors'] ?? 0) <span><i class="fa-solid fa-layer-group text-gray-400 mr-1"></i>{{ $listing['floors'] }} T</span> @endif
                @if($listing['bedrooms'] ?? 0) <span><i class="fa-solid fa-bed text-gray-400 mr-1"></i>{{ $listing['bedrooms'] }} PN</span> @endif
                @if($listing['toilets'] ?? 0) <span><i class="fa-solid fa-restroom text-gray-400 mr-1"></i>{{ $listing['toilets'] }} WC</span> @endif
                @if($listing['direction'] ?? '') <span><i class="fa-regular fa-compass text-gray-400 mr-1"></i>{{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction']] ?? 'N/A' }}</span> @endif
                @if($listing['front_width'] ?? 0) <span><i class="fa-solid fa-ruler-horizontal text-gray-400 mr-1"></i>MT: {{ floatval($listing['front_width']) }}m</span> @endif
                @if($listing['road_width'] ?? 0) <span><i class="fa-solid fa-road text-gray-400 mr-1"></i>Lộ: {{ floatval($listing['road_width']) }}m</span> @endif
            </div>
        </div>

        {{-- Footer: Reporter & Timestamp --}}
        <div class="mt-auto pt-3 border-t border-gray-50 flex justify-between items-center text-[10px] font-medium">
            @if (!empty($listing['reporter']))
                <div class="flex items-center gap-1.5 {{ $mode === 'grid' ? 'cursor-pointer hover:bg-blue-50' : '' }} p-1 rounded transition-colors" 
                    @if($mode === 'grid') wire:click.stop="showReporterListings({{ $listing['reporter']['id'] }}, '{{ addslashes($listing['reporter']['name']) }}')" @endif>
                    <div class="relative shrink-0">
                        @if (!empty($listing['reporter']['avatar']))
                            <img src="{{ url('storage/' . $listing['reporter']['avatar']) }}" 
                                 class="w-5 h-5 rounded-md object-cover ring-1 ring-gray-100 shadow-sm">
                        @else
                            <div class="w-5 h-5 rounded-md bg-blue-100 text-blue-600 flex items-center justify-center text-[8px] font-black">
                                {{ mb_substr($listing['reporter']['name'], 0, 1) }}
                            </div>
                        @endif
                        <div class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-blue-600 rounded-full border border-white"></div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-slate-800 font-bold truncate max-w-[80px] leading-tight">{{ $listing['reporter']['name'] }}</span>
                        <span class="text-[8px] text-blue-500 uppercase tracking-tighter leading-none">Nguồn</span>
                    </div>
                </div>
            @elseif(!empty($listing['user']))
                <div class="flex items-center gap-1.5">
                    <div class="shrink-0">
                        @if (!empty($listing['user']['avatar']))
                            <img src="{{ url('storage/' . $listing['user']['avatar']) }}" 
                                 class="w-5 h-5 rounded-md object-cover ring-1 ring-gray-100 shadow-sm">
                        @else
                            <div class="w-5 h-5 rounded-md bg-slate-100 text-slate-600 flex items-center justify-center text-[8px] font-black">
                                {{ mb_substr($listing['user']['name'], 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <span class="text-slate-500 font-bold truncate max-w-[80px] leading-tight">{{ $listing['user']['name'] }}</span>
                </div>
            @endif
            <div class="flex items-center gap-2">
                @if (!empty($listing['facebook_link']))
                    <a href="{{ $listing['facebook_link'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors" title="Facebook New">
                        <i class="fa-brands fa-facebook"></i>
                    </a>
                @endif
                <span class="text-gray-400 italic">
                    {{ \Carbon\Carbon::parse($listing['created_at'] ?? now())->diffForHumans() }}
                </span>
            </div>
        </div>
    </div>
</div>
