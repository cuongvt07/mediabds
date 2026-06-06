<div class="h-full flex flex-col bg-slate-50 relative">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 shrink-0">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-600">Website public</p>
                <h1 class="mt-1 text-xl sm:text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-emerald-100 text-emerald-600 rounded-xl">
                        <i class="fa-solid fa-globe"></i>
                    </span>
                    Module website BĐS
                </h1>
            </div>
            <a href="{{ route('docs.api') }}" target="_blank"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-[11px] font-black uppercase tracking-widest text-slate-600 hover:border-emerald-500 hover:text-emerald-600">
                <i class="fa-solid fa-code"></i> API Docs
            </a>
        </div>

        @if (session()->has('message'))
            <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"
                x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('message') }}
            </div>
        @endif
    </div>

    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 overflow-x-auto shrink-0">
        <div class="flex min-w-max gap-1">
            @foreach ([
                'overview' => ['Tổng quan', 'fa-chart-pie'],
                'listings' => ['Tin public', 'fa-newspaper'],
                'categories' => ['Danh mục', 'fa-layer-group'],
                'blogs' => ['Blog', 'fa-pen-nib'],
                'leads' => ['Lead', 'fa-address-book'],
                'favorites' => ['Yêu thích', 'fa-heart'],
                'saved-searches' => ['Tìm kiếm lưu', 'fa-bookmark'],
                'analytics' => ['Analytics', 'fa-chart-line'],
            ] as $tab => $meta)
                <button wire:click="setTab('{{ $tab }}')"
                    class="px-4 py-4 text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === $tab ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-400 hover:text-slate-700' }}">
                    <i class="fa-solid {{ $meta[1] }} mr-2"></i>{{ $meta[0] }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="flex-1 overflow-auto p-4 sm:p-6">
        @if ($activeTab === 'overview')
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-website-stat label="Tin đang hiển thị" :value="$stats['public_listings']" icon="fa-newspaper" />
                <x-website-stat label="Tin chờ duyệt" :value="$stats['pending_listings']" icon="fa-hourglass-half" />
                <x-website-stat label="Danh mục" :value="$stats['categories']" icon="fa-layer-group" />
                <x-website-stat label="Bài blog" :value="$stats['blogs']" icon="fa-pen-nib" />
                <x-website-stat label="Lead website" :value="$stats['leads']" icon="fa-address-book" />
                <x-website-stat label="Lead mới" :value="$stats['open_leads']" icon="fa-bell" />
                <x-website-stat label="Yêu thích" :value="$stats['favorites']" icon="fa-heart" />
                <x-website-stat label="Lượt xem" :value="$stats['views']" icon="fa-eye" />
            </div>

            <div class="mt-6 grid grid-cols-1 xl:grid-cols-2 gap-6">
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Tin mới từ website</h2>
                        <button wire:click="setTab('listings')" class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Xem tất cả</button>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($recentListings as $listing)
                            <div class="px-5 py-3 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-800">{{ $listing->title }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $listing->code ?? '#' . $listing->id }} · {{ number_format((float) $listing->price, 2) }} {{ $listing->price_unit }}</p>
                                </div>
                                <span class="rounded-lg bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-600 uppercase">
                                    {{ $listing->status ?? ($listing->is_sold ? 'sold' : 'active') }}
                                </span>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-400">Chưa có dữ liệu tin website.</div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Lead mới</h2>
                        <button wire:click="setTab('leads')" class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Xử lý lead</button>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($recentLeads as $lead)
                            <button wire:click="openLead({{ $lead->id }})" class="block w-full px-5 py-3 text-left hover:bg-slate-50">
                                <p class="text-sm font-bold text-slate-800">{{ $lead->name ?? 'Khách website' }} · {{ $lead->phone ?? '-' }}</p>
                                <p class="mt-1 line-clamp-1 text-xs text-slate-400">{{ $lead->message ?? 'Không có ghi chú' }}</p>
                            </button>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-400">Chưa có lead từ website.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        @elseif ($activeTab === 'listings')
            <section class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 flex flex-col lg:flex-row gap-3 lg:items-center">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input wire:model.live.debounce.300ms="listingSearch" type="text" placeholder="Tìm tiêu đề, mã tin, số điện thoại..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-semibold outline-none focus:border-emerald-500 focus:bg-white">
                    </div>
                    <select wire:model.live="listingStatus" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-black uppercase text-slate-600">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="pending">Chờ duyệt</option>
                        <option value="active">Đang hiển thị</option>
                        <option value="expired">Hết hạn</option>
                        <option value="sold">Đã bán/thuê</option>
                    </select>
                    <select wire:model.live="listingVip" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-black uppercase text-slate-600">
                        <option value="all">Tất cả VIP</option>
                        <option value="normal">Normal</option>
                        <option value="vip1">VIP 1</option>
                        <option value="vip2">VIP 2</option>
                        <option value="vip3">VIP 3</option>
                    </select>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] text-left">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <tr>
                                    <th class="px-5 py-4">Tin đăng</th>
                                    <th class="px-5 py-4">Giá/DT</th>
                                    <th class="px-5 py-4">Trạng thái</th>
                                    <th class="px-5 py-4">VIP</th>
                                    <th class="px-5 py-4">View</th>
                                    <th class="px-5 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse ($listings as $listing)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-800 line-clamp-1">{{ $listing->title }}</p>
                                            <p class="mt-1 text-[11px] font-mono text-slate-400">{{ $listing->code ?? '#' . $listing->id }} · {{ $listing->province_name ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-mono font-bold text-emerald-700">{{ number_format((float) $listing->price, 2) }} {{ $listing->price_unit }}</p>
                                            <p class="text-xs text-slate-400">{{ $listing->area }} m²</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <select wire:change="updateListingStatus({{ $listing->id }}, $event.target.value)"
                                                class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-[11px] font-black uppercase text-slate-600">
                                                @foreach (['pending' => 'Chờ duyệt', 'active' => 'Hiển thị', 'expired' => 'Hết hạn', 'sold' => 'Đã xong'] as $value => $label)
                                                    <option value="{{ $value }}" @selected(($listing->is_sold ? 'sold' : ($listing->status ?? 'active')) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-5 py-4">
                                            <select wire:change="updateListingVip({{ $listing->id }}, $event.target.value)"
                                                class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-[11px] font-black uppercase text-slate-600">
                                                @foreach (['normal', 'vip1', 'vip2', 'vip3'] as $value)
                                                    <option value="{{ $value }}" @selected(($listing->vip_tier ?? 'normal') === $value)>{{ strtoupper($value) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-5 py-4 font-mono text-slate-600">{{ number_format((int) ($listing->view_count ?? 0)) }}</td>
                                        <td class="px-5 py-4 text-right">
                                            <button wire:click="deleteListing({{ $listing->id }})" wire:confirm="Xóa tin website này?"
                                                class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-[10px] font-black uppercase text-red-600 hover:bg-red-100">
                                                Xóa
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Không có tin phù hợp.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-3">{{ $listings->links(data: ['scrollTo' => false]) }}</div>
                </div>
            </section>
        @elseif ($activeTab === 'categories')
            <section class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 flex flex-col sm:flex-row gap-3 sm:items-center">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input wire:model.live.debounce.300ms="categorySearch" type="text" placeholder="Tìm danh mục..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-semibold outline-none focus:border-emerald-500 focus:bg-white">
                    </div>
                    <button wire:click="createCategory"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-black uppercase tracking-widest text-white hover:bg-emerald-700">
                        <i class="fa-solid fa-plus"></i> Thêm danh mục
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[860px] text-left">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <tr>
                                    <th class="px-5 py-4">ID</th>
                                    <th class="px-5 py-4">Tên</th>
                                    <th class="px-5 py-4">Slug</th>
                                    <th class="px-5 py-4">Giao dịch</th>
                                    <th class="px-5 py-4">Loại BĐS</th>
                                    <th class="px-5 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse ($categories as $category)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $category->id }}</td>
                                        <td class="px-5 py-3 font-bold text-slate-800">{{ $category->name }}</td>
                                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $category->slug }}</td>
                                        <td class="px-5 py-3">{{ $category->transaction_type }}</td>
                                        <td class="px-5 py-3">{{ $category->property_type ?? '-' }}</td>
                                        <td class="px-5 py-3 text-right space-x-2">
                                            <button wire:click="editCategory('{{ $category->id }}')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[10px] font-black uppercase text-slate-600 hover:border-emerald-500 hover:text-emerald-600">Sửa</button>
                                            <button wire:click="deleteCategory('{{ $category->id }}')" wire:confirm="Xóa danh mục này?" class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-[10px] font-black uppercase text-red-600">Xóa</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Chưa có danh mục.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-3">{{ $categories->links(data: ['scrollTo' => false]) }}</div>
                </div>
            </section>
        @elseif ($activeTab === 'blogs')
            <section class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 flex flex-col lg:flex-row gap-3 lg:items-center">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input wire:model.live.debounce.300ms="blogSearch" type="text" placeholder="Tìm bài viết..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-semibold outline-none focus:border-emerald-500 focus:bg-white">
                    </div>
                    <select wire:model.live="blogStatus" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-black uppercase text-slate-600">
                        <option value="all">Tất cả</option>
                        <option value="published">Đã đăng</option>
                        <option value="draft">Nháp</option>
                        <option value="archived">Lưu trữ</option>
                    </select>
                    <button wire:click="createBlog"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-black uppercase tracking-widest text-white hover:bg-emerald-700">
                        <i class="fa-solid fa-plus"></i> Viết bài
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] text-left">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <tr>
                                    <th class="px-5 py-4">Bài viết</th>
                                    <th class="px-5 py-4">Tag</th>
                                    <th class="px-5 py-4">Trạng thái</th>
                                    <th class="px-5 py-4">Xuất bản</th>
                                    <th class="px-5 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse ($blogs as $post)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-800 line-clamp-1">{{ $post->title }}</p>
                                            <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $post->slug }}</p>
                                        </td>
                                        <td class="px-5 py-4">{{ $post->category_tag ?? '-' }}</td>
                                        <td class="px-5 py-4">
                                            <button wire:click="toggleBlogStatus({{ $post->id }})"
                                                class="rounded-lg px-2 py-1 text-[10px] font-black uppercase {{ $post->status === 'published' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $post->status }}
                                            </button>
                                        </td>
                                        <td class="px-5 py-4 text-xs text-slate-500">{{ optional($post->published_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td class="px-5 py-4 text-right space-x-2">
                                            <button wire:click="editBlog({{ $post->id }})" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[10px] font-black uppercase text-slate-600 hover:border-emerald-500 hover:text-emerald-600">Sửa</button>
                                            <button wire:click="deleteBlog({{ $post->id }})" wire:confirm="Xóa bài viết này?" class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-[10px] font-black uppercase text-red-600">Xóa</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Chưa có bài viết.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-3">{{ $blogs->links(data: ['scrollTo' => false]) }}</div>
                </div>
            </section>
        @elseif ($activeTab === 'leads')
            <section class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 flex flex-col lg:flex-row gap-3 lg:items-center">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input wire:model.live.debounce.300ms="leadSearch" type="text" placeholder="Tìm khách, số điện thoại, nội dung..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-semibold outline-none focus:border-emerald-500 focus:bg-white">
                    </div>
                    <select wire:model.live="leadStatus" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-black uppercase text-slate-600">
                        <option value="all">Tất cả lead</option>
                        <option value="new">Mới</option>
                        <option value="contacted">Đã liên hệ</option>
                        <option value="qualified">Tiềm năng</option>
                        <option value="closed">Đã chốt</option>
                        <option value="spam">Spam</option>
                    </select>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] text-left">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <tr>
                                    <th class="px-5 py-4">Khách</th>
                                    <th class="px-5 py-4">Nội dung</th>
                                    <th class="px-5 py-4">Trạng thái</th>
                                    <th class="px-5 py-4">Nguồn</th>
                                    <th class="px-5 py-4">Ngày</th>
                                    <th class="px-5 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse ($leads as $lead)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-800">{{ $lead->name }}</p>
                                            <p class="font-mono text-xs text-slate-400">{{ $lead->phone }}</p>
                                        </td>
                                        <td class="px-5 py-4 max-w-md">
                                            <p class="line-clamp-2 text-slate-600">{{ $lead->message ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <select wire:change="quickLeadStatus({{ $lead->id }}, $event.target.value)"
                                                class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-[11px] font-black uppercase text-slate-600">
                                                @foreach (['new' => 'Mới', 'contacted' => 'Đã gọi', 'qualified' => 'Tiềm năng', 'closed' => 'Đã chốt', 'spam' => 'Spam'] as $value => $label)
                                                    <option value="{{ $value }}" @selected(($lead->status ?? 'new') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-5 py-4 text-xs text-slate-500">{{ $lead->source ?? 'website' }}</td>
                                        <td class="px-5 py-4 text-xs text-slate-500">{{ optional($lead->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-4 text-right space-x-2">
                                            <button wire:click="openLead({{ $lead->id }})" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[10px] font-black uppercase text-slate-600 hover:border-emerald-500 hover:text-emerald-600">Ghi chú</button>
                                            <button wire:click="deleteLead({{ $lead->id }})" wire:confirm="Xóa lead này?" class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-[10px] font-black uppercase text-red-600">Xóa</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Chưa có lead phù hợp.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-3">{{ $leads->links(data: ['scrollTo' => false]) }}</div>
                </div>
            </section>
        @elseif ($activeTab === 'favorites')
            <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Người dùng yêu thích tin</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left">
                        <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <tr>
                                <th class="px-5 py-4">Người dùng</th>
                                <th class="px-5 py-4">Tin</th>
                                <th class="px-5 py-4">Mã tin</th>
                                <th class="px-5 py-4">Ngày lưu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($favorites as $item)
                                <tr>
                                    <td class="px-5 py-3 font-bold text-slate-800">{{ $item->user_name ?? 'User #' . $item->user_id }} <span class="font-mono text-xs text-slate-400">{{ $item->user_phone }}</span></td>
                                    <td class="px-5 py-3">{{ $item->listing_title ?? 'Tin #' . $item->listing_id }}</td>
                                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $item->listing_code ?? '-' }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-500">{{ $item->created_at }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Chưa có dữ liệu yêu thích.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-3">{{ $favorites->links(data: ['scrollTo' => false]) }}</div>
            </section>
        @elseif ($activeTab === 'saved-searches')
            <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Tìm kiếm đã lưu</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left">
                        <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <tr>
                                <th class="px-5 py-4">Người dùng</th>
                                <th class="px-5 py-4">Tên bộ lọc</th>
                                <th class="px-5 py-4">Params</th>
                                <th class="px-5 py-4">Ngày lưu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($savedSearches as $item)
                                <tr>
                                    <td class="px-5 py-3 font-bold text-slate-800">{{ $item->user_name ?? 'User #' . $item->user_id }} <span class="font-mono text-xs text-slate-400">{{ $item->user_phone }}</span></td>
                                    <td class="px-5 py-3">{{ $item->label }}</td>
                                    <td class="px-5 py-3"><code class="text-[11px] text-slate-500">{{ \Illuminate\Support\Str::limit($item->params, 120) }}</code></td>
                                    <td class="px-5 py-3 text-xs text-slate-500">{{ $item->created_at }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Chưa có tìm kiếm đã lưu.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-3">{{ $savedSearches->links(data: ['scrollTo' => false]) }}</div>
            </section>
        @elseif ($activeTab === 'analytics')
            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Top tin nhiều lượt xem</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($topViewedListings as $listing)
                            <div class="px-5 py-3 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-800">{{ $listing->title }}</p>
                                    <p class="text-[11px] font-mono text-slate-400">{{ $listing->code ?? '#' . $listing->id }}</p>
                                </div>
                                <span class="font-mono text-sm font-black text-emerald-600">{{ number_format((int) ($listing->view_count ?? 0)) }}</span>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-400">Chưa có dữ liệu lượt xem.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Lượt xem 14 ngày</h2>
                    </div>
                    <div class="p-5 space-y-3">
                        @forelse ($dailyViews as $row)
                            @php $width = min(100, max(8, (int) $row->total * 8)); @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs font-bold text-slate-500">
                                    <span>{{ $row->day }}</span>
                                    <span>{{ number_format((int) $row->total) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center text-sm text-slate-400">Chưa có analytics view event.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif
    </div>

    @if ($showCategoryModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeCategoryModal"></div>
            <form wire:submit.prevent="saveCategory" class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 uppercase tracking-widest text-sm">{{ $categoryEditing ? 'Sửa danh mục' : 'Thêm danh mục' }}</h3>
                    <button type="button" wire:click="closeCategoryModal" class="text-slate-400 hover:text-red-500"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-admin-field label="ID" model="categoryId" :disabled="$categoryEditing" />
                    <x-admin-field label="Tên danh mục" model="categoryName" />
                    <x-admin-field label="Slug" model="categorySlug" />
                    <x-admin-field label="Icon FontAwesome" model="categoryIcon" />
                    <div>
                        <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">Giao dịch</label>
                        <select wire:model="categoryTransactionType" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
                            <option value="both">Cả hai</option>
                            <option value="rent">Cho thuê</option>
                            <option value="sale">Mua bán</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">Loại BĐS</label>
                        <select wire:model="categoryPropertyType" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
                            <option value="">Không cố định</option>
                            <option value="apartment">Căn hộ</option>
                            <option value="room">Phòng trọ</option>
                            <option value="house">Nhà</option>
                            <option value="office">Văn phòng</option>
                            <option value="land">Đất</option>
                            <option value="shared">Ở ghép</option>
                        </select>
                    </div>
                    <x-admin-field label="Thứ tự" model="categorySortOrder" type="number" />
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" wire:click="closeCategoryModal" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black uppercase text-slate-600">Hủy</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black uppercase text-white">Lưu</button>
                </div>
            </form>
        </div>
    @endif

    @if ($showBlogModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeBlogModal"></div>
            <form wire:submit.prevent="saveBlog" class="relative w-full max-w-5xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <h3 class="font-black text-slate-800 uppercase tracking-widest text-sm">{{ $blogEditingId ? 'Sửa bài blog' : 'Viết bài blog' }}</h3>
                    <button type="button" wire:click="closeBlogModal" class="text-slate-400 hover:text-red-500"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2 space-y-4">
                        <x-admin-field label="Tiêu đề" model="blogTitle" />
                        <x-admin-field label="Slug" model="blogSlug" />
                        <div>
                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">Tóm tắt</label>
                            <textarea wire:model="blogExcerpt" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">Nội dung Markdown</label>
                            <textarea wire:model="blogContent" rows="14" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-mono"></textarea>
                            @error('blogContent') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="space-y-4">
                        <x-admin-field label="Ảnh bìa URL" model="blogCoverImage" />
                        <x-admin-field label="Tác giả" model="blogAuthorName" />
                        <x-admin-field label="Nhóm bài" model="blogCategoryTag" />
                        <x-admin-field label="Tags, cách nhau bằng dấu phẩy" model="blogTags" />
                        <x-admin-field label="Phút đọc" model="blogReadingMinutes" type="number" />
                        <div>
                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">Trạng thái</label>
                            <select wire:model="blogStatusValue" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
                                <option value="draft">Nháp</option>
                                <option value="published">Đã đăng</option>
                                <option value="archived">Lưu trữ</option>
                            </select>
                        </div>
                        <x-admin-field label="Ngày xuất bản" model="blogPublishedAt" type="datetime-local" />
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 sticky bottom-0 bg-white">
                    <button type="button" wire:click="closeBlogModal" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black uppercase text-slate-600">Hủy</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black uppercase text-white">Lưu bài viết</button>
                </div>
            </form>
        </div>
    @endif

    @if ($showLeadModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeLeadModal"></div>
            <form wire:submit.prevent="saveLead" class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-slate-800 uppercase tracking-widest text-sm">{{ $leadName }} · {{ $leadPhone }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $leadMessage ?: 'Không có nội dung khách để lại.' }}</p>
                    </div>
                    <button type="button" wire:click="closeLeadModal" class="text-slate-400 hover:text-red-500"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">Trạng thái xử lý</label>
                        <select wire:model="leadStatusValue" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
                            <option value="new">Mới</option>
                            <option value="contacted">Đã liên hệ</option>
                            <option value="qualified">Tiềm năng</option>
                            <option value="closed">Đã chốt</option>
                            <option value="spam">Spam</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">Ghi chú nội bộ</label>
                        <textarea wire:model="leadAdminNote" rows="6" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" wire:click="closeLeadModal" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black uppercase text-slate-600">Hủy</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black uppercase text-white">Lưu xử lý</button>
                </div>
            </form>
        </div>
    @endif
</div>
