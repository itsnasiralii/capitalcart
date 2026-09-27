<div>
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
                    <button wire:click="addImage" type="button" class="btn btn-sm btn-outline-primary">+ Add Image</button>
                </div>

                @foreach($images as $idx => $img)
                    <div class="border rounded-3 p-3 mb-3" wire:key="product-image-{{ $idx }}">
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
                                        class="btn btn-sm btn-outline-danger">
                                    Remove
                                </button>
                            @endif
                        </div>

                        <label class="form-label small fw-semibold mb-1">
                            {{ !empty($img['id']) ? 'Replace image from computer' : 'Choose image from computer' }}
                        </label>

                        <input type="file"
                               class="form-control js-product-image-upload"
                               accept="image/jpeg,image/png,image/webp,image/gif"
                               data-index="{{ $idx }}"
                               data-existing-image-id="{{ $img['id'] ?? '' }}"
                               data-upload-url="{{ $productId ? route('admin.products.images.upload', $productId) : '' }}"
                               {{ $productId ? '' : 'disabled' }}>

                        <div class="small mt-2 js-product-upload-status text-muted" data-index="{{ $idx }}">
                            @if($productId)
                                Select an image to upload directly to the database.
                            @else
                                Create the product first, then add images.
                            @endif
                        </div>

                        <div class="mt-2">
                            <small class="text-muted d-block mb-1">Current image:</small>
                            <img
                                class="js-product-image-preview {{ empty($img['url']) ? 'd-none' : '' }}"
                                src="{{ $img['url'] ?? '' }}"
                                style="height:110px;width:110px;object-fit:cover;border-radius:0.5rem;border:1px solid #dee2e6"
                                onerror="this.style.display='none'">
                        </div>
                    </div>
                @endforeach

                <small class="text-muted">
                    Upload JPG, PNG, WebP or GIF directly from your computer. Source files up to 20MB are accepted
                    and large images are optimized before saving to the CapitalCart database. The first image is the primary image.
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

            <button wire:click="save" type="button" class="btn btn-primary w-100 btn-lg">
                {{ $productId ? 'Save Changes' : 'Create Product' }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </div>
    </div>


    <script>
        (() => {
            if (window.__capitalCartDirectImageUpload) return;
            window.__capitalCartDirectImageUpload = true;

            const PREFIX = '[CapitalCart Direct Upload]';

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

                console.group(PREFIX + ' START');
                console.info('file', {
                    name: file.name,
                    size_bytes: file.size,
                    size_mb: Number((file.size / 1024 / 1024).toFixed(2)),
                    type: file.type,
                    index,
                    existing_image_id: existingImageId || null,
                    upload_url: uploadUrl
                });

                if (!uploadUrl) {
                    const message = 'Upload URL is missing. Save the product first.';
                    console.error(PREFIX, message);
                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-danger';
                        status.textContent = message;
                    }
                    console.groupEnd();
                    return;
                }

                if (file.size > 20 * 1024 * 1024) {
                    const message = 'File is larger than 20MB.';
                    console.error(PREFIX, message);
                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-danger';
                        status.textContent = message;
                    }
                    input.value = '';
                    console.groupEnd();
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

                const formData = new FormData();
                formData.append('image', file);
                formData.append('index', String(index));
                if (existingImageId) {
                    formData.append('existing_image_id', existingImageId);
                }

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
                        data = { raw_response: raw };
                    }

                    console.info(PREFIX, 'HTTP RESPONSE', {
                        status: response.status,
                        statusText: response.statusText,
                        ok: response.ok,
                        data
                    });

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
                        console.warn(PREFIX, 'Livewire state sync skipped', {
                            component_found: Boolean(component),
                            image_id: data.image_id,
                            image_url: data.image_url
                        });
                    }

                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-success';
                        status.textContent = '✓ Image uploaded successfully to database.';
                    }

                    console.info(PREFIX, 'SUCCESS', data);
                } catch (error) {
                    console.error(PREFIX, 'FAILED', {
                        name: error?.name,
                        message: error?.message,
                        stack: error?.stack
                    });

                    if (status) {
                        status.className = 'small mt-2 js-product-upload-status text-danger';
                        status.textContent = 'Upload failed: ' + (error?.message || 'Unknown error');
                    }
                } finally {
                    input.disabled = false;
                    URL.revokeObjectURL(localPreview);
                    console.groupEnd();
                }
            }, true);

            console.info(PREFIX, 'Direct database uploader enabled. Livewire temporary file upload is bypassed.');
        })();
    </script>

    <script>
        (() => {
            if (window.__capitalCartUploadConsoleDebug) return;
            window.__capitalCartUploadConsoleDebug = true;

            const PREFIX = '[CapitalCart Upload Debug]';

            const cleanUrl = (value) => {
                try {
                    const url = new URL(value, window.location.origin);
                    return url.origin + url.pathname;
                } catch (e) {
                    return String(value || '').split('?')[0];
                }
            };

            const isLivewireRequest = (value) => {
                const url = cleanUrl(value);
                return url.includes('/livewire');
            };

            console.info(PREFIX, 'Browser upload diagnostics ENABLED', {
                page: window.location.href,
                time: new Date().toISOString()
            });

            // Show the exact file selected in DevTools Console.
            document.addEventListener('change', (event) => {
                const input = event.target;

                if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;

                const model = input.getAttribute('wire:model') || '';
                if (!model.startsWith('imageUploads.')) return;

                const files = Array.from(input.files || []).map((file) => ({
                    name: file.name,
                    size_bytes: file.size,
                    size_mb: Number((file.size / 1024 / 1024).toFixed(2)),
                    mime: file.type || '(browser did not report MIME)',
                    last_modified: new Date(file.lastModified).toISOString()
                }));

                console.group(PREFIX + ' FILE SELECTED');
                console.info('wire:model:', model);
                console.table(files);
                console.info('input validity:', {
                    valid: input.validity.valid,
                    validationMessage: input.validationMessage
                });
                console.groupEnd();
            }, true);

            // Livewire emits these browser events directly on file inputs.
            [
                'livewire-upload-start',
                'livewire-upload-progress',
                'livewire-upload-finish',
                'livewire-upload-error',
                'livewire-upload-cancel'
            ].forEach((eventName) => {
                document.addEventListener(eventName, (event) => {
                    const payload = {
                        event: eventName,
                        detail: event.detail || {},
                        model: event.target?.getAttribute?.('wire:model') || null,
                        time: new Date().toISOString()
                    };

                    if (eventName === 'livewire-upload-error') {
                        console.error(PREFIX, 'LIVEWIRE UPLOAD ERROR', payload);
                    } else if (eventName === 'livewire-upload-cancel') {
                        console.warn(PREFIX, 'LIVEWIRE UPLOAD CANCELLED', payload);
                    } else {
                        console.info(PREFIX, eventName, payload);
                    }
                }, true);
            });

            // Capture Livewire XHR requests, if the current Livewire build uses XHR.
            const XHR = window.XMLHttpRequest;
            if (XHR && !XHR.prototype.__capitalCartUploadPatched) {
                const nativeOpen = XHR.prototype.open;
                const nativeSend = XHR.prototype.send;

                XHR.prototype.open = function(method, url, ...rest) {
                    this.__ccMethod = method;
                    this.__ccUrl = url;
                    return nativeOpen.call(this, method, url, ...rest);
                };

                XHR.prototype.send = function(body) {
                    if (isLivewireRequest(this.__ccUrl)) {
                        const requestInfo = {
                            transport: 'XHR',
                            method: this.__ccMethod,
                            url: cleanUrl(this.__ccUrl),
                            body_type: body?.constructor?.name || typeof body,
                            time: new Date().toISOString()
                        };

                        console.info(PREFIX, 'REQUEST START', requestInfo);

                        this.addEventListener('loadend', () => {
                            const result = {
                                ...requestInfo,
                                status: this.status,
                                status_text: this.statusText,
                                response_url: cleanUrl(this.responseURL || this.__ccUrl)
                            };

                            if (this.status >= 400 || this.status === 0) {
                                let preview = '';
                                try {
                                    preview = typeof this.responseText === 'string'
                                        ? this.responseText.slice(0, 4000)
                                        : '[responseText unavailable]';
                                } catch (e) {
                                    preview = '[responseText blocked: ' + e.message + ']';
                                }

                                console.error(PREFIX, 'REQUEST FAILED', result);
                                console.error(PREFIX, 'SERVER RESPONSE PREVIEW:', preview);
                            } else {
                                console.info(PREFIX, 'REQUEST OK', result);
                            }
                        });

                        this.addEventListener('error', (event) => {
                            console.error(PREFIX, 'XHR NETWORK ERROR', {
                                ...requestInfo,
                                status: this.status,
                                event
                            });
                        });

                        this.addEventListener('timeout', () => {
                            console.error(PREFIX, 'XHR TIMEOUT', requestInfo);
                        });

                        this.addEventListener('abort', () => {
                            console.warn(PREFIX, 'XHR ABORTED', requestInfo);
                        });
                    }

                    return nativeSend.call(this, body);
                };

                XHR.prototype.__capitalCartUploadPatched = true;
            }

            // Capture Livewire fetch requests (used by newer Livewire versions).
            if (window.fetch && !window.fetch.__capitalCartUploadPatched) {
                const nativeFetch = window.fetch.bind(window);

                const debugFetch = async (...args) => {
                    const input = args[0];
                    const options = args[1] || {};
                    const rawUrl = typeof input === 'string' ? input : input?.url;
                    const method = options.method || input?.method || 'GET';

                    if (!isLivewireRequest(rawUrl)) {
                        return nativeFetch(...args);
                    }

                    const requestInfo = {
                        transport: 'fetch',
                        method,
                        url: cleanUrl(rawUrl),
                        body_type: options.body?.constructor?.name || input?.body?.constructor?.name || null,
                        time: new Date().toISOString()
                    };

                    console.info(PREFIX, 'REQUEST START', requestInfo);

                    try {
                        const response = await nativeFetch(...args);

                        const result = {
                            ...requestInfo,
                            status: response.status,
                            status_text: response.statusText,
                            response_url: cleanUrl(response.url || rawUrl),
                            redirected: response.redirected
                        };

                        if (!response.ok) {
                            let preview = '';
                            try {
                                preview = (await response.clone().text()).slice(0, 4000);
                            } catch (e) {
                                preview = '[response body unavailable: ' + e.message + ']';
                            }

                            console.error(PREFIX, 'REQUEST FAILED', result);
                            console.error(PREFIX, 'SERVER RESPONSE PREVIEW:', preview);
                        } else {
                            console.info(PREFIX, 'REQUEST OK', result);
                        }

                        return response;
                    } catch (error) {
                        console.error(PREFIX, 'FETCH NETWORK/JS ERROR', {
                            ...requestInfo,
                            name: error?.name,
                            message: error?.message,
                            stack: error?.stack
                        });
                        throw error;
                    }
                };

                debugFetch.__capitalCartUploadPatched = true;
                window.fetch = debugFetch;
            }

            // Catch any browser-side JavaScript error that happens during upload.
            window.addEventListener('error', (event) => {
                const message = String(event.message || '');
                const filename = String(event.filename || '');

                if (
                    message.toLowerCase().includes('upload') ||
                    message.toLowerCase().includes('livewire') ||
                    filename.toLowerCase().includes('livewire')
                ) {
                    console.error(PREFIX, 'GLOBAL JS ERROR', {
                        message: event.message,
                        filename: event.filename,
                        line: event.lineno,
                        column: event.colno,
                        error: event.error
                    });
                }
            });

            window.addEventListener('unhandledrejection', (event) => {
                const reason = event.reason;
                const text = String(reason?.message || reason || '');

                if (text.toLowerCase().includes('upload') || text.toLowerCase().includes('livewire')) {
                    console.error(PREFIX, 'UNHANDLED PROMISE REJECTION', reason);
                }
            });
        })();
    </script>

</div>
