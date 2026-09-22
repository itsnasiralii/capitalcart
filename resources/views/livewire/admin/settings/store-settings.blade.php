<div>
    <div class="mb-4">
        <h2 class="font-poppins fw-bold mb-1">Store Settings</h2>
        <p class="text-muted small mb-0">Centralized settings for WhatsApp ordering, delivery rates, and public store toggles.</p>
    </div>

    <form wire:submit.prevent="saveSettings">
        <div class="row g-4">
            {{-- WhatsApp Configuration --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">📱</span>
                            <div>
                                <h5 class="font-poppins fw-bold mb-0">WhatsApp Business Number</h5>
                                <small class="text-muted">Direct order receiver and customer checkout inquiries</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Business WhatsApp Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">🇵🇰</span>
                                <input type="text" wire:model.live="whatsapp_number" class="form-control form-control-lg @error('whatsapp_number') is-invalid @enderror" placeholder="03002922584">
                            </div>
                            @error('whatsapp_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <small class="text-muted d-block mt-2">
                                Accepts standard formats (e.g. <code>03002922584</code> or <code>+923002922584</code>). Validated strictly for Pakistani cellular networks.
                            </small>
                        </div>

                        <div class="p-3 bg-light rounded-3 border">
                            <div class="small fw-semibold text-muted text-uppercase mb-1" style="letter-spacing:0.05em">Generated wa.me International Link:</div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="text-success fw-bold fs-6">https://wa.me/{{ $internationalFormat }}</code>
                            </div>
                            <small class="text-muted d-block mt-1">This dynamic number is used across all WhatsApp buttons, product pages, and checkout redirection.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shipping & Delivery Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">🚚</span>
                            <div>
                                <h5 class="font-poppins fw-bold mb-0">Delivery &amp; Shipping Charges</h5>
                                <small class="text-muted">Calculated on checkout and invoices</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Standard Delivery Fee (PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" wire:model="shipping_cost" class="form-control @error('shipping_cost') is-invalid @enderror" min="0">
                            </div>
                            @error('shipping_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Free Delivery Threshold (PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" wire:model="free_shipping_threshold" class="form-control @error('free_shipping_threshold') is-invalid @enderror" min="0">
                            </div>
                            <small class="text-muted">Orders with subtotal equal or greater receive free delivery.</small>
                            @error('free_shipping_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Storefront Interactive Toggles --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">🎯</span>
                            <div>
                                <h5 class="font-poppins fw-bold mb-0">Storefront Visual Toggles</h5>
                                <small class="text-muted">Enable or disable visitor proof &amp; counters</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check form-switch mb-4">
                            <input type="checkbox" wire:model="show_visitor_counter" class="form-check-input" id="counterSwitch">
                            <label class="form-check-label fw-semibold" for="counterSwitch">
                                Genuine Live Visitor Counter
                                <small class="text-muted d-block fw-normal">Shows actual 5-minute active sessions (e.g. "12 visitors currently shopping").</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input type="checkbox" wire:model="show_recent_orders" class="form-check-input" id="recentOrdersSwitch">
                            <label class="form-check-label fw-semibold" for="recentOrdersSwitch">
                                Privacy-Safe Recent Order Activity
                                <small class="text-muted d-block fw-normal">Displays real masked order proof (e.g. "N*** — 030*******4 recently placed an order").</small>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hero Slider Autoplay Interval (Seconds) <span class="text-danger">*</span></label>
                            <input type="number" wire:model="slider_autoplay_duration" class="form-control @error('slider_autoplay_duration') is-invalid @enderror" min="2" max="30">
                            <small class="text-muted">Time before the circular hero banner advances automatically.</small>
                            @error('slider_autoplay_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Unconfirmed WhatsApp Order Expiration (Hours) <span class="text-danger">*</span></label>
                            <input type="number" wire:model="order_expiration_hours" class="form-control @error('order_expiration_hours') is-invalid @enderror" min="1" max="168">
                            <small class="text-muted">Unconfirmed pending orders automatically expire and restore inventory after this time (default 24h).</small>
                            @error('order_expiration_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Store Contact Details --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">🏢</span>
                            <div>
                                <h5 class="font-poppins fw-bold mb-0">General Store Information</h5>
                                <small class="text-muted">Customer support contact info</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer Support Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="store_email" class="form-control @error('store_email') is-invalid @enderror">
                            @error('store_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Support Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" wire:model="store_phone" class="form-control @error('store_phone') is-invalid @enderror">
                            @error('store_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Store City / Base <span class="text-danger">*</span></label>
                            <input type="text" wire:model="store_city" class="form-control @error('store_city') is-invalid @enderror">
                            @error('store_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5" wire:loading.attr="disabled">
                <span wire:loading wire:target="saveSettings" class="spinner-border spinner-border-sm me-1"></span>
                Save Store Settings
            </button>
        </div>
    </form>
</div>
