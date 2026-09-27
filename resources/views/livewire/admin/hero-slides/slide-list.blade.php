<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="font-poppins fw-bold mb-1">Hero Slides</h2>
            <p class="text-muted small mb-0">Manage the circular rotating carousel on the homepage.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Slide
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead style="background:#F8FAFC">
                    <tr>
                        <th class="ps-3" style="width:80px">Circular Preview</th>
                        <th>Title &amp; Subtitle</th>
                        <th>CTA Button &amp; Link</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slides as $slide)
                    <tr>
                        <td class="ps-3">
                            <div style="width:54px;height:54px;border-radius:50%;overflow:hidden;border:2px solid #F97316;display:flex;align-items:center;justify-content:center;background:#0F172A">
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" style="width:100%;height:100%;object-fit:cover">
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $slide->title }}</div>
                            @if($slide->subtitle)
                                <small class="text-muted d-block text-truncate" style="max-width:280px">{{ $slide->subtitle }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $slide->button_text }}</span>
                            <small class="text-muted d-block mt-1">{{ $slide->link_url }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">#{{ $slide->sort_order }}</span>
                        </td>
                        <td>
                            <button wire:click="toggleStatus({{ $slide->id }})" class="btn btn-sm border-0 p-0">
                                @if($slide->is_active)
                                    <span class="badge bg-success-subtle text-success px-2 py-1">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1">Disabled</span>
                                @endif
                            </button>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <button wire:click="openEditModal({{ $slide->id }})" class="btn btn-outline-secondary" title="Edit">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button wire:click="deleteSlide({{ $slide->id }})" wire:confirm="Are you sure you want to delete this slide?" class="btn btn-outline-danger" title="Delete">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="fs-1 mb-2">🖼️</div>
                            No hero slides found. Add one to display in the circular slider!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($slides->hasPages())
            <div class="p-3 border-top">
                {{ $slides->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="slideModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form wire:submit.prevent="saveSlide"
                      x-data="{ uploading: false, progress: 0, uploadError: '' }"
                      x-on:open-slide-modal.window="uploading = false; uploadError = ''"
                      x-on:livewire-upload-start="uploading = true; progress = 0; uploadError = ''"
                      x-on:livewire-upload-finish="uploading = false"
                      x-on:livewire-upload-cancel="uploading = false"
                      x-on:livewire-upload-error="uploading = false; uploadError = 'Upload failed. Choose a JPG, PNG, WebP or GIF up to 5 MB and retry.'"
                      x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <div class="modal-header">
                        <h5 class="modal-title font-poppins fw-bold">{{ $isEditing ? 'Edit Hero Slide' : 'Add Hero Slide' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slide Title <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Discover Premium Fashion">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subtitle / Caption</label>
                            <input type="text" wire:model="subtitle" class="form-control @error('subtitle') is-invalid @enderror" placeholder="e.g. Up to 40% off seasonal trends">
                            @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Button Text <span class="text-danger">*</span></label>
                                <input type="text" wire:model="button_text" class="form-control @error('button_text') is-invalid @enderror" placeholder="Shop Now">
                                @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Target URL <span class="text-danger">*</span></label>
                                <input type="text" wire:model="link_url" class="form-control @error('link_url') is-invalid @enderror" placeholder="/shop">
                                @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input type="number" wire:model="sort_order" class="form-control @error('sort_order') is-invalid @enderror" min="0">
                                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-center pt-3">
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" wire:model="is_active" class="form-check-input" id="slideActiveSwitch">
                                    <label class="form-check-label fw-semibold" for="slideActiveSwitch">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slide Image (Square recommended) <span class="text-danger">*</span></label>
                            <input type="file" wire:model="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,image/gif">
                            <small class="text-muted">JPG/PNG/WebP/GIF (still image), up to 5 MB / 16 megapixels. Cropped circularly on the home page.</small>
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <div x-show="uploading" x-cloak class="text-primary small mt-1" role="status">
                                Uploading: <span x-text="progress"></span>%
                            </div>
                            <div x-show="uploadError" x-cloak x-text="uploadError" class="text-danger small mt-2" role="alert"></div>

                            @php
                                $tempUrl = null;
                                if ($image) {
                                    try {
                                        $tempUrl = $image->temporaryUrl();
                                    } catch (\Throwable $e) {
                                        $tempUrl = null;
                                    }
                                }
                            @endphp

                            @if ($tempUrl)
                                <div class="mt-2 text-center">
                                    <small class="text-muted d-block mb-1">Circular preview:</small>
                                    <div style="width:110px;height:110px;border-radius:50%;overflow:hidden;border:3px solid #F97316;margin:0 auto">
                                        <img src="{{ $tempUrl }}" style="width:100%;height:100%;object-fit:cover">
                                    </div>
                                </div>
                            @elseif ($image)
                                <div class="mt-2 text-center">
                                    <small class="text-success d-block mb-1">✓ File ready for upload: {{ is_string($image) ? $image : $image->getClientOriginalName() }}</small>
                                </div>
                            @elseif ($existingImagePath)
                                <div class="mt-2 text-center">
                                    <small class="text-muted d-block mb-1">Current image preview:</small>
                                    <div style="width:110px;height:110px;border-radius:50%;overflow:hidden;border:3px solid #F97316;margin:0 auto">
                                        <img src="{{ str_starts_with($existingImagePath, 'http') ? $existingImagePath : asset('storage/' . $existingImagePath) }}" style="width:100%;height:100%;object-fit:cover">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetForm">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" :disabled="uploading || uploadError !== ''">
                            <span wire:loading wire:target="saveSlide" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $isEditing ? 'Save Changes' : 'Add Slide' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('slideModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                window.addEventListener('open-slide-modal', () => modal.show());
                window.addEventListener('close-slide-modal', () => modal.hide());
            }
        });
    </script>
</div>
