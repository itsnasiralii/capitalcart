<div class="storefront-banner-slider" role="region" aria-label="Store highlights"
    x-data="{
        index: 0, count: {{ $slides->count() }}, paused: false, hovered: false, focused: false, timer: null,
        init() {
            this.paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (this.count > 1) this.timer = setInterval(() => {
                if (!this.paused && !this.hovered && !this.focused && !document.hidden && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.next();
            }, {{ $autoplayDuration * 1000 }});
        },
        next() { this.index = (this.index + 1) % this.count; },
        previous() { this.index = (this.index - 1 + this.count) % this.count; },
        destroy() { clearInterval(this.timer); }
    }"
    @mouseenter="hovered = true" @mouseleave="hovered = false"
    @focusin="focused = true" @focusout="focused = $el.contains($event.relatedTarget)">
    @forelse($slides as $index => $slide)
        <a class="storefront-banner-slide" wire:key="hero-slide-{{ $slide->id }}"
            href="{{ is_store_link($slide->link_url) ? $slide->link_url : route('shop') }}"
            aria-label="{{ $slide->title ?: 'Browse products' }}"
            x-show="index === {{ $index }}" @if($index !== 0) style="display:none" @endif>
            <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" width="400" height="126" decoding="async"
                onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'">
        </a>
    @empty
        <a class="storefront-banner-slide" href="{{ route('shop') }}">
            <img src="{{ asset('images/hero-fallback.svg') }}" alt="Explore Capital Cart products" width="400" height="126">
        </a>
    @endforelse
    @if($slides->count() > 1)
        <button type="button" class="storefront-banner-arrow storefront-banner-prev" aria-label="Previous slide" @click="previous()">&#8249;</button>
        <button type="button" class="storefront-banner-arrow storefront-banner-next" aria-label="Next slide" @click="next()">&#8250;</button>
        <div class="storefront-banner-dots" aria-label="Choose a slide">
            @foreach($slides as $index => $slide)
                <button type="button" aria-label="Slide {{ $index + 1 }}" :aria-pressed="index === {{ $index }}" @click="index = {{ $index }}"></button>
            @endforeach
        </div>
        <button type="button" class="storefront-banner-pause" :aria-label="paused ? 'Play slideshow' : 'Pause slideshow'" @click="paused = !paused" x-text="paused ? 'Play' : 'Pause'">Pause</button>
    @endif
</div>
