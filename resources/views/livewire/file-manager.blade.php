<div class="flex h-full bg-white">
    <!-- Sidebar component within the view -->
    <aside class="w-64 border-r border-gray-100 flex flex-col hidden lg:flex shrink-0">
        <div class="p-5">
            <div wire:click="selectFolder(null)"
                class="flex items-center gap-2 text-blue-600 font-bold text-[18px] tracking-tight cursor-pointer">
                <svg class="w-8 h-8" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="20" fill="#2563EB" />
                    <path d="M12 20H28M20 12V28" stroke="white" stroke-width="3" stroke-linecap="round" />
                </svg>
                OPENFILES
            </div>
        </div>

        <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
            <button wire:click="toggleFlatView(true)"
                class="w-full flex items-center gap-3 px-5 py-3.5 {{ $isFlatView ? 'bg-blue-50/50 text-blue-600 border border-blue-100 shadow-sm rounded-xl' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-colors' }} group font-medium text-[15px]">
                <span class="group-hover:text-blue-500 font-bold">📄</span>
                <span>All files</span>
            </button>

            <!-- Global Loading Indicator -->
            <div wire:loading wire:target.except="loadMore" class="fixed bottom-6 right-6 z-[100]">
                <div class="bg-white/90 backdrop-blur p-3 rounded-full shadow-2xl border border-blue-50/50">
                    <div class="w-6 h-6 border-2 border-blue-600/10 border-t-blue-600 rounded-full animate-spin"></div>
                </div>
            </div>

            <!-- Directory Tree -->
            <div class="pt-6 pb-2 px-5">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Your Directories</p>
                <div class="space-y-1">
                    @php
                        $renderTree = function ($folders, $depth = 0) use (&$renderTree) {
                            foreach ($folders as $folder) {
                                $isExpanded = in_array($folder->id, $this->expandedFolders);
                                $isActive = $this->currentFolderId === $folder->id;

                                echo '<div x-data="{
                                    expanded: '.($isExpanded ? '
                                    true ' : '
                                    false ').'
                                }">';
                                echo '<div class="flex items-center gap-1 group">';
                                // Expand toggle
                                if ($folder->children->count() > 0) {
                                    echo '<button @click.stop="expanded = !expanded" class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all" :class="expanded ? \'rotate-90\' : \'\'">';
                                    echo '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>';
                                    echo '</button>';
                                } else {
                                    echo '<div class="w-2"></div>';
                                }

                                // Folder Link (Added Context Menu)
                                echo '<button wire:click="selectFolder(\'' .
                                    $folder->id .
                                    '\')" @contextmenu.prevent="$dispatch(\'contextmenu-folder\', { x: $event.clientX, y: $event.clientY, id: \'' .
                                    $folder->id .
                                    '\' });" class="flex-1 flex items-center gap-2 py-2 text-[14px] font-medium ' .
                                    ($isActive
                                        ? 'text-blue-600 font-black'
                                        : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-lg px-2 -ml-2') .
                                    ' transition-all">';
                                echo '<span class="text-[16px]">' . ($isActive ? '📂' : '📁') . '</span>';
                                echo '<span class="truncate">' . $folder->name . '</span>';
                                echo '</button>';
                                echo '</div>';

                                if ($folder->children->count() > 0) {
                                    echo '<div x-show="expanded" x-collapse style="display: none;" class="ml-4 border-l-2 border-blue-50 pl-3 mt-1 mb-2">';
                                    $renderTree($folder->children, $depth + 1);
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                        };
                        $renderTree($this->getRootFolders());
                    @endphp
                </div>
            </div>
        </nav>

        <div class="p-5 mt-auto">
            <div class="bg-blue-50/50 p-3 rounded-2xl border border-blue-100/50 flex items-center gap-3 relative group">
                <div
                    class="w-10 h-10 rounded-full border-2 border-white shadow-sm flex items-center justify-center overflow-hidden bg-white ring-2 ring-blue-50/50 group-hover:ring-blue-100 transition-all">
                    <span class="text-xl">👤</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[14px] font-bold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-gray-400 font-medium truncate">{{ auth()->user()->phone }}</div>
                </div>
            </div>
            <div class="mt-4 px-4 flex justify-between gap-2">
                <button wire:click="syncFromS3"
                    class="flex-1 bg-white hover:bg-blue-50 border border-gray-200 text-slate-700 px-3 py-2 rounded-xl text-xs font-bold shadow-sm flex items-center justify-center gap-2 transition-all">
                    <span>🔄</span> Sync
                </button>
                <button wire:click="logout"
                    class="flex-1 bg-slate-900 hover:bg-slate-800 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-lg shadow-gray-200 flex items-center justify-center gap-2 transition-all">
                    <span>⇥</span> LogOut
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-y-auto bg-main-gradient p-2 lg:p-4">
        <div
            class="bg-white/50 backdrop-blur-2xl rounded-[2.5rem] shadow-xl shadow-blue-100/50 flex-1 border border-white/80 overflow-hidden flex flex-col relative">
            <!-- Basic Loading Overlay (Constrained to content area) -->
            <!-- Basic Loading Overlay Removed as per user request -->
            <!-- Quick Start Hero Section -->
            @if (!$isFlatView)
                <div
                    class="relative rounded-2xl p-4 md:p-5 text-center overflow-hidden bg-gradient-to-br from-blue-50 to-white border border-blue-50/50">
                    <div
                        class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.05),transparent)] pointer-events-none">
                    </div>
                    <div class="relative z-10">
                        <div class="flex flex-row sm:flex-row items-center justify-center gap-3 w-full sm:w-auto">
                            <button onclick="openModal('createFolderModal')"
                                class="flex-1 sm:flex-none w-1/2 sm:w-auto px-4 sm:px-8 py-3.5 bg-white text-slate-700 font-bold rounded-xl shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2 sm:gap-3 border border-gray-100 group">
                                <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-500 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <span class="truncate">Create Folder</span>
                            </button>
                            <button wire:click="showUploadModal"
                                class="flex-1 sm:flex-none w-1/2 sm:w-auto px-4 sm:px-8 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-100 border-b-4 border-blue-700 hover:border-blue-600 hover:-translate-y-0.5 active:translate-y-0 active:border-b-0 transition-all flex items-center justify-center gap-2 sm:gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                <span class="truncate">Upload Files</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Content Section -->
            <div class="flex-1 flex flex-col min-w-0 overflow-y-auto relative custom-scrollbar" x-data="{ globalDragging: false }"
                @dragover.prevent.stop="globalDragging = true" @dragenter.prevent.stop="globalDragging = true"
                @dragleave.prevent.stop="if($event.relatedTarget === null || !$el.contains($event.relatedTarget)) globalDragging = false"
                @drop.prevent.stop="globalDragging = false; 
                {
                    const droppedFiles = Array.from($event.dataTransfer.files);
                    if(droppedFiles.length > 0) {
                        $wire.showUploadModal();
                        setTimeout(() => $dispatch('files-dropped', { files: droppedFiles, autoStart: true }), 100);
                    }
                }">
                <!-- Global Drag Overlay -->
                <div x-show="globalDragging"
                    class="absolute inset-0 z-[150] bg-blue-600/10 backdrop-blur-sm border-4 border-dashed border-blue-500 rounded-[2.5rem] flex items-center justify-center p-10 pointer-events-none"
                    style="display: none;" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="bg-white p-6 rounded-[3rem] shadow-2xl flex flex-col items-center gap-6 text-center">
                        <div
                            class="w-24 h-24 bg-blue-50 rounded-3xl flex items-center justify-center text-blue-500 animate-bounce">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-3xl font-black text-slate-800">Drop files to upload</h2>
                            <p class="text-slate-500 font-medium mt-2">Release your files anywhere to start uploading
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Header -->
                <div
                    class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-50 bg-white/90 backdrop-blur-md sticky top-0 z-10 transiton-all">
                    <div class="flex flex-col gap-1 overflow-hidden">
                        <nav
                            class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest overflow-hidden">
                            <button wire:click="selectFolder(null)"
                                class="hover:text-blue-500 transition-colors shrink-0">Root</button>
                            @foreach ($this->getBreadcrumbs() as $crumb)
                                <span class="text-gray-300">/</span>
                                <button wire:click="selectFolder('{{ $crumb['id'] }}')"
                                    class="hover:text-blue-500 transition-colors truncate">{{ $crumb['name'] }}</button>
                            @endforeach
                        </nav>
                        <h3 class="text-[17px] font-black text-slate-800 tracking-tight flex items-center gap-2">
                            @if ($isFlatView)
                                <span class="p-1.5 bg-blue-50 rounded-lg text-blue-600 text-[12px]">📄</span> All System
                                Files
                            @elseif($currentFolderId)
                                @php $curr = \App\Models\Folder::find($currentFolderId); @endphp
                                <span class="p-1.5 bg-blue-50 rounded-lg text-blue-600 text-[12px]">📂</span>
                                {{ $curr?->name }}
                            @else
                                <span class="p-1.5 bg-blue-50 rounded-lg text-blue-600 text-[12px]">✦</span> Recent
                                Activities
                            @endif
                        </h3>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto flex-1 justify-end">
                        <div class="relative w-full md:max-w-md group flex-1">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="w-full pl-11 pr-5 py-3 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:ring-8 focus:ring-blue-50/50 focus:border-blue-200 outline-none shadow-inner transition-all text-[13px] text-gray-700 placeholder-gray-400"
                                placeholder="search...">
                        </div>

                        <!-- Mobile Profile Dropdown (visible only on small screens or always if cleaner?) -->
                        <!-- Actually sidebar hides on mobile (lg:flex), so we need this on mobile -->
                        <div class="lg:hidden relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-10 h-10 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200 flex items-center justify-center font-bold text-sm">
                                {{ substr(auth()->user()->name ?? 'User', 0, 1) }}
                            </button>

                            <div x-show="open" @click.away="open = false"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50 origin-top-right"
                                style="display: none;" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100">
                                <div class="px-4 py-3 border-b border-gray-50">
                                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Account</p>
                                    <p class="text-sm font-bold text-slate-800 truncate">
                                        {{ auth()->user()->name ?? 'Guest' }}</p>
                                </div>
                                <button wire:click="logout"
                                    class="w-full text-left px-4 py-3 text-red-500 font-bold text-sm hover:bg-red-50 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                    Sign Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <!-- Grid View -->
                <div x-data="{
                    selectedFiles: [],
                    toggle(id) {
                        if (this.selectedFiles.includes(id)) {
                            this.selectedFiles = this.selectedFiles.filter(i => i != id);
                        } else {
                            this.selectedFiles.push(id);
                        }
                    }
                }"
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-4 gap-y-6 p-2.5">
                    <!-- Folders -->
                    @foreach ($folders as $folder)
                        <div x-data="{ open: false, alignRight: true }" class="group cursor-pointer relative">
                            <div wire:click="selectFolder('{{ $folder->id }}')"
                                class="folder-wrapper relative mb-3 transition-transform duration-300 group-hover:-translate-y-1.5">
                                <div class="relative aspect-[1.3/1]">
                                    <div
                                        class="absolute -top-2 left-1/2 -translate-x-1/2 w-[70%] h-full bg-white rounded-md shadow-sm border border-gray-50 rotate-[-4deg] origin-bottom-right transition-transform group-hover:rotate-[-8deg]">
                                    </div>
                                    <div
                                        class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-[75%] h-full bg-white rounded-md shadow-sm border border-gray-50 rotate-[2deg] origin-bottom-left transition-transform group-hover:rotate-[6deg]">
                                    </div>

                                    <div class="relative w-full h-full">
                                        <svg viewBox="0 0 100 85" class="w-full h-full drop-shadow-lg filter block">
                                            <defs>
                                                <linearGradient id="folderGrad" x1="0%" y1="0%"
                                                    x2="0%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#93C5FD;stop-opacity:1" />
                                                    <stop offset="100%" style="stop-color:#3B82F6;stop-opacity:1" />
                                                </linearGradient>
                                            </defs>
                                            <path
                                                d="M5 15 C5 10 9 7 14 7 L35 7 L45 15 L91 15 C96 15 100 19 100 24 L100 76 C100 81 96 85 91 85 L9 85 C4 85 0 81 0 76 L0 20 C0 17 2 15 5 15 Z"
                                                fill="url(#folderGrad)" />
                                            <path d="M0 32 L100 32 L100 76 C100 81 96 85 91 85 L9 85 C4 85 0 81 0 76 Z"
                                                fill="#60A5FA" fill-opacity="0.9" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Three Dots Button -->
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-all z-20">
                                <button x-ref="btn"
                                    @click.stop="open = !open; if(open) { $nextTick(() => { const r = $refs.btn.getBoundingClientRect(); alignRight = r.left > (window.innerWidth - r.right); }) }"
                                    class="p-1.5 hover:bg-black/5 rounded-lg transition-colors text-slate-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Dropdown Menu (Relative to Card) -->
                            <div x-show="open" @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                :class="alignRight ? 'right-2' : 'left-2'"
                                class="absolute top-10 w-40 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-30">
                                <button
                                    class="w-full text-left px-4 py-2 text-[13px] text-gray-700 hover:bg-gray-50 active:bg-gray-100 active:scale-95 transition-all flex items-center gap-2">
                                    <span>ℹ️</span> Details
                                </button>
                                <button
                                    @click.stop="open = false; $wire.requestDelete('folder', '{{ $folder->id }}')"
                                    class="w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50 active:bg-red-100 active:scale-95 transition-all flex items-center gap-2 font-medium">
                                    <span>🗑️</span> Delete
                                </button>
                            </div>

                            <div wire:click="selectFolder('{{ $folder->id }}')" class="text-center px-1">
                                <div
                                    class="font-bold text-slate-700 text-[12px] truncate leading-tight mb-0.5 group-hover:text-blue-600 transition-colors">
                                    {{ $folder->name }}</div>
                                <div
                                    class="text-[10px] text-gray-400 font-medium opacity-80 uppercase tracking-tighter">
                                    {{ $folder->created_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Files -->
                    @foreach ($files as $file)
                        <div x-data="{ open: false, alignRight: true }"
                            @click="if(window.innerWidth < 1024 && !@js($isModeSelect)) open = !open"
                            class="group cursor-pointer relative">

                            <!-- Selection Checkbox Overlay -->
                            <div @if ($isModeSelect) @click="toggle('{{ $file->id }}')" @endif
                                class="file-wrapper relative mb-3 aspect-[1/1.3]">
                                <div class="w-full h-full bg-white rounded-2xl shadow-sm border flex flex-col items-center justify-center transition-all duration-300 group-hover:-translate-y-1.5 group-hover:shadow-xl relative overflow-hidden"
                                    :class="selectedFiles.includes('{{ $file->id }}') ? 'border-4 border-blue-500' :
                                        'border-gray-100/80 group-hover:border-blue-100'">

                                    @if ($isModeSelect)
                                        <div class="absolute top-2 left-2 z-20">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                                :class="selectedFiles.includes('{{ $file->id }}') ?
                                                    'bg-blue-500 border-blue-500' : 'bg-white/50 border-gray-300'">
                                                <template x-if="selectedFiles.includes('{{ $file->id }}')">
                                                    <svg class="w-4 h-4 text-white" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </template>
                                            </div>
                                        </div>
                                    @endif

                                    @if (str_starts_with($file->mime_type, 'image/') && !empty($file->metadata['public_url']))
                                        <img src="{{ $file->metadata['public_url'] }}" alt="{{ $file->name }}"
                                            loading="lazy" class="w-full h-full object-cover">
                                    @else
                                        <!-- Icon Logic -->
                                        <div
                                            class="p-3 rounded-full bg-blue-50/50 mb-2 group-hover:scale-110 transition-transform">
                                            @if (str_contains($file->mime_type, 'pdf'))
                                                <svg class="w-8 h-8 text-red-500" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9v-2h2v2zm0-4H9V7h2v5z">
                                                    </path>
                                                </svg>
                                            @elseif(str_contains($file->mime_type, 'sheet'))
                                                <svg class="w-8 h-8 text-green-600" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z">
                                                    </path>
                                                </svg>
                                            @else
                                                <svg class="w-8 h-8 text-blue-500" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z">
                                                    </path>
                                                </svg>
                                            @endif
                                        </div>
                                    @endif

                                    <div
                                        class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-gray-50 text-[8px] font-black uppercase text-gray-400 border border-gray-100">
                                        {{ collect(explode('/', $file->mime_type))->last() }}
                                    </div>
                                </div>

                                @if (!$isModeSelect)
                                    <!-- Three Dots Button (Normal Mode) -->
                                    <div
                                        class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-all z-20">
                                        <button x-ref="btn"
                                            @click.stop="open = !open; if(open) { $nextTick(() => { const r = $refs.btn.getBoundingClientRect(); alignRight = r.left > (window.innerWidth - r.right); }) }"
                                            class="p-2 bg-white/80 backdrop-blur shadow-sm rounded-xl text-slate-500 hover:text-blue-600 transition-colors">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Dropdown Menu -->
                                    <div x-show="open" @click.away="open = false"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        :class="alignRight ? 'right-2' : 'left-2'"
                                        class="absolute top-10 w-44 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-30">
                                        <!-- Menu Items ... -->
                                        <!-- Reduced code duplication for brevity: keeping original buttons but wrapping is implied -->
                                        <button
                                            @click.stop="open = false; $wire.showFileDetails('{{ $file->id }}')"
                                            class="w-full text-left px-4 py-2.5 text-[13px] text-gray-700 hover:bg-gray-50 flex items-center gap-2"><span>ℹ️</span>
                                            View Details</button>
                                        <button @click.stop="open = false; $wire.downloadFile('{{ $file->id }}')"
                                            class="w-full text-left px-4 py-2.5 text-[13px] text-gray-700 hover:bg-gray-50 flex items-center gap-2"><span>⬇️</span>
                                            Download</button>
                                        <button
                                            @click.stop="open = false; $wire.requestDelete('file', '{{ $file->id }}')"
                                            class="w-full text-left px-4 py-2.5 text-[13px] text-red-600 hover:bg-red-50 flex items-center gap-2 font-bold mt-1"><span>🗑️</span>
                                            Delete</button>
                                    </div>
                                @endif
                            </div>

                            <div class="text-center px-1">
                                <div
                                    class="font-bold text-slate-700 text-[12px] truncate leading-tight mb-0.5 group-hover:text-blue-600 transition-colors">
                                    {{ $file->name }}</div>
                            </div>
                        </div>
                    @endforeach

                    @if ($isModeSelect)
                        <div class="fixed bottom-6 right-6 z-[120] animate-[scaleIn_0.2s_ease-out]"
                            x-show="selectedFiles.length > 0" style="display: none;" x-transition>
                            <button @click="$wire.confirmSelection(selectedFiles)"
                                class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-bold shadow-2xl hover:bg-blue-700 hover:-translate-y-1 transition-all flex items-center gap-3">
                                <span class="bg-white/20 px-2 py-0.5 rounded text-sm font-black"
                                    x-text="selectedFiles.length"></span>
                                <span>Confirm Selection</span>
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Load More Trigger (Infinite Scroll) -->
                @if ($hasMore)
                    <div x-data x-intersect="$wire.loadMore()" class="py-10 flex justify-center">
                        <div class="w-8 h-8 border-3 border-blue-600/10 border-t-blue-600 rounded-full animate-spin">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Confirm Selection Button (Floating) -->


        <!-- Trial Banner at bottom of main -->
        @if (
            !session('showLicenseModal', false) &&
                auth()->user()->trial_ends_at &&
                now()->lt(auth()->user()->trial_ends_at) &&
                !auth()->user()->license_key)
            <div
                class="bg-orange-500 text-white px-4 py-3 flex items-center justify-center gap-4 shadow-inner text-sm font-bold shrink-0 mt-auto">
                <span>⚡ Trial Version: {{ ceil(now()->diffInDays(auth()->user()->trial_ends_at, false) ?: 1) }} days
                    remaining.</span>
                <button wire:click="$set('showLicenseModal', true)"
                    class="bg-white text-orange-600 px-3 py-1 rounded-lg hover:bg-orange-50 transition-colors uppercase text-xs tracking-wider">Activate
                    Now</button>
            </div>
        @endif
    </main>

    <!-- File Details / Preview Modal -->
    <div id="fileDetailsModal"
        class="fixed inset-0 bg-slate-900/80 backdrop-blur-xl hidden flex items-center justify-center z-[110] p-4 transition-all opacity-0 pointer-events-none"
        style="display: none;">
        @if ($selectedFileForDetails)
            <div
                class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl overflow-hidden translate-y-10 transition-transform duration-300 flex flex-col md:flex-row max-h-[90vh]">
                <!-- Preview Section -->
                <div
                    class="flex-1 bg-slate-100 flex items-center justify-center overflow-hidden min-h-[300px] relative">
                    @if (str_starts_with($selectedFileForDetails->mime_type, 'image/'))
                        <img src="{{ $selectedFileForDetails->metadata['public_url'] }}"
                            alt="{{ $selectedFileForDetails->name }}" class="w-full h-full object-contain">
                    @elseif(str_starts_with($selectedFileForDetails->mime_type, 'video/'))
                        <video controls class="w-full h-full">
                            <source src="{{ $selectedFileForDetails->metadata['public_url'] }}"
                                type="{{ $selectedFileForDetails->mime_type }}">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <div class="flex flex-col items-center gap-4 text-slate-400">
                            <div class="p-6 bg-white rounded-3xl shadow-sm">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <p class="font-bold text-sm uppercase tracking-widest">Preview not available</p>
                        </div>
                    @endif

                    <!-- Quick Actions Over Preview -->
                    <div class="absolute top-4 left-4 flex gap-2">
                        <button onclick="closeModal('fileDetailsModal')"
                            class="p-3 bg-white/90 backdrop-blur hover:bg-white rounded-2xl shadow-sm text-slate-800 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="w-full md:w-80 flex flex-col bg-white border-l border-gray-100">
                    <div class="p-5 pb-3 flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-black text-gray-800 tracking-tight break-all">
                                {{ $selectedFileForDetails->name }}</h3>
                            <p class="text-gray-400 text-[12px] mt-0.5">Metadata & Properties</p>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-3 space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-1.5 px-1">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">File Size</p>
                                <p
                                    class="text-[14px] text-gray-700 font-bold bg-gray-50 p-2 rounded-xl inline-block border border-gray-100">
                                    {{ number_format($selectedFileForDetails->size / 1024 / 1024, 2) }} MB</p>
                            </div>
                            <div class="space-y-1.5 px-1">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mime Type</p>
                                <p class="text-[13px] text-gray-700 font-semibold">
                                    {{ $selectedFileForDetails->mime_type }}</p>
                            </div>
                            <div class="space-y-1.5 px-1">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Uploaded On
                                </p>
                                <p class="text-[13px] text-gray-700 font-semibold">
                                    {{ $selectedFileForDetails->created_at->format('M d, Y') }}</p>
                                <p class="text-[11px] text-gray-400 font-medium">
                                    {{ $selectedFileForDetails->created_at->format('H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 border-t border-gray-50 flex flex-col gap-3">
                        <button wire:click="downloadFile('{{ $selectedFileForDetails->id }}')"
                            class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download
                        </button>
                        <button onclick="closeModal('fileDetailsModal')"
                            class="w-full py-3 text-gray-500 font-bold rounded-2xl hover:bg-gray-50 transition-all">Close
                            Editor</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Create Folder Modal -->
    <div id="createFolderModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-md hidden flex items-center justify-center z-[100] p-4 transition-all opacity-0 pointer-events-none"
        style="display: none;">
        <div
            class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden translate-y-10 transition-transform duration-300">
            <div class="p-8 pb-4 flex justify-between items-center bg-blue-50/20">
                <div>
                    <h3 class="text-2xl font-black text-gray-800 tracking-tight">Create Folder</h3>
                    <p class="text-gray-400 text-[14px] mt-1">Create a new folder to arrange & analyze your files</p>
                </div>
                <button onclick="closeModal('createFolderModal')"
                    class="p-3 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-2xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-5 pt-4 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Folder Name</label>
                    <input type="text" wire:model="newFolderName" id="newFolderName"
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-blue-500 outline-none font-bold text-slate-700 placeholder-slate-400 transition-all"
                        placeholder="e.g. Work Documents" autofocus>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Parent Folder</label>
                    <div class="relative" x-data="{ open: false, search: '' }">
                        <!-- Custom Select Trigger -->
                        <button type="button" @click="open = !open"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-blue-500 outline-none font-bold text-slate-700 flex items-center justify-between transition-all hover:bg-white">
                            <span class="truncate">
                                @if ($this->targetParentId)
                                    @php $p = $this->allFolders->firstWhere('id', $this->targetParentId); @endphp
                                    {{ $p ? '📂 ' . $p->name : 'Root ( / )' }}
                                @else
                                    Root ( / )
                                @endif
                            </span>
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" style="display: none;"
                            class="absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100">

                            <!-- Search -->
                            <div class="p-3 border-b border-gray-50 sticky top-0 bg-white z-10">
                                <input x-model="search" x-ref="searchInput"
                                    @keydown.window.prevent.slash="$refs.searchInput.focus()" type="text"
                                    placeholder="Search folder..."
                                    class="w-full px-4 py-2 bg-slate-50 rounded-xl text-sm font-bold text-gray-700 outline-none focus:ring-2 focus:ring-blue-100">
                            </div>

                            <!-- List -->
                            <div class="max-h-60 overflow-y-auto custom-scrollbar p-1">
                                <div @click="$wire.set('targetParentId', null); open = false"
                                    class="px-4 py-3 hover:bg-blue-50 rounded-xl cursor-pointer text-sm font-bold text-gray-700 flex items-center gap-2 transition-colors">
                                    <span>Root ( / )</span>
                                </div>
                                @foreach ($this->allFolders as $f)
                                    <div x-show="search === '' || '{{ strtolower($f->name) }}'.includes(search.toLowerCase())"
                                        @click="$wire.set('targetParentId', '{{ $f->id }}'); open = false"
                                        class="px-4 py-3 hover:bg-blue-50 rounded-xl cursor-pointer text-sm font-bold text-gray-700 flex items-center gap-2 transition-colors truncate">
                                        <span class="text-gray-300 font-mono text-xs">{!! $f->depth_name !!}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2.5 px-1">
                        <label class="block text-[14px] font-bold text-gray-700">Upload files</label>
                        <button class="text-blue-600 text-[12px] font-bold hover:underline">+add more</button>
                    </div>
                    <div x-data="{ dragging: false }" @dragover.prevent.stop="dragging = true"
                        @dragenter.prevent.stop="dragging = true" @dragleave.prevent.stop="dragging = false"
                        @drop.prevent.stop="dragging = false; $dispatch('files-dropped', { files: $event.dataTransfer.files, context: 'create-folder' }); openModal('uploadModal')"
                        :class="dragging ? 'border-blue-400 bg-blue-50 shadow-lg' : 'border-slate-100 bg-slate-50'"
                        class="border-2 border-dashed rounded-[2rem] p-6 text-center transition-all cursor-pointer group relative">

                        <div
                            class="mx-auto w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <p class="text-slate-800 font-bold mb-1">Drag and Drop your files here</p>
                        <p class="text-gray-400 text-sm italic">or <span
                                class="text-blue-600 underline font-black not-italic">click here</span> to select from
                            computer</p>
                        <input type="file" multiple class="absolute inset-0 opacity-0 cursor-pointer"
                            @change="$dispatch('files-dropped', { files: $event.target.files, context: 'create-folder' }); openModal('uploadModal')">
                    </div>
                </div>

                <div class="p-4 bg-orange-50 rounded-2xl border border-orange-100 flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg text-orange-400 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-[12px] text-orange-800 font-bold leading-tight">No files have been selected, empty
                        folder will be created. You can upload later.</p>
                </div>
            </div>
            <div class="p-5 pt-0 flex gap-4">
                <button onclick="closeModal('createFolderModal')"
                    class="flex-1 py-4 bg-white border border-slate-100 text-slate-500 font-bold rounded-2xl hover:bg-slate-50 transition-all">Discard</button>
                <button wire:click="createFolder" wire:loading.attr="disabled"
                    class="flex-1 py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="createFolder">Create Folder</span>
                    <span wire:loading wire:target="createFolder">Creating...</span>
            </div>
        </div>
    </div>

    <!-- Context Menu Component -->
    <div x-data="{ open: false, top: 0, left: 0, folderId: null }"
        @contextmenu-folder.window="open = true; top = $event.detail.y; left = $event.detail.x; folderId = $event.detail.id"
        @click.away="open = false" x-show="open" :style="`top: ${top}px; left: ${left}px`"
        class="fixed z-[9999] bg-white rounded-xl shadow-2xl border border-gray-100 w-56 overflow-hidden transform transition-all duration-200 origin-top-left"
        style="display: none;" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">

        <button
            @click="$dispatch('open-modal', 'createFolderModal'); $wire.openCreateFolderModalWithParent(folderId); setTimeout(() => document.getElementById('newFolderName').focus(), 100); open = false"
            class="w-full text-left px-5 py-3 hover:bg-blue-50 text-slate-700 font-bold text-sm flex items-center gap-3 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
            </svg>
            Create Subfolder
        </button>
    </div>

    <!-- Unified Upload Modal (Rich UX) -->
    <div id="uploadModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-md hidden flex items-center justify-center z-[100] p-4 transition-all opacity-0 pointer-events-none"
        style="display: none;">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden translate-y-10 transition-transform duration-300"
            x-data="{
                uploading: false,
                files: [],
                grouping: false,
                currentUploadIndex: -1,
                addFiles(fileList) {
                    const newFiles = Array.from(fileList).map(file => ({
                        file: file,
                        name: file.name,
                        size: file.size,
                        progress: 0,
                        status: 'pending'
                    }));
                    this.files = [...this.files, ...newFiles];
                    this.grouping = this.files.length > 1;
                },
                async startUpload() {
                    if (this.files.length === 0 || this.uploading) return;
                    if (this.grouping && !$wire.newGroupName) {
                        $dispatch('toast', { message: 'Please enter a folder name for grouping', type: 'error' });
                        return;
                    }
            
                    this.uploading = true;
                    let targetId = null;
                    if (this.grouping && $wire.newGroupName) {
                        targetId = await $wire.createFolderAndGetId($wire.newGroupName);
                    }
            
                    for (let i = 0; i < this.files.length; i++) {
                        if (this.files[i].status === 'done') continue;
                        this.currentUploadIndex = i;
                        this.files[i].status = 'uploading';
            
                        try {
                            await new Promise((resolve, reject) => {
                                @this.upload('uploadFiles', this.files[i].file,
                                    (uploadedFilename) => {
                                        this.files[i].progress = 100;
                                        this.files[i].status = 'done';
                                        @this.call('saveUploadedFile', uploadedFilename, this.files[i].name, this.files[i].file.type, this.files[i].file.size, targetId)
                                            .then(resolve).catch(reject);
                                    },
                                    () => reject('error'),
                                    (event) => {
                                        this.files[i].progress = event.detail.progress;
                                    }
                                );
                            });
                        } catch (e) {
                            this.files[i].status = 'error';
                        }
                    }
                    this.uploading = false;
                    $dispatch('toast', { message: 'All files processed successfully!', type: 'success' });
                    @this.call('loadItems'); // Refresh UI once after batch
                    closeModal('uploadModal');
                    this.files = [];
                }
            }"
            @files-dropped.window="addFiles($event.detail.files); if($event.detail.context === 'create-folder') grouping = true; if($event.detail.autoStart) startUpload();">

            <div class="p-8 pb-4 flex justify-between items-center bg-blue-50/20">
                <div>
                    <h3 class="text-2xl font-black text-gray-800 tracking-tight">File upload</h3>
                    <p class="text-gray-400 text-[14px] mt-1">Analyze individual files or group them together in a
                        folder</p>
                </div>
                <button onclick="closeModal('uploadModal')"
                    class="p-3 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-2xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-5">
                <!-- Drop Zone / Selected Files -->
                <div x-show="files.length === 0" x-data="{ dragging: false }" @dragover.prevent.stop="dragging = true"
                    @dragenter.prevent.stop="dragging = true" @dragleave.prevent.stop="dragging = false"
                    @drop.prevent.stop="dragging = false; const df = Array.from($event.dataTransfer.files); if(df.length > 0) { addFiles(df); startUpload(); }"
                    :class="dragging ? 'border-blue-400 bg-blue-50 shadow-lg scale-[1.02]' : 'border-slate-100 bg-slate-50'"
                    class="border-2 border-dashed rounded-[2rem] p-8 text-center transition-all cursor-pointer group relative mb-4">
                    <div
                        class="mx-auto w-20 h-20 bg-white rounded-3xl shadow-sm flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                    </div>
                    <p class="text-slate-800 text-xl font-black mb-2">Drag and Drop files</p>
                    <p class="text-gray-400 font-medium">or <span class="text-blue-600 underline font-black">browse
                            computer</span></p>
                    <input type="file" multiple class="absolute inset-0 opacity-0 cursor-pointer"
                        @change="addFiles($event.target.files); startUpload();">
                </div>

                <div x-show="files.length > 0" class="space-y-4">
                    <div class="flex items-center justify-between px-1">
                        <h4 class="text-[14px] font-black text-slate-800 uppercase tracking-widest">Selected Files
                            (<span x-text="files.length"></span>)</h4>
                        <button @click="files = []" class="text-red-500 text-[12px] font-bold hover:underline"
                            x-show="!uploading">Clear All</button>
                    </div>

                    <div class="max-h-[250px] overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                        <template x-for="(f, index) in files" :key="index">
                            <div
                                class="bg-slate-50 border border-slate-100 p-4 rounded-2xl flex items-center gap-4 transition-all">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                                    <span class="text-xl">📄</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="text-[13px] font-bold text-slate-800 truncate pr-4"
                                            x-text="f.name">
                                        </div>
                                        <div class="text-[10px] font-black text-gray-400"
                                            x-text="(f.size / 1024 / 1024).toFixed(2) + ' MB'"></div>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 transition-all duration-300"
                                            :style="'width: ' + f.progress + '%'"></div>
                                    </div>
                                    <div class="mt-1 flex items-center justify-between">
                                        <span class="text-[10px] font-bold"
                                            :class="f.status === 'done' ? 'text-green-500' : (f.status === 'error' ?
                                                'text-red-500' : 'text-blue-500')"
                                            x-text="f.status === 'pending' ? 'Waiting...' : (f.status === 'uploading' ? 'Uploading ' + f.progress + '%' : (f.status === 'error' ? 'Error' : 'Completed'))"></span>
                                        <button @click="files.splice(index, 1)" x-show="!uploading"
                                            class="text-gray-300 hover:text-red-500"><svg class="w-4 h-4"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg></button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Grouping Options -->
                <div x-show="files.length > 0 && !uploading" class="pt-2">
                    <div class="space-y-3">
                        <label
                            class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 cursor-pointer hover:bg-white hover:shadow-md transition-all">
                            <input type="radio" name="upload_mode" value="separate" x-model="grouping"
                                :value="false"
                                class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="flex-1">
                                <p class="text-[14px] font-bold text-slate-800">Upload separate files</p>
                                <p class="text-[11px] text-gray-400">Files will be added directly to the current
                                    directory</p>
                            </div>
                            <span class="text-2xl">📄</span>
                        </label>

                        <div :class="grouping ? 'bg-blue-50 border-blue-200 shadow-md ring-4 ring-blue-50' :
                            'bg-slate-50 border-slate-100'"
                            class="p-5 rounded-3xl border transition-all space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="upload_mode" value="folder" x-model="grouping"
                                    :value="true"
                                    class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <div class="flex-1">
                                    <p class="text-[14px] font-bold text-slate-800">Group to folder and upload</p>
                                    <p class="text-[11px] text-gray-400">Organize selected files into a new container
                                    </p>
                                </div>
                                <span class="text-2xl">📂</span>
                            </label>

                            <div x-show="grouping" x-collapse>
                                <div class="flex items-center gap-3 mt-4">
                                    <input type="text" placeholder="Folder Name (e.g. Project Assets)"
                                        x-model="$wire.newGroupName"
                                        class="flex-1 px-5 py-3.5 rounded-xl border-2 border-slate-200 bg-white focus:border-blue-400 focus:ring-0 outline-none transition-all text-[14px] font-bold shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-5 pt-0 flex gap-4">
                <button onclick="closeModal('uploadModal')" x-show="!uploading"
                    class="flex-1 py-5 bg-white border border-slate-100 text-slate-500 font-bold rounded-2xl hover:bg-slate-50 transition-all">Discard</button>
                <button @click="startUpload()"
                    :disabled="files.length === 0 || uploading || (grouping && !$wire.newGroupName)"
                    :class="files.length === 0 || uploading || (grouping && !$wire.newGroupName) ?
                        'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700 shadow-xl shadow-blue-100'"
                    class="flex-1 py-5 bg-blue-600 text-white font-black rounded-2xl transition-all flex items-center justify-center gap-3 text-lg">
                    <template x-if="!uploading">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <span>Upload <span x-text="files.length"></span> Files</span>
                        </div>
                    </template>
                    <template x-if="uploading">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin">
                            </div>
                            <span>Processing... (<span x-text="currentUploadIndex + 1"></span>/<span
                                    x-text="files.length"></span>)</span>
                        </div>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <!-- Global Toast Component -->
    <div x-data="{
        show: false,
        message: '',
        type: 'success',
        timer: null
    }"
        @toast.window="
            message = $event.detail.message; 
            type = $event.detail.type || 'success';
            show = true;
            clearTimeout(timer);
            timer = setTimeout(() => show = false, 3000);
        "
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-10 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-10 opacity-0" class="fixed bottom-10 right-10 z-[200] pointer-events-none"
        style="display: none;">
        <div
            class="bg-slate-900/90 backdrop-blur-md text-white px-6 py-4 rounded-3xl shadow-2xl flex items-center gap-4 min-w-[280px] border border-white/10">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-blue-500/20 text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7">
                    </path>
                </svg>
            </div>
            <div>
                <p class="text-[13px] font-bold tracking-tight" x-text="message"></p>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mt-0.5">Notification</p>
            </div>
        </div>
    </div>

    <!-- Sync Progress Popup -->
    <div wire:loading.flex wire:target="syncFromS3"
        class="fixed bottom-6 right-6 z-[120] bg-white rounded-2xl shadow-2xl border border-blue-50 p-4 flex items-center gap-4 animate-in slide-in-from-bottom-5 fade-in duration-300">
        <div class="w-10 h-10 border-4 border-blue-100 border-t-blue-500 rounded-full animate-spin shrink-0"></div>
        <div>
            <h4 class="text-sm font-bold text-gray-800">Syncing from Cloud...</h4>
            <p class="text-xs text-gray-400" x-text="$wire.syncMessage || 'Scanning files...'"></p>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmationModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-md hidden flex items-center justify-center z-[110] p-4 transition-all opacity-0 pointer-events-none"
        style="display: none;">
        <div
            class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 transition-all duration-300">
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">Delete Item?</h3>
                <p class="text-gray-500 text-sm font-medium">Are you sure you want to delete this item? This action
                    cannot be undone.</p>
            </div>
            <div class="p-6 pt-0 flex gap-3">
                <button onclick="closeModal('deleteConfirmationModal')"
                    class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition-all">Cancel</button>
                <button wire:click="performDelete"
                    class="flex-1 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 transition-all">Delete</button>
            </div>
        </div>
    </div>

    <!-- License Activation Modal -->
    <div x-show="$wire.showLicenseModal"
        class="fixed inset-0 bg-slate-900/90 backdrop-blur-xl z-[99999] flex items-center justify-center p-4"
        style="display: none;" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden text-center relative">

            @if ($this->trialDaysLeft > 0)
                <button wire:click="$set('showLicenseModal', false)"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            @endif

            <div class="p-8 pt-10">
                <div
                    class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 text-blue-600">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>

                <h3 class="text-2xl font-black text-slate-800 mb-2">OpenFiles Verification</h3>
                <p class="text-gray-500 text-sm mb-8 px-4 leading-relaxed">
                    Phiên bản này thuộc quyền sở hữu của <span class="font-bold text-slate-700">OpenFiles</span>.<br>
                    Vui lòng kích hoạt bản quyền để tiếp tục sử dụng.
                </p>

                <div class="space-y-4">
                    <div class="relative">
                        <input type="text" wire:model="licenseKeyInput"
                            x-on:input="
                                    let val = $el.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                                    if(val.length > 12) val = val.substring(0, 12);
                                    let parts = val.match(/.{1,3}/g) || [];
                                    $el.value = parts.join(' - ');
                                    $wire.set('licenseKeyInput', $el.value);
                                "
                            placeholder="ABC - 123 - 456 - 789"
                            class="w-full text-center text-xl font-mono font-bold tracking-wider px-5 py-4 bg-slate-50 border-2 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none transition-all placeholder-slate-300 {{ $licenseError ? 'border-red-300 text-red-600 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 text-slate-700 focus:border-blue-500' }}">
                    </div>

                    @if ($licenseError)
                        <p class="text-red-500 text-sm font-bold bg-red-50 py-2 rounded-lg animate-pulse">
                            {{ $licenseError }}</p>
                    @endif

                    <button wire:click="activateLicense" wire:loading.attr="disabled"
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-2xl hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="activateLicense">Activate License</span>
                        <span wire:loading wire:target="activateLicense">Verifying...</span>
                    </button>

                    <div class="pt-4 border-t border-gray-50 mt-6">
                        <p class="text-xs text-gray-400 font-medium">Trial period ended. Please contact support.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('open-modal', event => {
            openModal(event.detail);
        });

        function openModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = 'flex';
            setTimeout(() => {
                el.classList.remove('opacity-0', 'pointer-events-none');
                el.children[0].classList.remove('translate-y-10');
            }, 10);
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('opacity-0', 'pointer-events-none');
            el.children[0].classList.add('translate-y-10');
            setTimeout(() => {
                el.style.display = 'none';
            }, 300);
        }

        function submitCreateFolder() {
            const name = document.getElementById('newFolderName').value;
            if (name) {
                @this.call('createFolder', name);
                closeModal('createFolderModal');
                document.getElementById('newFolderName').value = '';
            }
        }
    </script>
</div>
