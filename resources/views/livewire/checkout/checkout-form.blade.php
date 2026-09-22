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
                        <input type="text" wire:model.blur="billing_name" class="form-control @error('billing_name') is-invalid @enderror" placeholder="e.g. Nasir Ali">
                        @error('billing_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Pakistani Mobile Number --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Mobile Number (WhatsApp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light text-muted fw-600">🇵🇰</span>
                            <input type="tel" wire:model.blur="billing_phone" class="form-control @error('billing_phone') is-invalid @enderror" placeholder="0300 1234567">
                            @error('billing_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem">Formats accepted: 03001234567 or +923001234567</div>
                    </div>

                    {{-- City --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">City <span class="text-muted small fw-normal">(Optional)</span></label>
                        <input type="text" wire:model="billing_city" class="form-control" placeholder="Islamabad">
                    </div>

                    {{-- Email (Optional unless stripe) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Email Address 
                            @if($payment_method === 'stripe')
                                <span class="text-danger">*</span>
                            @else
                                <span class="text-muted small fw-normal">(Optional)</span>
                            @endif
                        </label>
                        <input type="email" wire:model="billing_email" class="form-control @error('billing_email') is-invalid @enderror" placeholder="customer@example.com">
                        @error('billing_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Street / Delivery Address --}}
                    <div class="col-12">
                        <label class="form-label fw-600">Delivery Address / Landmark <span class="text-muted small fw-normal">(Optional for WhatsApp orders)</span></label>
                        <input type="text" wire:model="billing_address" class="form-control" placeholder="House / Flat / Street address or nearest landmark">
                    </div>

                    {{-- Customer Notes --}}
                    <div class="col-12">
                        <label class="form-label fw-600">Special Instructions / Order Notes <span class="text-muted small fw-normal">(Optional)</span></label>
                        <textarea wire:model="customer_notes" rows="2" class="form-control" placeholder="Any special requests or delivery instructions..."></textarea>
                    </div>
                </div>

                {{-- Payment Method Selection --}}
                <div class="mt-4 pt-3 border-top">
                    <h5 class="font-poppins fw-bold mb-3">Select Payment Method</h5>

                    <div class="d-flex flex-column gap-3">
                        {{-- WhatsApp Order (Default & Recommended) --}}
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 transition-all {{ $payment_method === 'whatsapp' ? 'border-success bg-light shadow-sm' : 'bg-white' }}" style="cursor:pointer">
                            <input type="radio" wire:model.live="payment_method" value="whatsapp" class="form-check-input mt-0">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    Direct Order on WhatsApp
                                    <span class="badge bg-success" style="font-size:0.75rem">Recommended</span>
                                </div>
                                <small class="text-muted">Instant WhatsApp checkout with complete itemized bill and direct confirmation.</small>
                            </div>
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="#25D366"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.392-10.416c-4.418 0-8 3.582-8 8 0 1.579.46 3.05 1.258 4.29l-1.297 4.743 4.887-1.282c1.206.732 2.618 1.157 4.131 1.157 4.418 0 8-3.582 8-8s-3.582-8-8-8z"/></svg>
                        </label>

                        {{-- Cash on Delivery --}}
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 transition-all {{ $payment_method === 'cod' ? 'border-primary bg-light shadow-sm' : 'bg-white' }}" style="cursor:pointer">
                            <input type="radio" wire:model.live="payment_method" value="cod" class="form-check-input mt-0">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark">Cash on Delivery (COD)</div>
                                <small class="text-muted">Pay in cash when your parcel is delivered at your doorstep.</small>
                            </div>
                            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" class="text-muted"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </label>

                        {{-- Bank Transfer --}}
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 transition-all {{ $payment_method === 'bank_transfer' ? 'border-primary bg-light shadow-sm' : 'bg-white' }}" style="cursor:pointer">
                            <input type="radio" wire:model.live="payment_method" value="bank_transfer" class="form-check-input mt-0">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark">Direct Bank Transfer</div>
                                <small class="text-muted">Pay directly to our official bank account and share the receipt.</small>
                            </div>
                            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" class="text-muted"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </label>

                        {{-- Stripe --}}
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 transition-all {{ $payment_method === 'stripe' ? 'border-primary bg-light shadow-sm' : 'bg-white' }}" style="cursor:pointer">
                            <input type="radio" wire:model.live="payment_method" value="stripe" class="form-check-input mt-0">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    Credit / Debit Card
                                    <span class="badge bg-secondary" style="font-size:0.7rem">Stripe</span>
                                </div>
                                <small class="text-muted">Visa, Mastercard, American Express — 256-bit encrypted</small>
                            </div>
                            <svg width="32" height="20" viewBox="0 0 60 25" fill="none"><rect width="60" height="25" rx="4" fill="#635BFF"/><text x="30" y="17" font-size="10" fill="white" text-anchor="middle" font-family="sans-serif" font-weight="bold">stripe</text></svg>
                        </label>

                        @if($payment_method === 'stripe')
                            <div wire:ignore id="stripe-card-wrapper-final" class="border rounded-3 p-3 bg-white" style="border-color:#635BFF!important">
                                <label class="form-label fw-600 mb-2">Card Details</label>
                                <div id="stripe-payment-element" style="min-height:44px"></div>
                                <div id="stripe-payment-errors" class="text-danger small mt-2" role="alert"></div>
                                <div class="mt-2 p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0">
                                    <small class="text-success fw-600">🧪 Test Card: <code>4242 4242 4242 4242</code> · Any future date · Any CVC</small>
                                </div>
                            </div>
                            <div id="stripe-processing" class="text-center py-2 d-none">
                                <div class="spinner-border text-primary spinner-border-sm me-2"></div>
                                <span class="text-muted">Verifying card payment…</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="mt-4 pt-3 border-top">
                    <button
                        type="button"
                        id="btn-place-order"
                        wire:click="{{ $payment_method === 'stripe' ? '' : 'placeOrder' }}"
                        @if($payment_method === 'stripe') onclick="stripeSubmit(event)" @endif
                        wire:loading.attr="disabled"
                        wire:target="placeOrder"
                        class="btn {{ $payment_method === 'whatsapp' ? 'btn-success' : 'btn-primary' }} btn-lg w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm"
                        style="font-size: 1.1rem; {{ $payment_method === 'whatsapp' ? 'background:#25D366;border-color:#25D366;' : '' }}"
                    >
                        <span wire:loading.remove wire:target="placeOrder">
                            @if($payment_method === 'whatsapp')
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="white" class="me-1"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.392-10.416c-4.418 0-8 3.582-8 8 0 1.579.46 3.05 1.258 4.29l-1.297 4.743 4.887-1.282c1.206.732 2.618 1.157 4.131 1.157 4.418 0 8-3.582 8-8s-3.582-8-8-8z"/></svg>
                                Confirm & Order via WhatsApp — Rs. {{ number_format($total, 0) }}
                            @elseif($payment_method === 'cod')
                                Place Cash on Delivery Order — Rs. {{ number_format($total, 0) }}
                            @elseif($payment_method === 'bank_transfer')
                                Place Order via Bank Transfer — Rs. {{ number_format($total, 0) }}
                            @else
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Pay Now — Rs. {{ number_format($total, 0) }}
                            @endif
                        </span>
                        <span wire:loading wire:target="placeOrder">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Creating your order securely…
                        </span>
                    </button>
                    <p class="text-center text-muted small mt-2 mb-0">By clicking above, you agree to our terms of service and order policies.</p>
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

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const STRIPE_KEY = '{{ config('services.stripe.key') }}';
    let stripe       = null;
    let cardElement  = null;
    let clientSecret = null;
    let domObserver  = null;

    function initStripe() {
        if (!STRIPE_KEY || STRIPE_KEY.startsWith('sk_') || STRIPE_KEY.includes('00000')) {
            return;
        }
        try {
            stripe = Stripe(STRIPE_KEY);
            startDomObserver();
        } catch (e) {
            console.error('[Stripe] Init failed:', e.message);
        }
    }

    function startDomObserver() {
        if (domObserver) return;
        domObserver = new MutationObserver(function () {
            tryMountCard();
        });
        domObserver.observe(document.body, { childList: true, subtree: true });
        tryMountCard();
    }

    function tryMountCard() {
        const container = document.getElementById('stripe-payment-element');
        if (!stripe || !container) return;

        if (container.querySelector('iframe')) return;

        if (cardElement) {
            try { cardElement.unmount(); } catch (_) {}
            cardElement = null;
        }

        const elements = stripe.elements();
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#0F172A',
                    fontFamily: 'Inter, sans-serif',
                    '::placeholder': { color: '#9CA3AF' },
                },
                invalid: { color: '#EF4444' },
            },
            hidePostalCode: true,
        });
        cardElement.mount(container);
        cardElement.on('change', function (e) {
            const err = document.getElementById('stripe-payment-errors');
            if (err) err.textContent = e.error ? e.error.message : '';
        });
    }

    function getCheckoutComponent() {
        const root = document.getElementById('checkout-form-root');
        const id   = root?.getAttribute('wire:id');
        if (id) return Livewire.find(id);
        for (const c of Livewire.all()) {
            if (c.name?.includes('checkout')) return c;
        }
        return null;
    }

    async function confirmPayment() {
        if (!stripe || !clientSecret || !cardElement) {
            return;
        }

        const processing = document.getElementById('stripe-processing');
        const btn        = document.getElementById('btn-place-order');
        const errEl      = document.getElementById('stripe-payment-errors');

        if (processing) processing.classList.remove('d-none');
        if (btn)        btn.disabled = true;

        const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: { card: cardElement },
        });

        if (processing) processing.classList.add('d-none');
        if (btn)        btn.disabled = false;

        if (error) {
            if (errEl) errEl.textContent = error.message;
            return;
        }

        if (paymentIntent?.status === 'succeeded') {
            getCheckoutComponent()?.call('finalizeStripeOrder', paymentIntent.id);
        }
    }

    document.addEventListener('livewire:init', function () {
        initStripe();

        Livewire.on('stripe-ready', function (data) {
            const payload = Array.isArray(data) ? data[0] : data;
            clientSecret  = payload?.clientSecret ?? null;
            if (clientSecret) confirmPayment();
        });
    });

    window.stripeSubmit = function (e) {
        e.preventDefault();

        if (!stripe) {
            alert('Stripe is not configured in .env.');
            return;
        }

        tryMountCard();

        if (!cardElement) {
            alert('Card input not loaded. Please wait a moment.');
            return;
        }

        getCheckoutComponent()?.call('createPaymentIntent');
    };
})();
</script>
@endpush
