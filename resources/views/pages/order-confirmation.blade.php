<x-layouts.app title="Order Confirmed — CapitalCart.pk">

    <div class="container py-5">
        <div class="text-center py-4 my-2">
            {{-- Success Icon --}}
            <div style="width:72px;height:72px;border-radius:50%;background:#DCFCE7;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem" class="text-success shadow-sm">
                <svg width="38" height="38" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>

            <h2 class="font-poppins fw-bold text-success mb-2">Order Placed Successfully!</h2>
            <p class="text-muted fs-6 mb-4">Shukriya! Your order has been placed and received. Our team will verify and dispatch it shortly.</p>

            {{-- Order Number Box --}}
            <div class="d-inline-block bg-white border rounded-3 px-5 py-3 mb-4 shadow-sm">
                <div class="text-muted small text-uppercase fw-600">Order ID</div>
                <div class="font-poppins fw-bold fs-3 text-primary letter-spacing-1">{{ $order->order_number }}</div>
            </div>

            {{-- Order Details Grid --}}
            <div class="row g-4 justify-content-center text-start mb-4">
                <div class="col-md-8 col-lg-7">
                    {{-- Customer & Privacy Protected Details --}}
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-poppins fw-bold text-uppercase small text-muted mb-0">Customer Information</h6>
                            <span class="badge bg-light text-muted border" style="font-size:0.75rem">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Privacy Masked
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-6 col-sm-3">
                                <div class="text-muted small">Customer</div>
                                <div class="fw-bold text-dark">{{ $order->masked_name }}</div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="text-muted small">Mobile</div>
                                <div class="fw-bold text-dark">{{ $order->masked_phone }}</div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="text-muted small">City</div>
                                <div class="fw-bold text-dark">{{ $order->shipping_city ?: 'Islamabad' }}</div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="text-muted small">Payment</div>
                                <div class="fw-bold text-success">{{ strtoupper($order->payment_method) }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Items Summary --}}
                    <div class="bg-white border rounded-3 p-4 shadow-sm">
                        <h6 class="font-poppins fw-bold text-uppercase small text-muted mb-3">Ordered Items</h6>

                        <div class="order-items-list mb-3">
                            @foreach($order->items as $item)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <div class="fw-600 text-dark">{{ $item->product_name }}</div>
                                        @if($item->variant_label)
                                            <div class="small text-muted">{{ $item->variant_label }}</div>
                                        @endif
                                        <div class="small text-muted">Quantity: {{ $item->quantity }} × Rs. {{ number_format($item->unit_price, 0) }}</div>
                                    </div>
                                    <div class="fw-bold text-dark">Rs. {{ number_format($item->line_total, 0) }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between text-muted small py-1">
                            <span>Subtotal</span>
                            <span class="text-dark fw-600">Rs. {{ number_format($order->subtotal, 0) }}</span>
                        </div>

                        @if($order->discount_amount > 0)
                            <div class="d-flex justify-content-between text-success small py-1">
                                <span>Discount</span>
                                <span class="fw-600">−Rs. {{ number_format($order->discount_amount, 0) }}</span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between text-muted small py-1">
                            <span>Delivery Charges</span>
                            <span class="text-dark fw-600">{{ $order->shipping_amount == 0 ? 'Free' : 'Rs. ' . number_format($order->shipping_amount, 0) }}</span>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="fw-bold fs-6">Grand Total</span>
                            <span class="fw-bold text-primary fs-5">Rs. {{ number_format($order->total, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $orderService = app(\App\Services\OrderService::class);
                $waUrl = $orderService->getWhatsAppUrlForOrder($order);
            @endphp

            {{-- WhatsApp Confirmation CTA --}}
            <div class="my-4">
                <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-lg px-5 py-3 shadow d-inline-flex align-items-center gap-2 fw-bold" style="background:#25D366;border-color:#25D366;font-size:1.15rem;border-radius:0.5rem">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="white"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.392-10.416c-4.418 0-8 3.582-8 8 0 1.579.46 3.05 1.258 4.29l-1.297 4.743 4.887-1.282c1.206.732 2.618 1.157 4.131 1.157 4.418 0 8-3.582 8-8s-3.582-8-8-8z"/></svg>
                    Send Order Details on WhatsApp
                </a>
                <div class="small text-muted mt-2">Apna calculated bill aur order verification WhatsApp par bhejne ke liye upar diye gaye button par click karein.</div>
            </div>

            @if($order->payment_method === 'whatsapp')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        window.open(@json($waUrl), '_blank');
                    }, 1200);
                });
            </script>
            @endif

            <div class="d-flex gap-3 justify-content-center mt-4">
                <a href="{{ route('home') }}" class="btn btn-primary px-5 py-2 fw-600">Continue Shopping</a>
                <a href="{{ route('shop') }}" class="btn btn-outline-secondary px-4 py-2">Browse More Products</a>
            </div>

            <div class="mt-4 text-muted small">
                🔒 For your privacy and customer security, sensitive contact details are masked on public confirmation receipts.
            </div>
        </div>
    </div>

</x-layouts.app>
