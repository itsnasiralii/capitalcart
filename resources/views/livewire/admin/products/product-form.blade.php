<div x-data="{ uploading: false, progress: 0, uploadError: '' }"
     x-on:livewire-upload-start="uploading = true; progress = 0; uploadError = ''"
     x-on:livewire-upload-finish="uploading = false"
     x-on:livewire-upload-cancel="uploading = false"
     x-on:livewire-upload-error="uploading = false; uploadError = 'Upload failed. Choose JPG, PNG, WebP or GIF files up to 5 MB each, and try again.'"
     x-on:livewire-upload-progress="progress = $event.detail.progress">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
        <h4 class="font-poppins fw-bold mb-0">{{ $productId ? 'Edit Product' : 'Add New Product' }}</h4>
    </div>

    <div class="row g-4">
        {{-- Main Info --}}
        <div class="col-lg-8">
            <div class="bg-white rounded-3 border p-4 mb-4">
                <h6 class="font-poppins fw-bold mb-3">Basic Information</h6>
                <div class="mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter product name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea wire:model="short_description" class="form-control" rows="2" placeholder="Brief summary..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Full Description</label>
                    <textarea wire:model="description" class="form-control" rows="8" placeholder="Detailed description (HTML supported)..."></textarea>
                </div>
            </div>

            {{-- Images --}}
            <div class="bg-white rounded-3 border p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-poppins fw-bold mb-0">Product Images</h6>
                    <button wire:click="addImage" type="button" class="btn btn-sm btn-outline-primary">+ Add Image URL</button>
                </div>
                <label for="productImageUploads" class="form-label fw-semibold">Upload from your device</label>
                <input id="productImageUploads" type="file" wire:model="newImages" multiple
                       accept="image/jpeg,image/png,image/webp,image/gif" class="form-control">
                <p class="small text-muted mt-2 mb-2">Choose up to 8 photos, 5 MB and 16 megapixels each. JPG, PNG, WebP or GIF (saved as a still image). Photos are saved when you save the product.</p>
                <div x-show="uploading" x-cloak class="small text-primary mb-2" role="status">
                    Uploading: <span x-text="progress"></span>%
                </div>
                <div x-show="uploadError" x-cloak x-text="uploadError" class="alert alert-danger small" role="alert"></div>
                @error('newImages') <div class="text-danger small" role="alert">{{ $message }}</div> @enderror
                @error('newImages.*') <div class="text-danger small" role="alert">{{ $message }}</div> @enderror
                @if($newImages)
                    <div class="d-flex flex-wrap gap-3 my-3">
                        @foreach($newImages as $index => $file)
                            @php
                                $preview = null;
                                try { $preview = $file->temporaryUrl(); } catch (\Throwable $e) {}
                            @endphp
                            <div wire:key="new-photo-{{ $index }}-{{ $file->getFilename() }}" class="border rounded p-2" style="width:130px">
                                @if($preview)
                                    <img src="{{ $preview }}" alt="New product photo {{ $index + 1 }}" class="rounded mb-1" style="width:110px;height:90px;object-fit:contain">
                                @endif
                                <div class="small text-truncate" title="{{ $file->getClientOriginalName() }}">{{ $file->getClientOriginalName() }}</div>
                                <button wire:click="removeNewImage({{ $index }})" type="button" class="btn btn-sm btn-outline-danger mt-1" :disabled="uploading">Remove</button>
                            </div>
                        @endforeach
                    </div>
                    <label class="form-check mb-3">
                        <input type="checkbox" wire:model="useUploadedAsPrimary" class="form-check-input">
                        <span class="form-check-label">Use the first uploaded photo as the main product image</span>
                    </label>
                @endif
                <hr>
                <p class="small text-muted">Existing images / direct image URLs. Remove an old image to replace it, or choose Make Primary.</p>
                @foreach($images as $idx => $img)
                    <div wire:key="product-image-{{ $idx }}" class="border rounded p-2 mb-2">
                        <label class="visually-hidden" for="image-url-{{ $idx }}">Image URL {{ $idx + 1 }}</label>
                        <input id="image-url-{{ $idx }}" type="text" wire:model="images.{{ $idx }}.url" class="form-control mb-2" placeholder="https://example.com/image.jpg">
                        @error('images.'.$idx.'.url') <div class="text-danger small">{{ $message }}</div> @enderror
                        <div class="d-flex align-items-center flex-wrap gap-2">
                        @if(!empty($img['url']))
                            <img src="{{ $img['url'] }}" alt="Product image {{ $idx + 1 }}" style="height:60px;width:60px;object-fit:cover;border-radius:0.375rem">
                        @endif
                        @if($idx === 0)
                            <span class="badge bg-primary">Primary</span>
                        @else
                            <button wire:click="makePrimary({{ $idx }})" type="button" class="btn btn-sm btn-outline-primary">Make Primary</button>
                        @endif
                        <button wire:click="removeImage({{ $idx }})" type="button" class="btn btn-sm btn-outline-danger">Remove</button>
                        </div>
                    </div>
                @endforeach
                @error('images') <div class="text-danger small">{{ $message }}</div> @enderror
                <small class="text-muted">Google Drive preview links are not direct image URLs. Download the photo to your laptop, then upload it above.</small>
            </div>

            {{-- Variants --}}
            <div class="bg-white rounded-3 border p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <h6 class="font-poppins fw-bold mb-0">Product Variants</h6>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" wire:model.live="is_variable" class="form-check-input" id="isVariable">
                        <label class="form-check-label small" for="isVariable">This product has variants</label>
                    </div>
                </div>

                @if($is_variable)
                    <div class="mb-3">
                        <button wire:click="addVariant" type="button" class="btn btn-sm btn-outline-primary">+ Add Variant</button>
                    </div>
                    @foreach($variants as $vidx => $variant)
                        <div class="border rounded-3 p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="small">Variant {{ $vidx + 1 }}</strong>
                                <button wire:click="removeVariant({{ $vidx }})" type="button" class="btn btn-sm btn-outline-danger">Remove</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">SKU</label>
                                    <input type="text" wire:model="variants.{{ $vidx }}.sku" class="form-control form-control-sm" placeholder="SKU-001-RED">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Price Modifier ($)</label>
                                    <input type="number" wire:model="variants.{{ $vidx }}.price_modifier" class="form-control form-control-sm" placeholder="0.00" step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Stock Qty</label>
                                    <input type="number" wire:model="variants.{{ $vidx }}.stock_quantity" class="form-control form-control-sm" placeholder="0" min="0">
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label small">Attributes</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($this->attributeTypes as $attrType)
                                            @foreach($attrType->attributes as $attr)
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox"
                                                        class="form-check-input"
                                                        value="{{ $attr->id }}"
                                                        id="attr-{{ $vidx }}-{{ $attr->id }}"
                                                        wire:model="variants.{{ $vidx }}.attribute_ids">
                                                    <label class="form-check-label small" for="attr-{{ $vidx }}-{{ $attr->id }}">
                                                        @if($attr->color_hex)
                                                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:{{ $attr->color_hex }};border:1px solid #ccc;margin-right:3px"></span>
                                                        @endif
                                                        {{ $attrType->name }}: {{ $attr->value }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" wire:model="sku" class="form-control" placeholder="SKU-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" wire:model="stock_quantity" class="form-control" min="0">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="bg-white rounded-3 border p-4 mb-3">
                <h6 class="font-poppins fw-bold mb-3">Pricing</h6>
                <div class="mb-3">
                    <label class="form-label">Base Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" wire:model="base_price" class="form-control @error('base_price') is-invalid @enderror" placeholder="0.00" step="0.01" min="0">
                        @error('base_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sale Price <small class="text-muted">(optional)</small></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" wire:model="sale_price" class="form-control" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cost Price <small class="text-muted">(optional)</small></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" wire:model="cost_price" class="form-control" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3 border p-4 mb-3">
                <h6 class="font-poppins fw-bold mb-3">Organization</h6>
                <div class="mb-3">
                    <label class="form-label">Category *</label>
                    <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="0">Select category...</option>
                        @foreach($this->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" wire:model="is_active" class="form-check-input" id="isActive">
                    <label class="form-check-label" for="isActive">Active (visible in store)</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" wire:model="is_featured" class="form-check-input" id="isFeatured">
                    <label class="form-check-label" for="isFeatured">Featured product</label>
                </div>
            </div>

            @error('save') <div class="alert alert-danger" role="alert">{{ $message }}</div> @enderror
            <button wire:click="save" type="button" class="btn btn-primary w-100 btn-lg" wire:loading.attr="disabled" :disabled="uploading || uploadError !== ''">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $productId ? 'Save Changes' : 'Create Product' }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </div>
    </div>
</div>
