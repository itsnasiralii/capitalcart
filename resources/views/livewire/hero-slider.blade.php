<div class="hero-circular-slider-container" id="capitalHeroSlider" data-autoplay="{{ $autoplayDuration * 1000 }}" style="position:relative;display:inline-block;max-width:100%">
    {{-- Concentric Glow Rings (Preserving existing visual layout) --}}
    <div class="glow-ring-outer" style="width:min(420px,calc(100vw - 48px));aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:center;margin:0 auto;position:relative">
        <div class="glow-ring-inner" style="width:84%;height:84%;border-radius:50%;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center">

            {{-- Main Circular Frame --}}
            <div class="circle-frame" style="width:80%;height:80%;border-radius:50%;overflow:hidden;border:4px solid rgba(255,255,255,0.25);position:relative;box-shadow:0 12px 35px rgba(0,0,0,0.3);background:#0F172A">
                @if($slides->isNotEmpty())
                    @foreach($slides as $index => $slide)
                        <div class="hero-slide-item {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:{{ $index === 0 ? '1' : '0' }};transition:opacity 0.6s ease-in-out;z-index:{{ $index === 0 ? '2' : '1' }}">
                            <a href="{{ $slide->link_url }}" style="display:block;width:100%;height:100%;position:relative">
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" decoding="async" onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'" style="width:100%;height:100%;object-fit:cover;display:block">
                                {{-- Subtle gradient overlay for text readability if needed --}}
                                <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top, rgba(15,23,42,0.8) 0%, transparent 60%);padding:10px 12px 14px;text-align:center">
                                    <div class="text-white fw-bold" style="font-size:clamp(0.75rem, 2vw, 0.9rem);line-height:1.2;text-shadow:0 1px 4px rgba(0,0,0,0.6)">{{ $slide->title }}</div>
                                    @if($slide->subtitle)
                                        <div class="text-white-50" style="font-size:clamp(0.65rem, 1.5vw, 0.75rem);margin-top:2px">{{ $slide->subtitle }}</div>
                                    @endif
                                    @if($slide->button_text)
                                        <span class="badge bg-primary mt-1">{{ $slide->button_text }}</span>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                @else
                    {{-- Fallback Image --}}
                    <div style="position:absolute;top:0;left:0;width:100%;height:100%">
                        <img src="{{ asset('images/hero-fallback.svg') }}" alt="CapitalCart Store" style="width:100%;height:100%;object-fit:cover">
                    </div>
                @endif
            </div>
        </div>

        {{-- Small, nicely scoped Navigation Arrows (32px, safe SVGs) --}}
        @if($slides->count() > 1)
            <button type="button" class="slider-btn prev-btn" aria-label="Previous Slide" style="position:absolute;left:0;top:50%;transform:translateY(-50%);width:34px;height:34px;border-radius:50%;background:rgba(15,23,42,0.85);border:1px solid rgba(255,255,255,0.2);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:5;transition:all 0.2s">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px!important;height:14px!important"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <button type="button" class="slider-btn next-btn" aria-label="Next Slide" style="position:absolute;right:0;top:50%;transform:translateY(-50%);width:34px;height:34px;border-radius:50%;background:rgba(15,23,42,0.85);border:1px solid rgba(255,255,255,0.2);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:5;transition:all 0.2s">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px!important;height:14px!important"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        @endif
    </div>

    {{-- Dot Indicators --}}
    @if($slides->count() > 1)
        <div class="slider-dots d-flex justify-content-center align-items-center gap-2 mt-3" style="position:relative;z-index:6">
            @foreach($slides as $idx => $slide)
                <button type="button" class="slider-dot {{ $idx === 0 ? 'active' : '' }}" data-index="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}" style="width:{{ $idx === 0 ? '22px' : '8px' }};height:8px;border-radius:4px;border:none;background:{{ $idx === 0 ? '#F97316' : 'rgba(255,255,255,0.35)' }};padding:0;cursor:pointer;transition:all 0.3s ease"></button>
            @endforeach
        </div>
    @endif

    {{-- Floating Badges --}}
    <div style="position:absolute;top:10px;right:-10px;background:#fff;border-radius:0.75rem;padding:0.6rem 0.85rem;box-shadow:0 8px 25px rgba(0,0,0,0.2);min-width:130px;text-align:left;z-index:4">
        <div style="font-size:0.65rem;color:#64748B;text-transform:uppercase;font-weight:600">Today's Deals</div>
        <div style="font-weight:700;color:#0F172A;font-size:0.85rem">Up to 50% OFF</div>
    </div>
    <div style="position:absolute;bottom:25px;left:-10px;background:#fff;border-radius:0.75rem;padding:0.6rem 0.85rem;box-shadow:0 8px 25px rgba(0,0,0,0.2);min-width:130px;text-align:left;z-index:4">
        <div style="font-size:0.65rem;color:#64748B;text-transform:uppercase;font-weight:600">Free Delivery</div>
        <div style="font-weight:700;color:#0F172A;font-size:0.85rem">Orders Rs. 2,000+</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('capitalHeroSlider');
            if (!container) return;

            const slides = container.querySelectorAll('.hero-slide-item');
            const dots = container.querySelectorAll('.slider-dot');
            const prevBtn = container.querySelector('.prev-btn');
            const nextBtn = container.querySelector('.next-btn');
            const intervalTime = parseInt(container.getAttribute('data-autoplay')) || 5000;

            if (slides.length <= 1) return;

            let currentIndex = 0;
            let timer = null;
            let isHovered = false;

            function showSlide(index) {
                if (index < 0) index = slides.length - 1;
                if (index >= slides.length) index = 0;
                currentIndex = index;

                slides.forEach((s, i) => {
                    if (i === currentIndex) {
                        s.style.opacity = '1';
                        s.style.zIndex = '2';
                        s.classList.add('active');
                    } else {
                        s.style.opacity = '0';
                        s.style.zIndex = '1';
                        s.classList.remove('active');
                    }
                });

                dots.forEach((d, i) => {
                    if (i === currentIndex) {
                        d.style.width = '22px';
                        d.style.background = '#F97316';
                        d.classList.add('active');
                    } else {
                        d.style.width = '8px';
                        d.style.background = 'rgba(255,255,255,0.35)';
                        d.classList.remove('active');
                    }
                });
            }

            function startAutoplay() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                stopAutoplay();
                timer = setInterval(() => {
                    if (!isHovered) {
                        showSlide(currentIndex + 1);
                    }
                }, intervalTime);
            }

            function stopAutoplay() {
                if (timer) clearInterval(timer);
            }

            // Controls
            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showSlide(currentIndex + 1);
                    startAutoplay();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showSlide(currentIndex - 1);
                    startAutoplay();
                });
            }

            dots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    const idx = parseInt(dot.getAttribute('data-index'));
                    showSlide(idx);
                    startAutoplay();
                });
            });

            // Pause on hover
            container.addEventListener('mouseenter', () => { isHovered = true; });
            container.addEventListener('mouseleave', () => { isHovered = false; });

            // Touch swipe support
            let startX = 0;
            container.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                isHovered = true;
            }, { passive: true });

            container.addEventListener('touchend', (e) => {
                const diffX = e.changedTouches[0].clientX - startX;
                if (diffX > 40) {
                    showSlide(currentIndex - 1);
                } else if (diffX < -40) {
                    showSlide(currentIndex + 1);
                }
                isHovered = false;
                startAutoplay();
            }, { passive: true });

            startAutoplay();
        });
    </script>
</div>
