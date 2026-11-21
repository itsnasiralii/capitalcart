<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use App\Helpers\PhoneHelper;

class OrderService
{
    public function __construct(private CartService $cart) {}

    public function createOrder(array $billing, array $shipping, string $paymentMethod = 'whatsapp', ?string $notes = null, ?string $stripeCheckoutKey = null): Order
    {
        if ($paymentMethod === 'whatsapp') {
            $this->ensureSingleWhatsAppDestination();
        }

        if ($paymentMethod === 'stripe' && !$stripeCheckoutKey) {
            throw new \DomainException('Card checkout is unavailable.');
        }
        $key = $stripeCheckoutKey ?? $this->cart->checkoutKey($billing, $shipping, $paymentMethod, $notes);
        $order = Order::where('checkout_key', $key)->first();
        if (!$order) {
            try {
                $order = $this->reserveOrder($billing, $shipping, $paymentMethod, $notes, $key);
            } catch (\Illuminate\Database\UniqueConstraintViolationException|\DomainException $exception) {
                // Another request with the same cart may already have reserved the remaining stock.
                $order = Order::where('checkout_key', $key)->first() ?? throw $exception;
            }
        }

        if ($paymentMethod !== 'stripe') {
            $this->cart->clear();
        }

        // Only this browser session may open the full contact details sent to WhatsApp.
        session()->put('whatsapp_order_access.' . $order->id, now()->addHours(24)->timestamp);

        return $order;
    }

    private function reserveOrder(array $billing, array $shipping, string $paymentMethod, ?string $notes, string $key): Order
    {
        return DB::transaction(function () use ($billing, $shipping, $paymentMethod, $notes, $key) {
            $this->cart->prepareCheckout();
            $subtotal    = $this->cart->getSubtotal();
            $discount    = $this->cart->getDiscount();
            $shippingAmt = $this->cart->getShipping();
            $tax         = $this->cart->getTax();
            $total       = $this->cart->getTotal();
            $couponCode  = $this->cart->getCouponCode();

            $orderNumber = Order::generateOrderNumber();
            $isGuest = !auth()->check();

            $status = ($paymentMethod === 'whatsapp') ? 'pending_whatsapp' : 'pending';
            $hours = max(1, min(168, (int) Setting::get('order_expiration_hours', 24)));
            $expiresAt = now()->addHours($hours);

            // Normalized phone
            $rawPhone = $billing['phone'] ?? null;
            $normalizedPhone = PhoneHelper::toLocal($rawPhone);

            $order = Order::create([
                'order_number'    => $orderNumber,
                'user_id'         => auth()->id(),
                'is_guest'        => $isGuest,
                'status'          => $status,
                'expires_at'      => $expiresAt,
                'billing_name'    => $billing['name'] ?? 'Customer',
                'billing_email'   => $billing['email'] ?? ($isGuest ? 'guest-' . strtolower(substr($orderNumber, 3)) . '@capitalcart.pk' : 'customer@capitalcart.pk'),
                'billing_phone'   => $normalizedPhone ?: $rawPhone,
                'billing_address' => $billing['address'] ?? 'WhatsApp Order',
                'billing_city'    => $billing['city'] ?? 'Islamabad',
                'billing_state'   => $billing['state'] ?? 'Federal',
                'billing_zip'     => $billing['zip'] ?? '44000',
                'billing_country' => $billing['country'] ?? 'PK',
                'shipping_name'   => $shipping['name'] ?? ($billing['name'] ?? 'Customer'),
                'shipping_address'=> $shipping['address'] ?? ($billing['address'] ?? 'WhatsApp Order'),
                'shipping_city'   => $shipping['city'] ?? ($billing['city'] ?? 'Islamabad'),
                'shipping_state'  => $shipping['state'] ?? ($billing['state'] ?? 'Federal'),
                'shipping_zip'    => $shipping['zip'] ?? ($billing['zip'] ?? '44000'),
                'shipping_country'=> $shipping['country'] ?? 'PK',
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shippingAmt,
                'tax_amount'      => $tax,
                'total'           => $total,
                'coupon_code'     => $couponCode,
                'payment_method'  => $paymentMethod,
                'payment_status'  => 'pending',
                'checkout_key' => $key,
                'customer_notes'  => $notes,
            ]);

            foreach ($this->cart->getItems() as $item) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name'       => $item['name'],
                    'variant_label'      => $item['variant_label'],
                    'sku'                => $item['sku'],
                    'unit_price'         => $item['price'],
                    'quantity'           => $item['quantity'],
                    'line_total'         => $item['price'] * $item['quantity'],
                ]);

                // Decrement stock safely with row lock
                if ($item['variant_id']) {
                    $variant = ProductVariant::lockForUpdate()->find($item['variant_id']);
                    if ($variant) {
                        $variant->decrement('stock_quantity', $item['quantity']);
                    }
                }

                $product = Product::lockForUpdate()->find($item['product_id']);
                if ($product) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Increment coupon usage
            if ($couponCode) {
                Coupon::where('code', $couponCode)->increment('used_count');
            }

            return $order;
        });

    }

    public function canOpenWhatsApp(Order $order): bool
    {
        if (in_array($order->status, ['expired', 'cancelled'], true)
            || ($order->expires_at && $order->expires_at->isPast())) {
            return false;
        }

        return $this->canViewReceipt($order);
    }

    public function canViewReceipt(Order $order): bool
    {
        $user = auth()->user();
        if ($user && ($user->is_admin || ($order->user_id && $user->id === $order->user_id))) {
            return true;
        }

        return (int) session('whatsapp_order_access.' . $order->id, 0) > now()->timestamp;
    }

    public function generateWhatsAppMessage(Order $order): string
    {
        // Unicode escapes keep emoji bytes intact even when a source editor uses a legacy encoding.
        $msg  = "\u{1F6D2} *Assalam-o-Alaikum! New Order from CapitalCart.pk*\n\n";
        $msg .= "\u{1F4E6} *Order ID:* " . $order->order_number . "\n";
        if ($order->payment_method === 'whatsapp') {
            $msg .= "*Payment:* Cash on Delivery (COD)\n";
        }
        $msg .= "\u{1F464} *Customer Name:* " . $order->billing_name . "\n";
        $msg .= "\u{1F4DE} *Contact:* " . $order->billing_phone . "\n";
        if ($order->shipping_address && !in_array($order->shipping_address, ['Direct Order', 'WhatsApp Order'], true)) {
            $msg .= "Delivery address: " . $order->shipping_address . "\n";
        }
        if ($order->customer_notes) {
            $msg .= "Order notes: " . $order->customer_notes . "\n";
        }
        if ($order->shipping_city && $order->shipping_city !== 'Islamabad') {
            $msg .= "\u{1F4CD} *City:* " . $order->shipping_city . "\n";
        }
        $msg .= "\n\u{1F4CB} *Ordered Items:*\n";
        foreach ($order->items as $idx => $item) {
            $variant = $item->variant_label ? " (" . $item->variant_label . ")" : "";
            $msg .= ($idx + 1) . ". " . $item->product_name . $variant . "\n";
            $msg .= "   " . $item->quantity . " x Rs. " . number_format($item->unit_price, 2)
                . " = Rs. " . number_format($item->line_total, 2) . "\n";
        }
        $msg .= "\n\u{1F4B5} *Subtotal:* Rs. " . number_format($order->subtotal, 2) . "\n";
        if ($order->discount_amount > 0) {
            $msg .= "\u{1F3F7}\u{FE0F} *Discount:* -Rs. " . number_format($order->discount_amount, 2) . "\n";
        }
        $msg .= "\u{1F69A} *Delivery Charges:* Rs. " . number_format($order->shipping_amount, 2) . "\n";
        $msg .= "\u{1F4B0} *Final Total Payable:* Rs. " . number_format($order->total, 2) . "\n\n";
        $msg .= "\u{2705} Please confirm my order!";

        return $msg;
    }

    public function getWhatsAppUrlForOrder(Order $order): string
    {
        $message = $this->generateWhatsAppMessage($order);
        $number = $this->getOrderWhatsAppNumbers($order)->first();

        if (!$number) {
            return Setting::getWhatsAppUrl($message);
        }

        return 'https://wa.me/' . PhoneHelper::toInternational($number) . '?text=' . rawurlencode($message);
    }

    private function ensureSingleWhatsAppDestination(): void
    {
        $numbers = $this->getCartWhatsAppNumbers();

        if ($numbers->count() > 1) {
            throw new \DomainException('Iqbal Herbal Store and CapitalCart products use different WhatsApp numbers. Please place them as separate orders.');
        }
    }

    private function getCartWhatsAppNumbers()
    {
        $items = collect($this->cart->getItems());
        $products = Product::with('category')
            ->whereIn('id', $items->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        return $items->map(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            $number = $product?->category?->whatsapp_number ?: Setting::getWhatsAppNumber();

            return PhoneHelper::toLocal($number);
        })->filter()->unique()->values();
    }

    private function getOrderWhatsAppNumbers(Order $order)
    {
        return $order->items()
            ->with('product.category')
            ->get()
            ->map(function (OrderItem $item) {
                $number = $item->product?->category?->whatsapp_number ?: Setting::getWhatsAppNumber();

                return PhoneHelper::toLocal($number);
            })->filter()->unique()->values();
    }

    public function updateStatus(Order $order, string $status): void
    {
        if (!in_array($status, Order::STATUSES, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['newStatus' => 'Choose a valid order status.']);
        }
        DB::transaction(function () use ($order, $status) {
            $current = Order::lockForUpdate()->findOrFail($order->id);
            if ($current->status === $status) return;
            $allowed = [
                'pending_whatsapp' => ['confirmed', 'processing', 'shipped', 'delivered', 'cancelled'],
                'pending' => ['confirmed', 'processing', 'shipped', 'delivered', 'cancelled'],
                'confirmed' => ['processing', 'shipped', 'delivered', 'cancelled'],
                'processing' => ['shipped', 'delivered', 'cancelled'],
                'shipped' => ['delivered'],
            ];
            if (!in_array($status, $allowed[$current->status] ?? [], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['newStatus' => 'This order cannot move to that status. Closed orders cannot be reopened.']);
            }
            if ($current->payment_method === 'stripe' && $current->payment_status !== 'paid') {
                throw \Illuminate\Validation\ValidationException::withMessages(['newStatus' => 'Card payment and refund status must be confirmed by the payment provider.']);
            }
            if ($status === 'cancelled') {
                if ($current->payment_status === 'paid') {
                    throw \Illuminate\Validation\ValidationException::withMessages(['newStatus' => 'Reconcile the paid order and its refund before closing it.']);
                }
                foreach ($current->items()->orderBy('product_id')->orderBy('product_variant_id')->get() as $item) {
                    Product::withTrashed()->whereKey($item->product_id)->increment('stock_quantity', $item->quantity);
                    if ($item->product_variant_id) {
                        ProductVariant::whereKey($item->product_variant_id)->increment('stock_quantity', $item->quantity);
                    }
                }
                if ($current->coupon_code) {
                    Coupon::where('code', $current->coupon_code)->where('used_count', '>', 0)->decrement('used_count');
                }
            }
            $updates = ['status' => $status, 'expires_at' => null];
            if ($status === 'shipped') $updates['shipped_at'] = now();
            if ($status === 'delivered') $updates['delivered_at'] = now();
            $current->update($updates);
        }, 3);
        $order->refresh();
    }
}
