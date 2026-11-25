<div>
    {{-- Top Filter Bar (Horizontal, Above Products Grid) --}}
    <div class="shop-top-filters bg-white rounded-3 border p-3 p-md-3 mb-4 shadow-sm">
        {{-- Category Pills Row --}}
        <div class="d-flex align-items-center gap-2 overflow-x-auto text-nowrap pb-2 mb-3 border-bottom" style="scrollbar-width: thin;-webkit-overflow-scrolling: touch;">
            <button type="button" wire:click="$set('category', '')" 
                    class="btn btn-sm rounded-pill px-3 py-1 fw-500 {{ $category === '' ? 'btn-primary' : 'btn-light border text-dark' }}">
                All Categories
            </button>
            @foreach($this->categories as $cat)
                <button type="button" wire:click="$set('category', '{{ $cat->slug }}')" 
                        class="btn btn-sm rounded-pill px-3 py-1 fw-500 {{ $category === $cat->slug ? 'btn-primary' : 'btn-light border text-dark' }}">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        {{-- Filters Control Row --}}
        <div class="row g-2 g-md-3 align-items-center">
            {{-- Search Bar --}}
            <div class="col-12 col-md-4 col-lg-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </span>
                    <input type="text" aria-label="Search products" wire:model.live.debounce.300ms="search" 
                           class="form-control form-control-sm border-start-0 ps-0" placeholder="Search products...">
                </div>
            </div>

            {{-- Price Range Filter --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="d-flex align-items-center gap-1">
                    <span class="small text-muted fw-semibold">Rs.</span>
                    <input type="number" aria-label="Minimum price" wire:model.live="minPrice" 
                           class="form-control form-control-sm text-center" min="0" placeholder="Min" style="min-width: 65px;">
                    <span class="text-muted">–</span>
                    <input type="number" aria-label="Maximum price" wire:model.live="maxPrice" 
                           class="form-control form-control-sm text-center" min="0" placeholder="No maximum" style="min-width: 85px;">
                </div>
            </div>

            {{-- In Stock & On Sale Toggles --}}
            <div class="col-7 col-sm-6 col-md-4 col-lg-4 d-flex align-items-center gap-3">
                <div class="form-check mb-0">
                    <input type="checkbox" wire:model.live="inStockOnly" class="form-check-input" id="inStockCheck">
                    <label class="form-check-label small fw-500 text-nowrap" for="inStockCheck">In Stock</label>
                </div>
                <div class="form-check mb-0">
                    <input type="checkbox" wire:model.live="onSale" class="form-check-input" id="onSaleCheck">
                    <label class="form-check-label small fw-500 text-nowrap text-danger" for="onSaleCheck">🔥 On sale only</label>
                </div>
            </div>

            {{-- Reset Button --}}
            <div class="col-5 col-md-12 col-lg-2 d-flex justify-content-end">
                <button wire:click="resetFilters" class="btn btn-sm btn-outline-secondary w-100 w-lg-auto text-nowrap">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Product Grid Header (Counter + Sort & Per-Page) --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 px-1">
        <span class="text-muted small">
            Showing <strong>{{ $this->products->total() }}</strong> products
            @if($search) for "<strong>{{ $search }}</strong>" @endif
            @if($category) in <span class="badge bg-light text-dark border">{{ $this->categories->firstWhere('slug', $category)?->name ?? $category }}</span> @endif
        </span>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0 me-1">Sort:</label>
            <select aria-label="Sort products" wire:model.live="sort" class="form-select form-select-sm" style="width:auto">
                <option value="newest">Newest</option>
                <option value="featured">Featured</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="name_asc">Name A–Z</option>
            </select>
            <select aria-label="Products per page" wire:model.live="perPage" class="form-select form-select-sm" style="width:auto">
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="48">48</option>
            </select>
        </div>
    </div>

    {{-- Products Grid (Full 12-Column Width: 4 cards per row on desktop, 3 on tablet, 2 on mobile) --}}
    @if($this->products->isEmpty())
        <div class="text-center py-5 bg-white rounded-3 border p-4 my-3">
            <svg width="72" height="72" fill="none" stroke="#D1D5DB" stroke-width="1.5" viewBox="0 0 24 24" class="mb-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h5 class="fw-bold">No products found</h5>
            <p class="text-muted small">Try adjusting your filters or search keywords.</p>
            <button wire:click="resetFilters" class="btn btn-primary btn-sm px-4">Clear Filters</button>
        </div>
    @else
        <div class="row g-3 g-md-4">
            @foreach($this->products as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card h-100">
                        <a href="{{ route('product.show', $product->slug) }}" class="product-img-wrap d-block">
                            @if($product->is_on_sale)
                                <span class="badge-sale">-{{ $product->discount_percent }}%</span>
                            @endif
                            @if($product->is_featured)
                                <span class="badge-featured">Featured</span>
                            @endif
                            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy"
                                 onerror="this.onerror=null;this.src='https://placehold.co/600x600/f1f5f9/94a3b8?text={{ rawurlencode($product->name) }}'">
                        </a>
                        <div class="product-body d-flex flex-column flex-grow-1">
                            <div class="product-category text-truncate">{{ $product->category?->name ?? 'General' }}</div>
                            <a href="{{ route('product.show', $product->slug) }}" class="product-name text-decoration-none">{{ $product->name }}</a>

                            {{-- Stars --}}
                            @if($product->reviews->count() > 0)
                                <div class="d-flex align-items-center gap-1 mb-2">
                                    <span class="star-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            {{ $i <= round($product->average_rating) ? '★' : '☆' }}
                                        @endfor
                                    </span>
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

                            <div class="d-flex gap-2">
                                @if($product->stock_quantity > 0 && !$product->is_variable)
                                    <button wire:click="addToCart({{ $product->id }})" class="btn-add-cart flex-grow-1">
                                        Add to Cart
                                    </button>
                                @else
                                    <a href="{{ route('product.show', $product->slug) }}" class="btn-add-cart flex-grow-1 text-center">
                                        {{ $product->is_variable ? 'Select Options' : 'Out of Stock' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $this->products->links('components.custom-pagination') }}
        </div>
    @endif
</div>
