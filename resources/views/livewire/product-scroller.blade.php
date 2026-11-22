<section class="py-5 product-scroller-section" style="background:#f8fafc;position:relative;overflow:hidden">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <span class="badge bg-danger-subtle text-danger text-uppercase fw-bold px-2 py-1 mb-1" style="font-size:0.75rem;letter-spacing:0.05em">Trending Now</span>
                <h2 class="section-title mb-0" style="font-size:1.5rem">Featured & Hot Products</h2>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center scroller-btn-prev" aria-label="Previous Products" style="width:36px;height:36px">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center scroller-btn-next" aria-label="Next Products" style="width:36px;height:36px">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <a href="{{ route('shop') }}" class="btn btn-sm btn-outline-primary ms-2 d-none d-sm-inline-block">View All →</a>
            </div>
        </div>

        <div class="product-scroller-wrapper position-relative" style="cursor:grab">
            <div class="product-scroller-track d-flex gap-3 overflow-auto py-2 px-1"
                 tabindex="0"
                 aria-label="Scrollable Product Showcase"
                 style="scroll-behavior:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;-ms-overflow-style:none;will-change:scroll-position">
                @forelse($products as $product)
                    <div class="product-scroller-item flex-shrink-0" style="width:230px">
                        <div class="product-card h-100 bg-white border rounded-3 p-3 shadow-sm d-flex flex-column justify-content-between position-relative">
                            <div>
                                <a href="{{ route('product.show', $product->slug) }}" class="product-img-wrap d-block position-relative overflow-hidden rounded-2 mb-2" style="aspect-ratio:1/1;background:#f1f5f9" draggable="false">
                                    @if($product->is_on_sale)
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2" style="font-size:0.7rem;z-index:2">-{{ $product->discount_percent }}%</span>
                                    @endif
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" draggable="false" style="width:100%;height:100%;object-fit:cover;pointer-events:none"
                                         onerror="this.onerror=null;this.src='https://placehold.co/600x600/f1f5f9/94a3b8?text={{ rawurlencode($product->name) }}'">
                                </a>
                                <div class="text-muted small text-truncate mb-1" style="font-size:0.75rem">{{ $product->category?->name ?? 'General' }}</div>
                                <a href="{{ route('product.show', $product->slug) }}" class="fw-600 text-dark text-decoration-none text-truncate-2 d-block mb-2" style="font-size:0.875rem;line-height:1.3;height:2.6em;overflow:hidden" title="{{ $product->name }}">
                                    {{ $product->name }}
                                </a>
                            </div>
                            <div>
                                <div class="mb-2">
                                    @if($product->is_on_sale)
                                        <span class="fw-bold text-danger" style="font-size:0.95rem">Rs. {{ number_format($product->sale_price, 0) }}</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size:0.75rem">Rs. {{ number_format($product->base_price, 0) }}</span>
                                    @else
                                        <span class="fw-bold text-dark" style="font-size:0.95rem">Rs. {{ number_format($product->base_price, 0) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('product.show', $product->slug) }}" class="btn btn-sm btn-outline-primary w-100 fw-500 py-1" style="font-size:0.8rem">
                                    {{ $product->is_variable ? 'Select Options' : 'View Product' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted py-4">No products available at the moment.</div>
                @endforelse

                @if($hasMore)
                    <div class="product-scroller-item flex-shrink-0 d-flex align-items-center justify-content-center load-more-item" style="width:160px">
                        <button type="button" wire:click="loadMore" class="btn btn-light border p-3 rounded-3 text-center shadow-sm w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-2">
                            <span wire:loading.remove wire:target="loadMore">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <div class="small fw-bold">Load More</div>
                            </span>
                            <span wire:loading wire:target="loadMore" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .product-scroller-track::-webkit-scrollbar { display: none; }
        .product-scroller-track { user-select: none; }
        .product-scroller-wrapper.is-dragging { cursor: grabbing !important; }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        (function() {
            function initWrapAroundScroller() {
                const wrapper = document.querySelector('.product-scroller-wrapper');
                const track = document.querySelector('.product-scroller-track');
                const prevBtn = document.querySelector('.scroller-btn-prev');
                const nextBtn = document.querySelector('.scroller-btn-next');
                if (!track || track.dataset.scrollerInitialized === 'true') return;

                const originalItems = Array.from(track.querySelectorAll('.product-scroller-item:not(.load-more-item)'));
                if (originalItems.length === 0) return;

                track.dataset.scrollerInitialized = 'true';

                // Clone items for a seamless wrap-around loop (right to left)
                originalItems.forEach(item => {
                    const clone = item.cloneNode(true);
                    clone.classList.add('scroller-clone');
                    clone.setAttribute('aria-hidden', 'true');
                    track.appendChild(clone);
                });

                let isPaused = false;
                let isDragging = false;
                let startX = 0;
                let scrollLeftStart = 0;
                let dragMoved = 0;
                let animFrameId = null;
                const scrollSpeed = 0.65; // ~0.5x smooth glide speed (pixels per frame at 60fps)

                function getLoopThreshold() {
                    const totalItems = track.querySelectorAll('.product-scroller-item:not(.load-more-item)').length;
                    const singleSetCount = originalItems.length;
                    if (totalItems <= singleSetCount) return track.scrollWidth / 2;
                    return (track.scrollWidth / totalItems) * singleSetCount;
                }

                function step() {
                    if (!isPaused && !isDragging) {
                        track.scrollLeft += scrollSpeed;
                        const threshold = getLoopThreshold();
                        if (track.scrollLeft >= threshold) {
                            track.scrollLeft -= threshold;
                        }
                    }
                    animFrameId = requestAnimationFrame(step);
                }

                // Start smooth infinite loop
                animFrameId = requestAnimationFrame(step);

                // Stop/Pause when mouse enters the entire scroller area
                wrapper.addEventListener('mouseenter', () => { isPaused = true; });
                wrapper.addEventListener('mouseleave', () => {
                    if (!isDragging) isPaused = false;
                });
                wrapper.addEventListener('focusin', () => { isPaused = true; });
                wrapper.addEventListener('focusout', () => { isPaused = false; });

                // Drag with mouse
                wrapper.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    isPaused = true;
                    wrapper.classList.add('is-dragging');
                    startX = e.pageX - wrapper.offsetLeft;
                    scrollLeftStart = track.scrollLeft;
                    dragMoved = 0;
                });

                window.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    e.preventDefault();
                    const x = e.pageX - wrapper.offsetLeft;
                    const walk = (x - startX);
                    dragMoved += Math.abs(walk);
                    track.scrollLeft = scrollLeftStart - walk;

                    // Seamless wrap-around during drag
                    const threshold = getLoopThreshold();
                    if (track.scrollLeft >= threshold) {
                        track.scrollLeft -= threshold;
                        scrollLeftStart -= threshold;
                    } else if (track.scrollLeft <= 0) {
                        track.scrollLeft += threshold;
                        scrollLeftStart += threshold;
                    }
                });

                window.addEventListener('mouseup', () => {
                    if (isDragging) {
                        isDragging = false;
                        wrapper.classList.remove('is-dragging');
                        isPaused = wrapper.matches(':hover');
                    }
                });

                // Prevent accidental click when dragging
                track.addEventListener('click', (e) => {
                    if (dragMoved > 8) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }, true);

                // Touch support
                track.addEventListener('touchstart', () => { isPaused = true; }, { passive: true });
                track.addEventListener('touchend', () => {
                    setTimeout(() => { isPaused = false; }, 2000);
                }, { passive: true });

                // Buttons controls
                const cardStep = 246; // Card width + gap
                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        isPaused = true;
                        const threshold = getLoopThreshold();
                        if (track.scrollLeft <= cardStep) {
                            track.scrollLeft += threshold;
                        }
                        track.scrollBy({ left: -cardStep, behavior: 'smooth' });
                        setTimeout(() => { isPaused = wrapper.matches(':hover'); }, 3000);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        isPaused = true;
                        const threshold = getLoopThreshold();
                        if (track.scrollLeft + cardStep >= threshold) {
                            track.scrollLeft -= threshold;
                        }
                        track.scrollBy({ left: cardStep, behavior: 'smooth' });
                        setTimeout(() => { isPaused = wrapper.matches(':hover'); }, 3000);
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWrapAroundScroller);
            } else {
                initWrapAroundScroller();
            }

            document.addEventListener('livewire:navigated', () => {
                const track = document.querySelector('.product-scroller-track');
                if (track) delete track.dataset.scrollerInitialized;
                initWrapAroundScroller();
            });
        })();
    </script>
</section>
