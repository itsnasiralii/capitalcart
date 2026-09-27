<div>
    <div class="row g-5">
        {{-- Gallery --}}
        <div class="col-lg-6">
            <div class="product-detail-gallery">
                <div class="main-image">
                    <img id="mainProductImage" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}"
                         onerror="this.onerror=null;this.src='https://placehold.co/600x600/f1f5f9/94a3b8?text={{ rawurlencode($product->name) }}'">
                </div>
                @if($product->images->count() > 1)
                    <div class="thumbnails">
                        @foreach($product->images as $i => $img)
                            <div class="thumb {{ $i === 0 ? 'active' : '' }}" onclick="switchImage('{{ $img->image_url }}', this)">
                                <img src="{{ $img->image_url }}" alt="{{ $product->name }} {{ $i + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Product Info --}}
        <div class="col-lg-6">
            <div class="text-muted small mb-2">{{ $product->category->name }}</div>
            <h1 class="font-poppins fw-bold" style="font-size:1.75rem;color:#0F172A">{{ $product->name }}</h1>

            {{-- Rating --}}
            @if($product->reviews->count() > 0)
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="star-rating fs-5">
                        @for($i = 1; $i <= 5; $i++)
                            {{ $i <= round($product->average_rating) ? '★' : '☆' }}
                        @endfor
                    </span>
                    <span class="text-muted small">{{ $product->average_rating }} ({{ $product->reviews->count() }} reviews)</span>
                </div>
            @endif

            {{-- Price --}}
            <div class="mb-4">
                <span class="fs-2 fw-bold {{ $product->is_on_sale ? 'text-danger' : 'text-dark' }}">
                    Rs. {{ number_format($this->effectivePrice, 0) }}
                </span>
                @if($product->is_on_sale)
                    <span class="text-muted text-decoration-line-through ms-2 fs-5">Rs. {{ number_format($product->base_price, 0) }}</span>
                    <span class="badge bg-danger ms-2">Save {{ $product->discount_percent }}%</span>
                @endif
            </div>

            {{-- Short Description --}}
            @if($product->short_description)
                <p class="text-muted mb-4">{{ $product->short_description }}</p>
            @endif

            {{-- Variants --}}
            @if($product->is_variable && !empty($this->groupedAttributes))
                @foreach($this->groupedAttributes as $typeSlug => $group)
                    <div class="mb-4">
                        <div class="fw-600 small mb-2">
                            {{ $group['name'] }}:
                            @if(isset($selectedAttributes[$typeSlug]))
                                @php $selId = $selectedAttributes[$typeSlug]; @endphp
                                @php $selAttr = collect($group['attributes'])->firstWhere('id', $selId); @endphp
                                @if($selAttr)
                                    <span class="text-primary">{{ $selAttr['value'] }}</span>
                                @endif
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($group['attributes'] as $attr)
                                @if($group['display_type'] === 'color_swatch')
                                    <button
                                        type="button"
                                        class="color-swatch {{ isset($selectedAttributes[$typeSlug]) && $selectedAttributes[$typeSlug] == $attr['id'] ? 'active' : '' }}"
                                        style="background-color: {{ $attr['color_hex'] ?? '#999' }}"
                                        title="{{ $attr['value'] }}"
                                        wire:click="selectAttribute('{{ $typeSlug }}', {{ $attr['id'] }})"
                                    ></button>
                                @else
                                    <button
                                        type="button"
                                        class="variant-btn {{ isset($selectedAttributes[$typeSlug]) && $selectedAttributes[$typeSlug] == $attr['id'] ? 'active' : '' }}"
                                        wire:click="selectAttribute('{{ $typeSlug }}', {{ $attr['id'] }})"
                                    >{{ $attr['value'] }}</button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @if($product->is_variable && !$selectedVariantId && !empty($selectedAttributes))
                    <div class="alert alert-warning py-2 small mb-3">
                        This combination is not available. Try a different selection.
                    </div>
                @endif
            @endif

            {{-- Quantity --}}
            <div class="mb-4">
                <div class="fw-600 small mb-2">Quantity
                    @if($this->maxStock > 0 && $this->maxStock <= 10)
                        <span class="text-warning ms-2">(Only {{ $this->maxStock }} left)</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button
                        wire:click="decrementQty"
                        class="btn btn-outline-secondary btn-sm px-3"
                        @if($quantity <= 1) disabled @endif
                    >−</button>
                    <span class="fw-bold px-3" style="min-width:2rem;text-align:center">{{ $quantity }}</span>
                    <button
                        wire:click="incrementQty"
                        class="btn btn-outline-secondary btn-sm px-3"
                        @if(!$this->isInStock || $quantity >= $this->maxStock || $quantity >= 10) disabled @endif
                    >+</button>
                </div>
            </div>

            {{-- Stock Status --}}
            <div class="mb-4">
                @if(!$product->is_variable)
                    @php $status = $product->stock_status; @endphp
                    <span class="stock-badge {{ $status === 'in_stock' ? 'in-stock' : ($status === 'low_stock' ? 'low-stock' : 'out-stock') }}">
                        {{ $status === 'in_stock' ? 'In Stock' : ($status === 'low_stock' ? 'Low Stock — Order Soon' : 'Out of Stock') }}
                    </span>
                @elseif($selectedVariantId)
                    <span class="stock-badge {{ $this->isInStock ? ($this->maxStock <= 5 ? 'low-stock' : 'in-stock') : 'out-stock' }}">
                        {{ $this->isInStock ? ($this->maxStock <= 5 ? 'Only ' . $this->maxStock . ' left' : 'In Stock') : 'Out of Stock' }}
                    </span>
                @elseif($product->is_variable)
                    <span class="text-muted small">Select options to check availability</span>
                @endif
            </div>

            {{-- CTA Buttons --}}
            <div class="d-flex gap-3 flex-wrap align-items-center mb-3">
                <button
                    wire:click="addToCart"
                    class="btn btn-primary btn-lg px-5 flex-grow-1"
                    @if(!$this->isInStock) disabled @endif
                >
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-2" style="vertical-align:-3px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ $this->isInStock ? 'Add to Cart' : 'Out of Stock' }}
                </button>

                {{-- Wishlist --}}
                <button
                    wire:click="toggleWishlist"
                    class="btn btn-lg px-3 {{ $inWishlist ? 'btn-danger' : 'btn-outline-secondary' }}"
                    title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                >
                    <svg width="20" height="20" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
            </div>

            {{-- Direct Order on WhatsApp --}}
            @if($this->isInStock)
                @php
                    $variantName = '';
                    if ($product->is_variable && $selectedVariantId) {
                        $v = $product->variants->firstWhere('id', $selectedVariantId);
                        $variantName = $v ? ' (' . $v->variantAttributes->pluck('value')->implode(', ') . ')' : '';
                    }
                    $storeName = $product->category?->whatsapp_number ? $product->category->name : 'CapitalCart';
                    $productWaMsg = "Assalam-o-Alaikum {$storeName}!\nI want to order this product:\n\n*Product:* " . $product->name . $variantName . "\n*Price:* Rs. " . number_format($this->effectivePrice, 0) . "\n*Qty:* " . $quantity . "\n*Link:* " . route('product.show', $product->slug);
                    $productWaUrl = $product->category
                        ? $product->category->getWhatsAppUrl($productWaMsg)
                        : \App\Models\Setting::getWhatsAppUrl($productWaMsg);
                @endphp
                <div class="mb-3">
                    <a href="{{ $productWaUrl }}" target="_blank" class="btn btn-outline-success w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" style="border-color:#25D366;color:#15803d">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#25D366"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.392-10.416c-4.418 0-8 3.582-8 8 0 1.579.46 3.05 1.258 4.29l-1.297 4.743 4.887-1.282c1.206.732 2.618 1.157 4.131 1.157 4.418 0 8-3.582 8-8s-3.582-8-8-8z"/></svg>
                        Direct Order on WhatsApp
                    </a>
                </div>
            @else
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" disabled>
                        Direct Order on WhatsApp (Out of Stock)
                    </button>
                </div>
            @endif

            {{-- SKU & Category --}}
            <div class="mt-3 pt-3 border-top">
                @if($product->sku)
                    <small class="text-muted d-block">SKU: {{ $product->sku }}</small>
                @endif
                <small class="text-muted d-block">Category:
                    <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="text-primary">{{ $product->category->name }}</a>
                </small>
            </div>
        </div>
    </div>

    {{-- Description & Reviews Tabs --}}
    <div class="mt-5">
        <ul class="nav nav-tabs border-bottom mb-4">
            <li class="nav-item">
                <a class="nav-link active fw-500" data-bs-toggle="tab" href="#desc">Description</a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-500" data-bs-toggle="tab" href="#reviews">
                    Reviews ({{ $product->reviews->count() }})
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="desc">
                @if($product->description)
                    <div class="prose" style="color:#374151;line-height:1.7">{!! $product->description !!}</div>
                @else
                    <p class="text-muted">No description available.</p>
                @endif
            </div>
            <div class="tab-pane fade" id="reviews">
                @if($product->reviews->isEmpty())
                    <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                @else
                    <div class="row g-3">
                        @foreach($product->reviews as $review)
                            <div class="col-md-6">
                                <div class="card border-0 bg-white shadow-sm p-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <strong class="small">{{ $review->reviewer_name }}</strong>
                                            <div class="star-rating">
                                                @for($i = 1; $i <= 5; $i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($review->title)
                                        <div class="fw-600 small mt-2">{{ $review->title }}</div>
                                    @endif
                                    @if($review->body)
                                        <p class="text-muted small mb-0 mt-1">{{ $review->body }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
