<div>
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="font-poppins fw-bold mb-1">Categories</h2>
            <p class="text-muted small mb-0">Manage catalog categories, visual banners, and public display orders.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search categories by name or description...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active (Visible)</option>
                        <option value="inactive">Disabled (Hidden)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead style="background:#F8FAFC">
                    <tr>
                        <th class="ps-3" style="width:70px">Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>WhatsApp</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="ps-3">
                            @if($category->image_url)
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #E2E8F0">
                            @else
                                <div style="width:44px;height:44px;border-radius:8px;background:#E2E8F0;display:flex;align-items:center;justify-content:center;color:#64748B;font-size:1.2rem">
                                    📁
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $category->name }}</div>
                            @if($category->description)
                                <small class="text-muted d-block text-truncate" style="max-width:260px">{{ $category->description }}</small>
                            @endif
                        </td>
                        <td>
                            <code class="text-muted small">/shop?category={{ $category->slug }}</code>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1">
                                {{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}
                            </span>
                        </td>
                        <td>
                            @if($category->whatsapp_number)
                                <span class="badge bg-success-subtle text-success px-2 py-1">{{ $category->whatsapp_number }}</span>
                            @else
                                <span class="text-muted small">Store default</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">#{{ $category->sort_order }}</span>
                        </td>
                        <td>
                            <button wire:click="toggleStatus({{ $category->id }})" class="btn btn-sm border-0 p-0" title="Click to toggle">
                                @if($category->is_active)
                                    <span class="badge bg-success-subtle text-success px-2 py-1">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1">Hidden</span>
                                @endif
                            </button>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <button wire:click="openEditModal({{ $category->id }})" class="btn btn-outline-secondary" title="Edit">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $category->id }})" class="btn btn-outline-danger" title="Delete">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="fs-1 mb-2">📁</div>
                            No categories found matching your filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="p-3 border-top">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal (Native Bootstrap modal controlled by Livewire events) --}}
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form wire:submit.prevent="saveCategory">
                    <div class="modal-header">
                        <h5 class="modal-title font-poppins fw-bold">{{ $isEditing ? 'Edit Category' : 'Create Category' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model.live="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Smart Watches">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL Slug <span class="text-danger">*</span></label>
                            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="e.g. smart-watches">
                            <small class="text-muted">Unique URL segment (auto-generated from name)</small>
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description (Optional)</label>
                            <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Short summary of this category..."></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category WhatsApp Number (Optional)</label>
                            <input type="text" wire:model="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" placeholder="e.g. 03009362584">
                            <small class="text-muted">Orders for this category will use this number. Leave blank to use the main CapitalCart number.</small>
                            @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Display Order</label>
                                <input type="number" wire:model="sort_order" class="form-control @error('sort_order') is-invalid @enderror" min="0">
                                <small class="text-muted">Lower numbers show first</small>
                                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-center pt-3">
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" wire:model="is_active" class="form-check-input" id="isActiveSwitch">
                                    <label class="form-check-label fw-semibold" for="isActiveSwitch">Active in Public Store</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Banner / Image</label>
                            <input type="file" wire:model="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            <small class="text-muted">PNG, JPG, WebP up to 2MB</small>
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <div wire:loading wire:target="image" class="text-primary small mt-1">
                                <span class="spinner-border spinner-border-sm me-1"></span> Uploading preview...
                            </div>

                            @php
                                $catTempUrl = null;
                                if ($image) {
                                    try {
                                        $catTempUrl = $image->temporaryUrl();
                                    } catch (\Throwable $e) {
                                        $catTempUrl = null;
                                    }
                                }
                            @endphp

                            @if ($catTempUrl)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">New image preview:</small>
                                    <img src="{{ $catTempUrl }}" style="width:100px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #CBD5E1">
                                </div>
                            @elseif ($image)
                                <div class="mt-2">
                                    <small class="text-success d-block mb-1">✓ File ready for upload: {{ is_string($image) ? $image : $image->getClientOriginalName() }}</small>
                                </div>
                            @elseif ($existingImageUrl)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">Current image:</small>
                                    <img src="{{ $existingImageUrl }}" style="width:100px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #CBD5E1">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetForm">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveCategory" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $isEditing ? 'Save Changes' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Safe Delete Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-poppins fw-bold text-danger d-flex align-items-center gap-2">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Delete Category
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body py-3">
                    @if($categoryProductCount > 0)
                        <div class="alert alert-warning mb-3">
                            <strong>⚠️ Safe Deletion Warning:</strong> This category currently contains <strong>{{ $categoryProductCount }}</strong> {{ Str::plural('product', $categoryProductCount) }}.
                            To prevent orphaned products and broken store links, you must reassign these products to another category before deleting.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reassign {{ $categoryProductCount }} products to:</label>
                            <select wire:model="reassignToCategoryId" class="form-select @error('reassign') is-invalid @enderror">
                                <option value="">-- Select Destination Category --</option>
                                @foreach($otherCategories as $other)
                                    <option value="{{ $other->id }}">{{ $other->name }}</option>
                                @endforeach
                            </select>
                            @error('reassign') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @else
                        <p class="mb-0 text-muted">Are you sure you want to permanently delete this category? This action cannot be undone.</p>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteCategory" wire:loading.attr="disabled">
                        <span wire:loading wire:target="deleteCategory" class="spinner-border spinner-border-sm me-1"></span>
                        {{ $categoryProductCount > 0 ? 'Reassign Products & Delete' : 'Confirm Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('categoryModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                window.addEventListener('open-category-modal', () => modal.show());
                window.addEventListener('close-category-modal', () => modal.hide());
            }
        });
    </script>
</div>
