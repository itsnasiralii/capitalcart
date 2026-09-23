<div>
    @if($isSuccess)
        <div class="p-4 rounded-4 text-center border" style="background: rgba(34, 197, 94, 0.08); border-color: rgba(34, 197, 94, 0.3) !important;">
            <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.2); color: #22C55E;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h4 class="h5 fw-bold text-white mb-2">Message Transmitted</h4>
            <p class="text-white-50 small mb-3">{{ $successMessage }}</p>
            <button type="button" wire:click="$set('isSuccess', false)" class="btn btn-sm btn-outline-light px-3 py-1">
                Send Another Message
            </button>
        </div>
    @else
        <form wire:submit.prevent="submit" novalidate>
            {{-- Bot Honeypot --}}
            <div style="display:none;" aria-hidden="true">
                <input type="text" wire:model="honeypot" tabindex="-1" autocomplete="off">
            </div>

            @if($errors->has('rate_limit'))
                <div class="alert alert-danger py-2 small mb-3">
                    {{ $errors->first('rate_limit') }}
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-white-50 fw-600 mb-1">Your Full Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="name" class="form-control portfolio-input @error('name') is-invalid @enderror" placeholder="e.g. Tariq Mehmood">
                    @error('name') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small text-white-50 fw-600 mb-1">Your Email Address <span class="text-danger">*</span></label>
                    <input type="email" wire:model="email" class="form-control portfolio-input @error('email') is-invalid @enderror" placeholder="name@company.com">
                    @error('email') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label small text-white-50 fw-600 mb-1">Subject / Inquiry Type <span class="text-danger">*</span></label>
                    <input type="text" wire:model="subject" class="form-control portfolio-input @error('subject') is-invalid @enderror" placeholder="e.g. Enterprise Network Automation Project / Job Inquiry">
                    @error('subject') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label small text-white-50 fw-600 mb-1">Detailed Message <span class="text-danger">*</span></label>
                    <textarea wire:model="message" rows="4" class="form-control portfolio-input @error('message') is-invalid @enderror" placeholder="Describe your technical requirement, project scope, or opportunity..."></textarea>
                    @error('message') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-portfolio-primary w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit" class="d-inline-flex align-items-center gap-2">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                            Transmit Direct Message
                        </span>
                        <span wire:loading wire:target="submit" class="spinner-border spinner-border-sm" role="status"></span>
                        <span wire:loading wire:target="submit">Transmitting...</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
