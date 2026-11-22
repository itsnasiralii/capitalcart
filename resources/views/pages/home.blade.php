<x-layouts.app title="Home — Modern Online Store">

    {{-- Hero --}}
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Discover <span class="hero-accent">Everyday</span> Essentials</h1>
                    <p class="mt-2 mb-3">Find your next favourite. Order on WhatsApp, pay on delivery.</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('shop') }}" class="btn px-4" style="background:#F97316;color:#fff;font-weight:600">Shop Now</a>
                        <a href="{{ $featured->isNotEmpty() ? '#featured-products' : route('shop') }}" class="btn btn-outline-light px-4">Explore Products</a>
                    </div>
                </div>
                <div class="col-md-4 d-none d-md-block text-center">
                    <livewire:hero-slider />
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Products --}}
    @if($featured->isNotEmpty())
        <section id="featured-products" class="py-4" style="background:#fff">
            <div class="container">
                <div class="d-flex align-items-end justify-content-between mb-4">
                    <h2 class="section-title mb-0">Featured Products</h2>
                    <a href="{{ route('shop', ['sort' => 'featured']) }}" class="text-primary fw-500 small">View All →</a>
                </div>
                <div class="row g-3">
                    @foreach($featured as $product)
                        <div class="col-6 col-lg-3">
                            <div class="product-card h-100">
                                <a href="{{ route('product.show', $product->slug) }}" class="product-img-wrap d-block">
                                    @if($product->is_on_sale)
                                        <span class="badge-sale">-{{ $product->discount_percent }}%</span>
                                    @endif
                                    <span class="badge-featured">Featured</span>
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy"
                                         onerror="this.onerror=null;this.src='https://placehold.co/600x600/f1f5f9/94a3b8?text={{ rawurlencode($product->name) }}'">
                                </a>
                                <div class="product-body d-flex flex-column">
                                    <div class="product-category text-truncate">{{ $product->category?->name ?? 'General' }}</div>
                                    <a href="{{ route('product.show', $product->slug) }}" class="product-name text-decoration-none">{{ $product->name }}</a>
                                    @if($product->reviews->count() > 0)
                                        <div class="d-flex align-items-center gap-1 mb-2">
                                            <span class="star-rating">{{ str_repeat('★', round($product->average_rating)) }}{{ str_repeat('☆', 5 - round($product->average_rating)) }}</span>
                                            <span class="text-muted" style="font-size:0.75rem">({{ $product->reviews->count() }})</span>
                                        </div>
                                    @endif
                                    <div class="product-price mt-auto mb-2">
                                        @if($product->is_on_sale)
                                            <span class="sale-price">Rs. {{ number_format($product->sale_price, 0) }}</span>
                                            <span class="original-price">Rs. {{ number_format($product->base_price, 0) }}</span>
                                        @else
                                            Rs. {{ number_format($product->base_price, 0) }}
                                        @endif
                                    </div>
                                    <a href="{{ route('product.show', $product->slug) }}" class="btn-add-cart d-block text-center">
                                        {{ $product->is_variable ? 'Select Options' : 'View Product' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Categories --}}
    <section class="py-5" style="background:#F8FAFC">
        <div class="container">
            <div class="d-flex align-items-end justify-content-between mb-4">
                <div>
                    <span class="text-uppercase text-muted fw-600" style="font-size:0.75rem;letter-spacing:0.1em">Explore Collections</span>
                    <h2 class="section-title mb-0">Shop by Category</h2>
                </div>
                <a href="{{ route('shop') }}" class="text-primary fw-600 small">View All Categories &rarr;</a>
            </div>
            <div class="row g-3">
                @foreach($categories as $cat)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('shop', ['category' => $cat->slug]) }}" class="category-card d-block text-decoration-none">
                            <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" loading="lazy">
                            <div class="overlay">
                                <div class="w-100">
                                    <h5 class="text-truncate mb-0" title="{{ $cat->name }}">{{ Str::limit($cat->name, 26) }}</h5>
                                    <span class="small" style="color:rgba(255,255,255,0.85);font-size:0.75rem">{{ $cat->products_count ?? $cat->products()->count() }} items</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- All products --}}
    <livewire:product-scroller />

    {{-- New Arrivals --}}
    @if($newArrivals->isNotEmpty())
        <section class="py-5" style="background:#fff">
            <div class="container">
                <div class="d-flex align-items-end justify-content-between mb-4">
                    <h2 class="section-title mb-0">New Arrivals</h2>
                    <a href="{{ route('shop', ['sort' => 'newest']) }}" class="text-primary fw-500 small">View All →</a>
                </div>
                <div class="row g-3">
                    @foreach($newArrivals as $product)
                        <div class="col-6 col-lg-3">
                            <div class="product-card h-100">
                                <a href="{{ route('product.show', $product->slug) }}" class="product-img-wrap d-block">
                                    <span class="badge-featured" style="background:#22C55E">New</span>
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy"
                                         onerror="this.onerror=null;this.src='https://placehold.co/600x600/f1f5f9/94a3b8?text={{ rawurlencode($product->name) }}'">
                                </a>
                                <div class="product-body">
                                    <div class="product-category text-truncate">{{ $product->category?->name ?? 'General' }}</div>
                                    <a href="{{ route('product.show', $product->slug) }}" class="product-name text-decoration-none">{{ $product->name }}</a>
                                    <div class="product-price mt-2 mb-2">Rs. {{ number_format($product->effective_price, 0) }}</div>
                                    <a href="{{ route('product.show', $product->slug) }}" class="btn-add-cart d-block text-center">View Product</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Value Propositions --}}
    <section class="py-4" style="background:#fff;border-bottom:1px solid #E5E7EB">
        <div class="container">
            <div class="row g-3 text-center">
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center">
                        <span style="font-size:2rem">🚚</span>
                        <div class="text-start">
                            <div class="fw-600 small">Free Shipping</div>
                            <div class="text-muted" style="font-size:0.8rem">On orders over Rs. 2,000</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center">
                        <span style="font-size:2rem">🔄</span>
                        <div class="text-start">
                            <div class="fw-600 small">Easy Returns</div>
                            <div class="text-muted" style="font-size:0.8rem">30-day return policy</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center">
                        <span style="font-size:2rem">🔒</span>
                        <div class="text-start">
                            <div class="fw-600 small">Secure Checkout</div>
                            <div class="text-muted" style="font-size:0.8rem">256-bit SSL encryption</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 justify-content-center">
                        <span style="font-size:2rem">🎧</span>
                        <div class="text-start">
                            <div class="fw-600 small">24/7 Support</div>
                            <div class="text-muted" style="font-size:0.8rem">Customer care anytime</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-5 text-center" style="background:#3B82F6">
        <div class="container">
            <h2 class="text-white font-poppins fw-bold mb-2">Start Shopping Today</h2>
            <p class="text-white mb-4" style="opacity:0.85">Create your account and enjoy exclusive member discounts and early access to sales.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('register') }}" class="btn btn-lg btn-light px-5 fw-600">Create Account</a>
                <a href="{{ route('shop') }}" class="btn btn-lg btn-outline-light px-5">Browse Shop</a>
            </div>
        </div>
    </section>

</x-layouts.app>
