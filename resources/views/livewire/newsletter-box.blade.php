<div>
    <form wire:submit.prevent="subscribe">
        {{-- Honeypot hidden trap --}}
        <div style="display:none" aria-hidden="true">
            <input type="text" wire:model="honeypot" tabindex="-1" autocomplete="off">
        </div>

        <div class="input-group mb-2">
            <input
                type="email"
                wire:model="email"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="Your email address"
                required
                aria-label="Your email address"
            >
            <button
                type="submit"
                class="btn fw-semibold"
                style="background:#F97316;color:#fff"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="subscribe">Subscribe</span>
                <span wire:loading wire:target="subscribe" class="spinner-border spinner-border-sm" role="status"></span>
            </button>
        </div>

        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror

        @if($statusMessage)
            <div class="small mt-2 p-2 rounded {{ $statusType === 'success' ? 'bg-success-subtle text-success' : ($statusType === 'info' ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger') }}">
                {{ $statusMessage }}
            </div>
        @endif
    </form>
</div>
