@props(['vehicle', 'isAdmin' => false, 'mode' => 'grid'])

@php
    $vehicleTypes = \App\Models\VehicleListing::VEHICLE_TYPES;
    $transmissions = \App\Models\VehicleListing::TRANSMISSIONS;
    $conditions = \App\Models\VehicleListing::CONDITIONS;

    $priceUnit = $vehicle['price_unit'] ?? 'Triệu';
    $priceDisplay = ($vehicle['price'] ?? null) !== null && $vehicle['price'] !== ''
        ? number_format((float) $vehicle['price'], 0, ',', '.')
        : null;
@endphp

<div wire:key="vehicle-{{ $vehicle['id'] }}-{{ $vehicle['updated_at'] ?? now() }}"
    @if($mode === 'grid' && $isAdmin) wire:click="editListing({{ $vehicle['id'] }})" @endif
    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden flex flex-col md:flex-row h-auto {{ $mode === 'grid' ? 'md:h-52' : '' }} group {{ $isAdmin ? 'cursor-pointer' : '' }} relative animate-[fadeIn_0.3s_ease-out] mb-3 last:mb-0">

    @if($mode === 'grid')
    <!-- Left: Image (30%) -->
    <div class="w-full h-48 md:w-[30%] md:h-full bg-gray-200 relative overflow-hidden shrink-0">
        @php
            $avatarUrl = !empty($vehicle['avatar']) ? $vehicle['avatar'] : (!empty($vehicle['images']) && count($vehicle['images']) > 0 ? $vehicle['images'][0] : null);
            if ($avatarUrl && !str_starts_with($avatarUrl, 'http')) {
                $avatarUrl = url('storage/' . $avatarUrl);
            }
            $avatarUrl = $avatarUrl ?: 'https://placehold.co/400x300?text=No+Img';
        @endphp
        <img src="{{ $avatarUrl }}"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            loading="lazy" alt="{{ $vehicle['title'] }}">

        <!-- Badges -->
        <div class="absolute top-2 left-2 flex flex-col gap-1 z-20">
            @if(!empty($vehicle['type']))
                <span class="bg-blue-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                    {{ $vehicle['type'] }}
                </span>
            @endif
            @if(!empty($vehicle['vehicle_type']))
                <span class="bg-indigo-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                    {{ $vehicleTypes[$vehicle['vehicle_type']] ?? $vehicle['vehicle_type'] }}
                </span>
            @endif
            @if(!empty($vehicle['code']))
                <span class="bg-slate-800 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                    #{{ $vehicle['code'] }}
                </span>
            @endif
            @if(!empty($vehicle['is_sold']))
                <span class="bg-red-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> ĐÃ BÁN
                </span>
            @endif
        </div>

        <!-- VIP badge -->
        @if(!empty($vehicle['vip_tier']) && $vehicle['vip_tier'] !== 'normal')
            <div class="absolute top-2 right-2 z-20">
                <span class="bg-gradient-to-r from-amber-400 to-yellow-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider shadow-md flex items-center gap-1">
                    <i class="fa-solid fa-crown"></i> {{ strtoupper($vehicle['vip_tier']) }}
                </span>
            </div>
        @endif

        <!-- Video Overlay -->
        @if(!empty($vehicle['youtube_link']))
            <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/20 transition-colors pointer-events-none">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white ring-1 ring-white/50">
                    <i class="fa-solid fa-play ml-0.5"></i>
                </div>
            </div>
        @endif
    </div>
    @endif

    <!-- Right: Info -->
    <div class="w-full {{ $mode === 'grid' ? 'md:w-[70%]' : '' }} p-3 md:p-4 flex flex-col justify-between relative overflow-hidden">
        <div>
            <div class="flex justify-between items-start gap-2 mb-1.5">
                <h3 class="font-bold text-slate-800 text-sm md:text-base leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors" title="{{ $vehicle['title'] }}">
                    {{ $vehicle['title'] }}
                </h3>

                @if($mode === 'grid' && $isAdmin)
                <div class="flex items-center gap-1.5 shrink-0">
                    <button wire:click.stop="editListing({{ $vehicle['id'] }})"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 transition-colors bg-gray-50 md:bg-transparent">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </button>
                    <button wire:click.stop="toggleSold({{ $vehicle['id'] }})"
                        class="w-7 h-7 flex items-center justify-center rounded-lg {{ !empty($vehicle['is_sold']) ? 'text-slate-600' : 'text-emerald-600' }} hover:bg-emerald-50 transition-colors bg-gray-50 md:bg-transparent"
                        title="{{ !empty($vehicle['is_sold']) ? 'Mở bán lại' : 'Đánh dấu đã bán' }}">
                        <i class="fa-solid {{ !empty($vehicle['is_sold']) ? 'fa-rotate-left' : 'fa-circle-check' }}"></i>
                    </button>
                    <button wire:click.stop="deleteListing({{ $vehicle['id'] }})"
                        wire:confirm="Bạn có chắc chắn muốn xóa tin xe này?"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-red-600 hover:bg-red-50 transition-colors bg-gray-50 md:bg-transparent">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
                @endif
            </div>

            <!-- Location -->
            <p class="text-xs text-gray-500 font-medium truncate flex items-center gap-1 mb-2">
                <i class="fa-solid fa-location-dot text-gray-400"></i>
                {{ implode(', ', array_filter([$vehicle['ward_name'] ?? '', $vehicle['district_name'] ?? '', $vehicle['province_name'] ?? ''])) ?: 'Chưa cập nhật vị trí' }}
            </p>

            <!-- Price -->
            <div class="flex items-center gap-3 mb-2">
                <span class="font-black text-red-600 text-base">
                    @if($priceDisplay !== null && $priceUnit !== 'Thỏa thuận')
                        {{ $priceDisplay }}
                        <span class="text-[10px] font-normal text-gray-400">{{ $priceUnit }}</span>
                    @else
                        Thỏa thuận
                    @endif
                </span>
                @if(!empty($vehicle['brand']))
                    <span class="w-px h-3 bg-gray-200"></span>
                    <span class="text-sm font-bold text-slate-700">{{ $vehicle['brand'] }}{{ !empty($vehicle['model_name']) ? ' ' . $vehicle['model_name'] : '' }}</span>
                @endif
            </div>

            <!-- Vehicle facts -->
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-gray-500 font-medium mb-3">
                @if(!empty($vehicle['condition']))
                    <span class="bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase transition-colors">
                        {{ $conditions[$vehicle['condition']] ?? $vehicle['condition'] }}
                    </span>
                @endif
                @if(!empty($vehicle['year']))
                    <span><i class="fa-solid fa-calendar-days text-gray-400 mr-1"></i>{{ $vehicle['year'] }}</span>
                @endif
                @if(($vehicle['mileage'] ?? null) !== null && $vehicle['mileage'] !== '')
                    <span><i class="fa-solid fa-gauge-high text-gray-400 mr-1"></i>{{ number_format((int) $vehicle['mileage'], 0, ',', '.') }} km</span>
                @endif
                @if(!empty($vehicle['transmission']))
                    <span><i class="fa-solid fa-gears text-gray-400 mr-1"></i>{{ $transmissions[$vehicle['transmission']] ?? $vehicle['transmission'] }}</span>
                @endif
                @if(!empty($vehicle['seats']) && ($vehicle['vehicle_type'] ?? '') === 'car')
                    <span><i class="fa-solid fa-chair text-gray-400 mr-1"></i>{{ $vehicle['seats'] }} chỗ</span>
                @endif
                @if(!empty($vehicle['color']))
                    <span><i class="fa-solid fa-palette text-gray-400 mr-1"></i>{{ $vehicle['color'] }}</span>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-auto pt-3 border-t border-gray-50 flex justify-between items-center text-[10px] font-medium">
            @if(!empty($vehicle['user']))
                <div class="flex items-center gap-1.5">
                    <div class="shrink-0">
                        @php
                            $userAvatar = !empty($vehicle['user']['avatar']) ? $vehicle['user']['avatar'] : null;
                            if ($userAvatar && !str_starts_with($userAvatar, 'http')) {
                                $userAvatar = url('storage/' . $userAvatar);
                            }
                        @endphp
                        @if($userAvatar)
                            <img src="{{ $userAvatar }}" class="w-5 h-5 rounded-md object-cover ring-1 ring-gray-100 shadow-sm">
                        @else
                            <div class="w-5 h-5 rounded-md bg-slate-100 text-slate-600 flex items-center justify-center text-[8px] font-black">
                                {{ mb_substr($vehicle['user']['name'] ?? 'U', 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <span class="text-slate-500 font-bold truncate max-w-[80px] leading-tight">{{ $vehicle['user']['name'] ?? '' }}</span>
                </div>
            @else
                <span></span>
            @endif
            <div class="flex items-center gap-2">
                @if(!empty($vehicle['contact_phone']))
                    <a href="tel:{{ $vehicle['contact_phone'] }}" @click.stop
                       class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors text-[10px] font-bold"
                       title="Gọi {{ $vehicle['contact_phone'] }}">
                        <i class="fa-solid fa-phone"></i> Gọi
                    </a>
                @endif
                <span class="text-gray-400 italic">
                    {{ \Carbon\Carbon::parse($vehicle['created_at'] ?? now())->diffForHumans() }}
                </span>
            </div>
        </div>
    </div>
</div>
