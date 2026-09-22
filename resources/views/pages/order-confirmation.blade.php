<x-layouts.app title="Order Confirmed">

    <div class="container py-5">
        <div class="text-center py-5 my-3">
            <div style="width:80px;height:80px;border-radius:50%;background:#DCFCE7;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem" class="fs-1">
                ✓
            </div>
            <h1 class="font-poppins fw-bold text-success mb-2">Order Placed Successfully!</h1>
            <p class="text-muted fs-5 mb-4">Thank you for your purchase. Your order has been received and is being processed.</p>

            <div class="d-inline-block bg-white border rounded-3 px-5 py-3 mb-4">
                <div class="text-muted small">Order Number</div>
                <div class="font-poppins fw-bold fs-4 text-primary">{{ $order->order_number }}</div>
            </div>

            <div class="row g-3 justify-content-center mb-5">
                <div class="col-md-8">
                    <div class="bg-white border rounded-3 p-4">
                        <h5 class="font-poppins fw-bold mb-4">Order Summary</h5>
                        @foreach($order->items as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">{{ $item->product_name }}
                                    @if($item->variant_label) <span class="text-muted">({{ $item->variant_label }})</span> @endif
                                    ×{{ $item->quantity }}
                                </span>
                                <span class="small fw-600">Rs. {{ number_format($item->line_total, 0) }}</span>
                            </div>
                        @endforeach
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total Amount</span>
                            <span class="fw-bold text-primary fs-5">Rs. {{ number_format($order->total, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $whatsappNumber = env('WHATSAPP_NUMBER', '923453904084');
                $message = "🛒 *Assalam-o-Alaikum! New Order from CapitalCart.pk*\n";
                $message .= "━━━━━━━━━━━━━━━━━━\n";
                $message .= "📦 *Order Number:* " . $order->order_number . "\n";
                $message .= "👤 *Customer Name:* " . $order->billing_name . "\n";
                $message .= "📞 *Phone:* " . $order->billing_phone . "\n";
                $message .= "📍 *Address:* " . $order->shipping_address . ", " . $order->shipping_city . "\n";
                $message .= "💳 *Payment Method:* " . strtoupper($order->payment_method) . "\n";
                if ($order->customer_notes) {
                    $message .= "📝 *Notes:* " . $order->customer_notes . "\n";
                }
                $message .= "━━━━━━━━━━━━━━━━━━\n";
                $message .= "📋 *Items:*\n";
                foreach($order->items as $idx => $item) {
                    $message .= ($idx + 1) . ". " . $item->product_name . ($item->variant_label ? " (" . $item->variant_label . ")" : "") . " x" . $item->quantity . " = Rs. " . number_format($item->line_total, 0) . "\n";
                }
                $message .= "━━━━━━━━━━━━━━━━━━\n";
                $message .= "💵 *Subtotal:* Rs. " . number_format($order->subtotal, 0) . "\n";
                if ($order->discount_amount > 0) {
                    $message .= "🏷️ *Discount:* -Rs. " . number_format($order->discount_amount, 0) . "\n";
                }
                $message .= "🚚 *Delivery Charges:* Rs. " . number_format($order->shipping_amount, 0) . "\n";
                $message .= "💰 *Total Amount Payable:* Rs. " . number_format($order->total, 0) . "\n";
                $message .= "━━━━━━━━━━━━━━━━━━\n";
                $message .= "Please confirm my order!";
                $waUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsappNumber) . "?text=" . rawurlencode($message);
            @endphp

            <div class="my-4">
                <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-lg px-4 py-3 shadow d-inline-flex align-items-center gap-2 fw-bold" style="background:#25D366;border-color:#25D366;font-size:1.1rem">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.392-10.416c-4.418 0-8 3.582-8 8 0 1.579.46 3.05 1.258 4.29l-1.297 4.743 4.887-1.282c1.206.732 2.618 1.157 4.131 1.157 4.418 0 8-3.582 8-8s-3.582-8-8-8z"/></svg>
                    Confirm Order on WhatsApp
                </a>
                <div class="small text-muted mt-2">Apna calculated bill aur order details WhatsApp par bhejne ke liye upar diye gaye button par click karein.</div>
            </div>

            @if($order->payment_method === 'whatsapp')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        window.location.href = @json($waUrl);
                    }, 1500);
                });
            </script>
            @endif

            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg px-5">Continue Shopping</a>
                <a href="{{ route('shop') }}" class="btn btn-outline-secondary btn-lg px-4">Browse More</a>
            </div>

            <div class="mt-4 text-muted small">
                A confirmation will be sent to <strong>{{ $order->billing_email }}</strong>
            </div>
        </div>
    </div>

</x-layouts.app>
