@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            <div class="flex flex-1 items-center justify-center md:justify-between gap-4">
                {{-- Left side info (Hidden on small mobile) --}}
                <div class="hidden lg:block">
                    <p class="text-sm text-gray-700 leading-5">
                        <span>{!! __('Showing') !!}</span>
                        <span class="font-bold text-blue-600">{{ $paginator->firstItem() }}</span>
                        <span>{!! __('to') !!}</span>
                        <span class="font-bold text-blue-600">{{ $paginator->lastItem() }}</span>
                        <span>{!! __('of') !!}</span>
                        <span class="font-bold text-blue-600">{{ $paginator->total() }}</span>
                    </p>
                </div>

                {{-- Pagination Links --}}
                <div>
                    <span class="relative z-0 inline-flex rounded-xl shadow-sm overflow-hidden border border-gray-200 bg-white">
                        <span>
                            {{-- Previous Page Link --}}
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-gray-50 cursor-default leading-5 border-r border-gray-200 h-8 w-8 md:h-10 md:w-10 justify-center" aria-hidden="true">
                                        <i class="fa-solid fa-chevron-left opacity-30"></i>
                                    </span>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white leading-5 hover:bg-blue-50 hover:text-blue-600 focus:z-10 focus:outline-none transition-all h-8 w-8 md:h-10 md:w-10 justify-center border-r border-gray-200" aria-label="{{ __('pagination.previous') }}">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                            @endif
                        </span>

                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-400 bg-gray-50 cursor-default leading-5 border-r border-gray-200 h-8 md:h-10">{{ $element }}</span>
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-black text-white bg-blue-600 cursor-default leading-5 border-r border-gray-200 h-8 md:h-10">{{ $page }}</span>
                                            </span>
                                        @else
                                            {{-- Hide middle pages on small mobile if not adjacent to current? 
                                                 Actually, with onEachSide(0), we only have first, current, and last. --}}
                                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white leading-5 hover:bg-blue-50 hover:text-blue-600 focus:z-10 focus:outline-none transition-all h-8 md:h-10 border-r border-gray-200" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                                {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        @endforeach

                        <span>
                            {{-- Next Page Link --}}
                            @if ($paginator->hasMorePages())
                                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white leading-5 hover:bg-blue-50 hover:text-blue-600 focus:z-10 focus:outline-none transition-all h-8 w-8 md:h-10 md:w-10 justify-center" aria-label="{{ __('pagination.next') }}">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-gray-50 cursor-default leading-5 h-8 w-8 md:h-10 md:w-10 justify-center" aria-hidden="true">
                                        <i class="fa-solid fa-chevron-right opacity-30"></i>
                                    </span>
                                </span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
