@props(['title', 'items', 'activeTab'])

<div class="cms-nav-group">
    <div class="cms-nav-title">{{ $title }}</div>
    @foreach ($items as $tab => $meta)
        <a href="{{ route('website.admin', ['tab' => $tab]) }}"
            class="cms-nav-link {{ $activeTab === $tab ? 'is-active' : '' }}"
            title="{{ $meta[0] }}">
            <i class="fa-solid {{ $meta[1] }}"></i>
            <span class="cms-nav-text cms-truncate">{{ $meta[0] }}</span>
            @if (!empty($meta[2]))
                <span class="cms-badge {{ $tab === 'leads' || $tab === 'listings' ? 'warning' : 'muted' }} mono">{{ $meta[2] }}</span>
            @endif
        </a>
    @endforeach
</div>
