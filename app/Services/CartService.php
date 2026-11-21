<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'capitalcart_cart';
    private const SHIPPING_THRESHOLD = 2000.00;
    private const SHIPPING_COST = 200.00;
    private const TAX_RATE = 0.00;

    public function getCart(): array
    {
        return Session::get(self::SESSION_KEY, ['items' => [], 'coupon_code' => null, 'discount' => 0]);
    }

    private function saveCart(array $cart): void
    {
        $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['quantity'], $cart['items']));
        $coupon = !empty($cart['coupon_code']) ? Coupon::where('code', $cart['coupon_code'])->first() : null;
        if (!$coupon || !$coupon->is_valid || $subtotal < $coupon->minimum_order) {
            $cart['coupon_code'] = null;
            $cart['discount'] = 0;
        } else {
            $cart['discount'] = max(0, min($subtotal, $coupon->calculateDiscount($subtotal)));
        }
        $cart['checkout_token'] ??= (string) \Illuminate\Support\Str::uuid();
        Session::put(self::SESSION_KEY, $cart);
    }

    public function add(int $productId, ?int $variantId = null, int $qty = 1): void
    {
        if ($qty < 1 || $qty > 9999) {
            throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'Choose a quantity from 1 to 9999.']);
        }
        $product = Product::with('images')->where('is_active', true)->findOrFail($productId);
        $variant = $variantId ? $product->variants()->with('variantAttributes')->findOrFail($variantId) : null;
        if ($product->is_variable && !$variant) {
            throw \Illuminate\Validation\ValidationException::withMessages(['variant' => 'Choose a product option first.']);
        }

        $key = $productId . '-' . ($variantId ?? '0');
        $cart = $this->getCart();

        $price = $variant ? $variant->effective_price : $product->effective_price;
        $variantLabel = $variant ? $variant->label : null;

        if (isset($cart['items'][$key])) {
            $cart['items'][$key]['quantity'] = min(9999, $cart['items'][$key]['quantity'] + $qty);
        } else {
            $cart['items'][$key] = [
                'product_id'    => $productId,
                'variant_id'    => $variantId,
                'name'          => $product->name,
                'variant_label' => $variantLabel,
                'price'         => (float) $price,
                'quantity'      => $qty,
                'image_url'     => $product->primary_image_url,
                'sku'           => $variant?->sku ?? $product->sku,
                'slug'          => $product->slug,
            ];
        }

        $this->saveCart($cart);
    }

    public function checkoutKey(array $billing, array $shipping, string $method, ?string $notes): string
    {
        $cart = $this->getCart();
        // Older carts predate checkout_token. An existing session still gives them a stable key.
        return hash('sha256', json_encode([
            $cart['checkout_token'] ?? session()->getId(), auth()->id(), $cart, $billing, $shipping, $method, $notes,
        ], JSON_THROW_ON_ERROR));
    }

    public function renewCheckoutToken(): void
    {
        $cart = $this->getCart();
        unset($cart['checkout_token']);
        $this->saveCart($cart);
    }

    public function remove(string $key): void
    {
        $cart = $this->getCart();
        unset($cart['items'][$key]);
        $this->saveCart($cart);
    }

    public function updateQuantity(string $key, int $qty): void
    {
        $cart = $this->getCart();
        if (isset($cart['items'][$key])) {
            if ($qty <= 0) {
                unset($cart['items'][$key]);
            } else {
                $cart['items'][$key]['quantity'] = min(9999, $qty);
            }
        }
        $this->saveCart($cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function getItems(): array
    {
        return $this->getCart()['items'];
    }

    public function getSubtotal(): float
    {
        return array_reduce($this->getItems(), function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0.0);
    }

    public function getDiscount(): float
    {
        return (float) $this->getCart()['discount'];
    }

    public function getCouponCode(): ?string
    {
        return $this->getCart()['coupon_code'];
    }

    public function getShipping(): float
    {
        if (empty($this->getItems())) return 0;
        $threshold = (float) \App\Models\Setting::get('free_shipping_threshold', 2000);
        $cost = (float) \App\Models\Setting::get('shipping_cost', 200);

        return $this->getSubtotal() >= $threshold ? 0 : $cost;
    }

    /** Called inside the order transaction, before prices or stock are committed. */
    public function prepareCheckout(): void
    {
        $cart = $this->getCart();
        if (empty($cart['items'])) {
            throw new \DomainException('Your cart is empty.');
        }

        $items = $cart['items'];
        ksort($items);
        $reserved = [];
        $changed = false;
        foreach ($items as $key => $item) {
            $product = Product::lockForUpdate()->find($item['product_id']);
            $variant = $product && $item['variant_id']
                ? $product->variants()->lockForUpdate()->find($item['variant_id']) : null;
            $qty = $item['quantity'];
            $reserved[$item['product_id']] = ($reserved[$item['product_id']] ?? 0) + $qty;
            if (!$product || !$product->is_active || $qty < 1 || $qty > 9999
                || $product->stock_quantity < $reserved[$item['product_id']]
                || (($product->is_variable || $item['variant_id']) && (!$variant || $variant->stock_quantity < $qty))) {
                throw new \DomainException('Some items are no longer available in the requested quantity.');
            }

            $price = (float) ($variant ? $variant->effective_price : $product->effective_price);
            if ($price < 0) {
                throw new \DomainException('An item is no longer available at its listed price.');
            }
            if (round((float) $item['price'], 2) !== round($price, 2)) {
                $cart['items'][$key]['price'] = $price;
                $changed = true;
            }
        }

        $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['quantity'], $cart['items']));
        $discount = 0.0;
        if ($code = $cart['coupon_code'] ?? null) {
            $coupon = Coupon::where('code', $code)->lockForUpdate()->first();
            if (!$coupon || !$coupon->is_valid || $subtotal < $coupon->minimum_order) {
                $cart['coupon_code'] = null;
                $changed = true;
            } else {
                $discount = max(0, min($subtotal, $coupon->calculateDiscount($subtotal)));
            }
        }
        if ($changed || round((float) $cart['discount'], 2) !== round($discount, 2)) {
            $cart['discount'] = $discount;
            $this->saveCart($cart);
            throw new \DomainException('Your cart prices or discount changed. Review the updated total and try again.');
        }
    }

    public function getTax(): float
    {
        return round(($this->getSubtotal() - $this->getDiscount()) * self::TAX_RATE, 2);
    }

    public function getTotal(): float
    {
        return max(0, $this->getSubtotal() - $this->getDiscount() + $this->getShipping() + $this->getTax());
    }

    public function getItemCount(): int
    {
        return array_sum(array_column($this->getItems(), 'quantity'));
    }

    public function getCount(): int
    {
        return $this->getItemCount();
    }

    public function applyCoupon(string $code): bool|string
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) return 'Invalid coupon code.';
        if (!$coupon->is_valid) return 'This coupon is no longer valid.';
        if ($this->getSubtotal() < $coupon->minimum_order) {
            return "Minimum order of Rs. " . number_format($coupon->minimum_order, 0) . " required.";
        }

        $cart = $this->getCart();
        $cart['coupon_code'] = strtoupper($code);
        $cart['discount'] = $coupon->calculateDiscount($this->getSubtotal());
        $this->saveCart($cart);

        return true;
    }

    public function removeCoupon(): void
    {
        $cart = $this->getCart();
        $cart['coupon_code'] = null;
        $cart['discount'] = 0;
        $this->saveCart($cart);
    }
}
