<div class="py-2" id="checkout-form-root">
    <div class="row g-4">
        {{-- Checkout Form --}}
        <div class="col-lg-8">
            <div class="bg-white rounded-3 border p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="font-poppins fw-bold mb-1">Customer & Delivery Information</h4>
                        <p class="text-muted small mb-0">Fast guest checkout. No account or password required.</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Privacy Protected
                    </span>
                </div>

                @if($errors->has('order_error'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>{{ $errors->first('order_error') }}</div>
                    </div>
                @endif

                <div class="row g-3">
                    {{-- Full Name --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="billing_name" aria-label="Full Name" wire:model.blur="billing_name" class="form-control @error('billing_name') is-invalid @enderror" placeholder="e.g. Nasir Ali">
                        @error('billing_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Pakistani Mobile Number --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Mobile Number (WhatsApp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light text-muted fw-600">🇵🇰</span>
                            <input type="tel" autocomplete="tel" id="billing_phone" aria-label="Mobile Number" wire:model.blur="billing_phone" class="form-control @error('billing_phone') is-invalid @enderror" placeholder="0300 1234567">
                            @error('billing_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem">Formats accepted: 03001234567 or +923001234567</div>
                    </div>

                    {{-- City --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">City <span class="text-muted small fw-normal">(Optional)</span></label>
                        <input type="text" id="billing_city" aria-label="City" wire:model="billing_city" class="form-control @error('billing_city') is-invalid @enderror" placeholder="Islamabad">
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Email Address
                            <span class="text-muted small fw-normal">(Optional)</span>
                        </label>
                        <input type="email" autocomplete="email" id="billing_email" aria-label="Email Address" wire:model="billing_email" class="form-control @error('billing_email') is-invalid @enderror" placeholder="customer@example.com">
                        @error('billing_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Street / Delivery Address --}}
                    <div class="col-12">
                        <label class="form-label fw-600">Delivery Address / Landmark <span class="text-muted small fw-normal">(Optional for WhatsApp orders)</span></label>
                        <input type="text" id="billing_address" aria-label="Delivery Address" wire:model="billing_address" class="form-control @error('billing_address') is-invalid @enderror" placeholder="House / Flat / Street address or nearest landmark">
                    </div>

                    {{-- Customer Notes --}}
                    <div class="col-12">
                        <label class="form-label fw-600">Special Instructions / Order Notes <span class="text-muted small fw-normal">(Optional)</span></label>
                        <textarea id="customer_notes" aria-label="Order notes" wire:model="customer_notes" rows="2" class="form-control" placeholder="Any special requests or delivery instructions..."></textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <h5 class="font-poppins fw-bold mb-2">Direct Order on WhatsApp</h5>
                    <p class="mb-1 fw-semibold">Cash on Delivery (COD)</p>
                    <p class="text-muted small mb-0">Send your order to us on WhatsApp to confirm availability and delivery. Pay in cash when your parcel arrives.</p>
                </div>

                {{-- Action Button --}}
                <div class="mt-4 pt-3 border-top">
                    <button type="button" id="btn-place-order" wire:click="placeOrder"
                        wire:loading.attr="disabled" wire:target="placeOrder"
                        class="btn btn-success btn-lg w-100 py-3 fw-bold">
                        <span wire:loading.remove wire:target="placeOrder">Continue to WhatsApp — Rs. {{ number_format($total, 0) }}</span>
                        <span wire:loading wire:target="placeOrder">Preparing your order…</span>
                    </button>
                    <p class="text-muted small mt-2">Your request is saved first. On the next page, tap Send Order Details on WhatsApp and send the message to confirm it with us.</p>
                    <p class="text-center text-muted small mt-2 mb-0">Review our <a href="{{ route('ordering') }}">ordering and payment terms</a> and <a href="{{ route('privacy') }}">privacy information</a>.</p>
                </div>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="col-lg-4">
            <div class="bg-white rounded-3 border p-4 shadow-sm sticky-top" style="top: 2rem">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-poppins fw-bold mb-0">Order Summary</h5>
                    <span class="badge bg-primary rounded-pill">{{ count($items) }} {{ Str::plural('item', count($items)) }}</span>
                </div>

                <div class="order-items-list mb-3" style="max-height: 280px; overflow-y: auto;">
                    @foreach($items as $item)
                        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" style="width:52px;height:52px;object-fit:cover;border-radius:0.375rem" class="border">
                            <div class="flex-grow-1 min-w-0">
                                <div class="small fw-600 text-truncate">{{ $item['name'] }}</div>
                                @if($item['variant_label'])
                                    <div class="small text-muted">{{ $item['variant_label'] }}</div>
                                @endif
                                <div class="small text-muted">Qty: {{ $item['quantity'] }}</div>
                            </div>
                            <div class="small fw-bold text-nowrap">Rs. {{ number_format($item['price'] * $item['quantity'], 0) }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between text-muted small py-1">
                    <span>Subtotal</span>
                    <span class="text-dark fw-600">Rs. {{ number_format($subtotal, 0) }}</span>
                </div>

                @if($discount > 0)
                    <div class="d-flex justify-content-between text-success small py-1">
                        <span>Coupon Discount</span>
                        <span class="fw-600">−Rs. {{ number_format($discount, 0) }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between text-muted small py-1">
                    <span>Delivery Charges</span>
                    <span class="text-dark fw-600">{{ $shipping == 0 ? 'Free' : 'Rs. ' . number_format($shipping, 0) }}</span>
                </div>

                @if($tax > 0)
                    <div class="d-flex justify-content-between text-muted small py-1">
                        <span>Tax</span>
                        <span class="text-dark fw-600">Rs. {{ number_format($tax, 0) }}</span>
                    </div>
                @endif

                <hr class="my-2">

                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold fs-6">Grand Total</span>
                    <span class="fw-bold text-primary fs-5">Rs. {{ number_format($total, 0) }}</span>
                </div>

                <div class="mt-4 p-3 rounded-3 bg-light border">
                    <div class="d-flex align-items-center gap-2 mb-2 text-success small fw-600">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Customer Guarantee
                    </div>
                    <ul class="list-unstyled mb-0 small text-muted" style="font-size:0.8rem">
                        <li class="mb-1">✓ 100% Genuine Products Guaranteed</li>
                        <li class="mb-1">✓ Same-day dispatch in Islamabad & Rawalpindi</li>
                        <li>✓ Live WhatsApp support for order tracking</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
