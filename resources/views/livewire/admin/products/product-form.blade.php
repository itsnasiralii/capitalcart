<div x-data="{ uploads: 0, syncFailed: false }"
     x-on:catalog-upload-start.window="uploads++"
     x-on:catalog-upload-finish.window="uploads = Math.max(0, uploads - 1)"
     x-on:catalog-upload-sync-failed.window="syncFailed = true">
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
                    <button wire:click="addImage" type="button" class="btn btn-sm btn-outline-primary" :disabled="uploads > 0">+ Add Image</button>
                </div>

                <div class="mb-3" x-data="{ message: '' }">
                    <label for="catalog-photo-batch" class="form-label">Select multiple photos</label>
                    <input id="catalog-photo-batch" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif"
                           class="form-control" :disabled="uploads > 0 || syncFailed"
                           @change="await window.uploadCatalogPhotos($event.target, $wire, text => message = text)">
                    <small class="text-muted" role="status" x-text="message"></small>
                </div>

                @foreach($images as $idx => $img)
                    <div class="border rounded-3 p-3 mb-3" wire:key="product-image-{{ $idx }}-{{ $img['key'] ?? $img['id'] ?? 'initial' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                @if($idx === 0)
                                    <span class="badge bg-primary">Primary</span>
                                @else
                                    <span class="badge bg-light text-dark border">Image {{ $idx + 1 }}</span>
                                @endif
                            </div>

                            @if($idx > 0)
                                <button wire:click="removeImage({{ $idx }})"
                                        type="button"
                                        class="btn btn-sm btn-outline-danger" :disabled="uploads > 0">
                                    Remove
                                </button>
                            @endif
                        </div>

                        <label for="product-photo-{{ $idx }}" class="form-label small fw-semibold mb-1">
                            {{ !empty($img['id']) ? 'Replace image from computer' : 'Choose image from computer' }}
                        </label>

                        @if($productId && empty($imageUploads[$idx]))
                            <input type="file" id="product-photo-{{ $idx }}"
                                   class="form-control js-product-image-upload" :disabled="uploads > 0 || syncFailed"
                                   accept="image/jpeg,image/png,image/webp,image/gif"
                                   data-index="{{ $idx }}"
                                   data-existing-image-id="{{ $img['id'] ?? '' }}"
                                   data-upload-url="{{ route('admin.products.images.upload', $productId) }}">

                            <div class="small mt-2 js-product-upload-status text-muted" data-index="{{ $idx }}">
                                Select an image to upload directly to the database.
                            </div>

                            <div class="mt-2">
                                <small class="text-muted d-block mb-1">Current image:</small>
                                <img
                                    class="js-product-image-preview {{ empty($img['url']) ? 'd-none' : '' }}"
                                    src="{{ $img['url'] ?? '' }}"
                                    style="height:110px;width:110px;object-fit:cover;border-radius:0.5rem;border:1px solid #dee2e6"
                                    onerror="this.style.display='none'">
                            </div>
                        @else
                            <div x-data="{ uploading: false, failed: false }"
                                 x-on:livewire-upload-start="uploading = true; failed = false; uploads++"
                                 x-on:livewire-upload-finish="uploading = false; uploads--"
                                 x-on:livewire-upload-error="uploading = false; failed = true; uploads--"
                                 x-on:livewire-upload-cancel="uploading = false; uploads--">
                                <input type="file" id="product-photo-{{ $idx }}" class="form-control"
                                       accept="image/jpeg,image/png,image/webp,image/gif"
                                       wire:model="imageUploads.{{ $idx }}" :disabled="uploading || uploads > 0 || syncFailed">
                                <small x-show="uploading" x-cloak role="status">Uploading photo…</small>
                                <div x-show="failed" x-cloak role="alert" class="text-danger small">Upload failed. Please select the photo again.</div>
                                @error("imageUploads.$idx") <div role="alert" class="text-danger small">{{ $message }}</div> @enderror
                                @if(($imageUploads[$idx] ?? null) instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile && $imageUploads[$idx]->isPreviewable())
                                    <img src="{{ $imageUploads[$idx]->temporaryUrl() }}" alt="Selected product photo"
                                         class="rounded border mt-2" style="height:110px;width:110px;object-fit:cover">
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach

                <small class="text-muted">
                    Upload JPG, PNG, WebP or GIF directly from your computer. Source files up to 20MB are accepted
                    (up to 16 megapixels) and optimized before saving to the CapitalCart database. GIFs are saved as still images. The first image is the primary image.
                </small>
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
            <div x-show="syncFailed" x-cloak class="alert alert-warning">Your image was saved, but the form could not refresh. Reload this page before making further changes.</div>
            <button wire:click="save" type="button" class="btn btn-primary w-100 btn-lg" :disabled="uploads > 0 || syncFailed" wire:loading.attr="disabled">
                {{ $productId ? 'Save Changes' : 'Create Product' }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </div>
    </div>


    <script>
        (() => {
            if (window.__capitalCartDirectImageUpload) return;
            window.__capitalCartDirectImageUpload = true;

            window.uploadCatalogPhotos = async (input, component, showStatus) => {
                const files = Array.from(input.files);
                input.value = '';
                if (!files.length) return;
                window.dispatchEvent(new Event('catalog-upload-start'));
                let saved = 0;
                try {
                    if (files.some(file => file.size > 20 * 1024 * 1024)) {
                        showStatus('Each photo must be 20 MB or smaller.');
                        return;
                    }
                    const slots = await component.reservePhotoSlots(files.length);
                    if (slots.length !== files.length) {
                        showStatus('A product can have up to 51 photos.');
                        return;
                    }
                    for (const [i, file] of files.entries()) {
                        showStatus('Uploading photo ' + (i + 1) + ' of ' + files.length + '…');
                        await new Promise((resolve, reject) =>
                            component.$upload('imageUploads.' + slots[i], file, resolve, reject, () => {}, reject)
                        );
                        saved++;
                    }
                    showStatus(saved + ' photos ready. Save the product to keep them.');
                } catch {
                    showStatus(saved + ' photos ready. Select the remaining photos again.');
                } finally {
                    window.dispatchEvent(new Event('catalog-upload-finish'));
                }
            };

            const getCsrf = () =>
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const getComponent = (input) => {
                const root = input.closest('[wire\\:id]');
                const id = root?.getAttribute('wire:id');

                if (!id || !window.Livewire?.find) return null;

                return window.Livewire.find(id);
            };

            document.addEventListener('change', async (event) => {
                const input = event.target;

                if (!(input instanceof HTMLInputElement)) return;
                if (!input.classList.contains('js-product-image-upload')) return;

                const file = input.files?.[0];
                if (!file) return;

                const row = input.closest('.border.rounded-3.p-3.mb-3');
                const status = row?.querySelector('.js-product-upload-status');
                const preview = row?.querySelector('.js-product-image-preview');
                const uploadUrl = input.dataset.uploadUrl || '';
                const index = Number(input.dataset.index || 0);
                const existingImageId = input.dataset.existingImageId || '';

                if (!uploadUrl) {
                    const message = 'Upload URL is missing. Save the product first.';
                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-danger';
                        status.textContent = message;
                    }
                    return;
                }

                if (file.size > 20 * 1024 * 1024) {
                    const message = 'File is larger than 20MB.';
                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-danger';
                        status.textContent = message;
                    }
                    input.value = '';
                    return;
                }

                const localPreview = URL.createObjectURL(file);
                if (preview) {
                    preview.src = localPreview;
                    preview.style.display = '';
                    preview.classList.remove('d-none');
                }

                if (status) {
                    status.className = 'small mt-2 js-product-upload-status text-primary';
                    status.textContent = 'Uploading directly to database...';
                }

                input.disabled = true;
                window.dispatchEvent(new Event('catalog-upload-start'));

                const formData = new FormData();
                formData.append('image', file);
                formData.append('index', String(index));
                if (existingImageId) {
                    formData.append('existing_image_id', existingImageId);
                }

                let imageSaved = false;
                try {
                    const response = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': getCsrf(),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData,
                        credentials: 'same-origin'
                    });

                    const raw = await response.text();
                    let data = null;

                    try {
                        data = raw ? JSON.parse(raw) : {};
                    } catch (parseError) {
                        data = {};
                    }

                    if (!response.ok) {
                        const validationErrors = data?.errors
                            ? Object.values(data.errors).flat().join(' ')
                            : '';

                        throw new Error(
                            validationErrors ||
                            data?.message ||
                            'Upload failed with HTTP ' + response.status
                        );
                    }

                    imageSaved = true;
                    input.dataset.existingImageId = String(data.image_id || '');
                    input.value = '';

                    if (preview && data.image_url) {
                        preview.src = data.image_url;
                        preview.style.display = '';
                        preview.classList.remove('d-none');
                    }

                    const component = getComponent(input);

                    if (component && data.image_id && data.image_url) {
                        await component.call(
                            'registerDirectImageUpload',
                            index,
                            Number(data.image_id),
                            String(data.image_url)
                        );
                    } else {
                        window.dispatchEvent(new Event('catalog-upload-sync-failed'));
                    }

                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-success';
                        status.textContent = '✓ Image uploaded successfully to database.';
                    }
                } catch (error) {
                    if (imageSaved) window.dispatchEvent(new Event('catalog-upload-sync-failed'));

                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-danger';
                        status.textContent = 'Upload failed: ' + (error?.message || 'Unknown error');
                    }
                } finally {
                    input.disabled = false;
                    window.dispatchEvent(new Event('catalog-upload-finish'));
                    URL.revokeObjectURL(localPreview);
                }
            }, true);
        })();
    </script>

</div>
