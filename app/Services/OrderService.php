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

    public function createOrder(array $billing, array $shipping, string $paymentMethod = 'whatsapp', ?string $notes = null, ?string $stripePaymentIntentId = null): Order
    {
        // 1. Re-validate stock on server
        $outOfStock = $this->cart->validateStock();
        if (!empty($outOfStock)) {
            throw new \Exception('Some items are no longer available in the requested quantity: ' . implode(', ', $outOfStock));
        }

        if ($paymentMethod === 'whatsapp') {
            $this->ensureSingleWhatsAppDestination();
        }

        return DB::transaction(function () use ($billing, $shipping, $paymentMethod, $notes, $stripePaymentIntentId) {
            $subtotal    = $this->cart->getSubtotal();
            $discount    = $this->cart->getDiscount();
            $shippingAmt = $this->cart->getShipping();
            $tax         = $this->cart->getTax();
            $total       = $this->cart->getTotal();
            $couponCode  = $this->cart->getCouponCode();

            $orderNumber = Order::generateOrderNumber();
            $isGuest = !auth()->check();

            $status = ($paymentMethod === 'whatsapp') ? 'pending_whatsapp' : 'pending';
            $expiresAt = ($paymentMethod === 'whatsapp' || $isGuest) ? now()->addHours(24) : null;

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
                'payment_status'  => $paymentMethod === 'stripe' ? 'paid' : 'pending',
                'stripe_payment_intent_id' => $stripePaymentIntentId,
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

            // Clear session cart
            $this->cart->clear();

            return $order;
        });
    }

    public function generateWhatsAppMessage(Order $order): string
    {
        $msg  = "🛒 *Assalam-o-Alaikum! New Order from CapitalCart.pk*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📦 *Order ID:* " . $order->order_number . "\n";
        $msg .= "👤 *Customer Name:* " . $order->billing_name . "\n";
        $msg .= "📞 *Contact:* " . $order->billing_phone . "\n";
        if ($order->shipping_city && $order->shipping_city !== 'Islamabad') {
            $msg .= "📍 *City:* " . $order->shipping_city . "\n";
        }
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📋 *Ordered Items:*\n";
        foreach ($order->items as $idx => $item) {
            $variant = $item->variant_label ? " (" . $item->variant_label . ")" : "";
            $msg .= ($idx + 1) . ". " . $item->product_name . $variant . " x" . $item->quantity . " = Rs. " . number_format($item->line_total, 0) . "\n";
        }
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "💵 *Subtotal:* Rs. " . number_format($order->subtotal, 0) . "\n";
        if ($order->discount_amount > 0) {
            $msg .= "🏷️ *Discount:* -Rs. " . number_format($order->discount_amount, 0) . "\n";
        }
        $msg .= "🚚 *Delivery Charges:* Rs. " . number_format($order->shipping_amount, 0) . "\n";
        $msg .= "💰 *Final Total Payable:* Rs. " . number_format($order->total, 0) . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Please confirm my order!";

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
            throw new \Exception('Iqbal Herbal Store and CapitalCart products use different WhatsApp numbers. Please place them as separate orders.');
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
        $updates = ['status' => $status];

        if ($status === 'shipped') $updates['shipped_at'] = now();
        if ($status === 'delivered') $updates['delivered_at'] = now();

        $order->update($updates);
    }
}
