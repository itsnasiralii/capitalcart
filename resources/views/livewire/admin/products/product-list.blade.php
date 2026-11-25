<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="font-poppins fw-bold mb-0">Products</h4>
            <small class="text-muted">Manage your product catalog</small>
        </div>
        <div class="d-flex gap-2 w-100 w-sm-auto justify-content-between justify-content-sm-end">
            <button wire:click="openBulkModal" class="btn btn-outline-primary flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Bulk Import
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary flex-fill flex-sm-grow-0 text-center">
                + Add Product
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-3 border p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name or SKU...">
            </div>
            <div class="col-6 col-md-3">
                <select wire:model.live="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select wire:model.live="stock" class="form-select">
                    <option value="">All Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
            <div class="col-9 col-md-2">
                <select wire:model.live="sort" class="form-select">
                    <option value="newest">Newest</option>
                    <option value="name">Name</option>
                    <option value="price_asc">Price ↑</option>
                    <option value="price_desc">Price ↓</option>
                    <option value="stock_asc">Stock ↑</option>
                </select>
            </div>
            <div class="col-3 col-md-1">
                <button wire:click="resetFilters" class="btn btn-outline-secondary w-100" title="Reset Filters">↺</button>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
        <label for="admin-products-status" class="small fw-semibold mb-0">Status</label>
        <select id="admin-products-status" wire:model.live="status" class="form-select form-select-sm w-auto me-sm-3">
            <option value="">All products</option>
            <option value="enabled">Enabled only</option>
            <option value="disabled">Disabled only</option>
        </select>
        <label for="admin-products-per-page" class="small fw-semibold mb-0">Products per page</label>
        <select id="admin-products-per-page" wire:model.live="perPage" class="form-select form-select-sm w-auto"
                wire:loading.attr="disabled" wire:target="perPage">
            @foreach(\App\Livewire\Admin\Products\ProductList::PAGE_SIZES as $size)
                <option value="{{ $size }}">{{ $size }}</option>
            @endforeach
        </select>
        <span wire:loading.delay wire:target="perPage,status,search,category,stock,sort,resetFilters" class="small text-muted" role="status">Loading products...</span>
    </div>

    {{-- Bulk Import Modal --}}
    @if($showBulkModal)
        <div class="modal show d-block" style="background:rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="fw-bold mb-1">Bulk Product Import (CSV)</h5>
                            <p class="text-muted small mb-0">Import new products or update descriptions and prices. Existing stock, visibility and photos are preserved.</p>
                        </div>
                        <button type="button" wire:click="closeBulkModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4 p-3 bg-light rounded-3 border">
                            <label class="form-label fw-bold small mb-1">Select CSV File</label>
                            <input type="file" wire:model="csvFile" class="form-control" accept=".csv">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    Leave blank to automatically import the bundled <strong>products.csv</strong> (890 scraped items).
                                </small>
                                <div wire:loading wire:target="csvFile" class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>
                            @error('csvFile') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Price Markup (PKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text">+ Rs.</span>
                                    <input type="number" wire:model="bulkMarkup" class="form-control" placeholder="250" step="10">
                                </div>
                                <small class="text-muted" style="font-size:0.75rem">Added automatically on top of the reference scraped price.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Default Stock per Product</label>
                                <input type="number" wire:model="bulkDefaultStock" class="form-control" placeholder="50" min="0">
                                <small class="text-muted" style="font-size:0.75rem">For new products only. Existing stock is preserved.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Fallback Category</label>
                                <select wire:model="bulkDefaultCategory" class="form-select">
                                    <option value="">Auto-create from CSV or first category</option>
                                    @foreach($this->categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 d-flex align-items-center pt-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="bulkUpdateExisting" class="form-check-input" id="bulkUpdateCheck" checked>
                                    <label class="form-check-label small fw-500" for="bulkUpdateCheck">
                                        Update descriptions and prices for matching SKUs
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if($importFeedback)
                            <div class="alert alert-info mt-3 mb-0 small">
                                {{ $importFeedback }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" wire:click="closeBulkModal" class="btn btn-outline-secondary" wire:loading.attr="disabled">Cancel</button>
                        <button type="button" wire:click="runBulkImport" class="btn btn-primary d-inline-flex align-items-center gap-2" wire:loading.attr="disabled">
                            <span wire:loading wire:target="runBulkImport" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <span wire:loading.remove wire:target="runBulkImport">Start Bulk Import</span>
                            <span wire:loading wire:target="runBulkImport">Importing Products...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if($deleteId)
        <div class="modal show d-block" style="background:rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <div class="mb-3 text-danger" style="font-size:2.5rem">🗑️</div>
                        <h5 class="fw-bold">Delete Product?</h5>
                        <p class="text-muted small mb-4">This action cannot be undone.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button wire:click="$set('deleteId', null)" class="btn btn-outline-secondary">Cancel</button>
                            <button wire:click="deleteProduct" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Products Display (Mobile Cards + Desktop Table) --}}
    <div class="admin-table bg-white rounded-3 border overflow-hidden">
        {{-- Mobile Cards View (Below md breakpoint) --}}
        <div class="d-md-none">
            @forelse($this->products as $product)
                <div class="p-3 border-bottom" wire:key="product-mobile-{{ $product->id }}">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <img src="{{ $product->primaryImage?->image_url ?? asset('images/hero-fallback.svg') }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="52" height="52" style="width:52px;height:52px;object-fit:cover;border-radius:0.5rem;flex-shrink:0" onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark text-truncate" style="font-size:0.92rem" title="{{ $product->name }}">{{ $product->name }}</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap small text-muted mt-1">
                                <span class="badge bg-light text-dark border">{{ $product->category?->name ?? 'General' }}</span>
                                @if($product->sku)
                                    <span style="font-size:0.75rem">SKU: {{ $product->sku }}</span>
                                @endif
                                @if($product->is_featured)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:0.65rem">Featured</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-2">
                        <div>
                            <span class="fw-bold text-dark" style="font-size:0.95rem">Rs. {{ number_format($product->effective_price, 0) }}</span>
                            @if($product->is_on_sale)
                                <span class="text-muted text-decoration-line-through small ms-1" style="font-size:0.75rem">Rs. {{ number_format($product->base_price, 0) }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="stock-badge {{ $product->stock_status === 'in_stock' ? 'in-stock' : ($product->stock_status === 'low_stock' ? 'low-stock' : 'out-stock') }}">
                                {{ $product->stock_quantity }} units
                            </span>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" wire:click="toggleActive({{ $product->id }})" {{ $product->is_active ? 'checked' : '' }} title="Toggle Active Status">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2 pt-2 border-top">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary flex-fill d-flex align-items-center justify-content-center gap-1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Edit
                        </a>
                        <a href="{{ route('admin.products.inventory', $product->id) }}" class="btn btn-sm btn-outline-secondary px-3" title="Inventory">📦</a>
                        <button wire:click="confirmDelete({{ $product->id }})" class="btn btn-sm btn-outline-danger px-3" title="Delete">Del</button>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5 p-3">
                    No products found. Use <strong>+ Add Product</strong> or <strong>Bulk Import CSV</strong>.
                </div>
            @endforelse
        </div>

        {{-- Desktop Table View (md and above) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-borderless mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->products as $product)
                        <tr wire:key="product-desktop-{{ $product->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $product->primaryImage?->image_url ?? asset('images/hero-fallback.svg') }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="48" height="48" style="width:48px;height:48px;object-fit:cover;border-radius:0.5rem" onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'">
                                    <div>
                                        <div class="fw-600 small">{{ $product->name }}</div>
                                        @if($product->sku)
                                            <div class="text-muted" style="font-size:0.75rem">{{ $product->sku }}</div>
                                        @endif
                                        @if($product->is_featured)
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:0.65rem">Featured</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="small">{{ $product->category?->name ?? 'General' }}</td>
                            <td>
                                <div class="fw-600">Rs. {{ number_format($product->effective_price, 0) }}</div>
                                @if($product->is_on_sale)
                                    <div class="text-muted text-decoration-line-through small">Rs. {{ number_format($product->base_price, 0) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="stock-badge {{ $product->stock_status === 'in_stock' ? 'in-stock' : ($product->stock_status === 'low_stock' ? 'low-stock' : 'out-stock') }}">
                                    {{ $product->stock_quantity }} units
                                </span>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:click="toggleActive({{ $product->id }})" {{ $product->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="{{ route('admin.products.inventory', $product->id) }}" class="btn btn-sm btn-outline-secondary" title="Inventory">📦</a>
                                    <button wire:click="confirmDelete({{ $product->id }})" class="btn btn-sm btn-outline-danger">Del</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No products found. Use <strong>+ Add Product</strong> or <strong>Bulk Import CSV</strong> to add products.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Responsive Pagination --}}
        <div class="p-3 border-top">
            {{ $this->products->links('components.custom-pagination') }}
        </div>
    </div>
</div>
