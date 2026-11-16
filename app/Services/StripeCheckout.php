<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeCheckout
{
    public static function enabled(): bool
    {
        $key = config('services.stripe.key');
        $secret = config('services.stripe.secret');
        if (!$key || !$secret || !config('services.stripe.webhook_secret')) {
            return false;
        }
        return !app()->environment('production') ||
            (str_starts_with($key, 'pk_live_') && (str_starts_with($secret, 'sk_live_') || str_starts_with($secret, 'rk_live_')));
    }

    protected function client(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    public function begin(CartService $cart, OrderService $orders, array $billing, ?string $notes): PaymentIntent
    {
        $key = $cart->checkoutKey($billing, $billing, 'stripe', $notes);
        $order = $orders->createOrder($billing, $billing, 'stripe', $notes, $key);

        session()->put(['card_checkout_order' => $order->id, 'card_checkout_cart' => $cart->getCart()]);
        if (!in_array($order->status, ['pending', 'confirmed'], true) || $order->expires_at?->isPast()) {
            $cart->renewCheckoutToken();
            throw new \DomainException('This checkout has closed. Refresh your cart to start again.');
        }
        if ($order->stripe_payment_intent_id) {
            return $this->client()->paymentIntents->retrieve($order->stripe_payment_intent_id);
        }

        return DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            if ($order->status !== 'pending' || $order->expires_at?->isPast()) {
                throw new \DomainException('This checkout has expired. Start a new checkout.');
            }
            $client = $this->client();
            if ($order->stripe_payment_intent_id) {
                return $client->paymentIntents->retrieve($order->stripe_payment_intent_id);
            }
            $intent = $client->paymentIntents->create([
                'amount' => (int) round((float) $order->total * 100),
                'currency' => 'pkr',
                'payment_method_types' => ['card'],
                'metadata' => ['order_number' => $order->order_number],
            ], ['idempotency_key' => $order->checkout_key]);
            $order->update(['stripe_payment_intent_id' => $intent->id]);
            return $intent;
        });
    }

    public function finish(string $id, OrderService $orders): Order
    {
        $order = Order::whereKey(session('card_checkout_order'))->where('stripe_payment_intent_id', $id)->firstOrFail();
        abort_unless($orders->canViewReceipt($order), 403);
        $intent = $this->client()->paymentIntents->retrieve($id);
        if (!$this->recordPayment($intent) || $order->refresh()->payment_status !== 'paid') {
            throw new \DomainException('The payment has not been confirmed yet.');
        }
        return $order->refresh();
    }

    public function recordPayment(PaymentIntent $intent): bool
    {
        if ($intent->status !== 'succeeded' || $intent->currency !== 'pkr') {
            return false;
        }
        return DB::transaction(function () use ($intent) {
            $order = Order::where('stripe_payment_intent_id', $intent->id)
                ->where('payment_method', 'stripe')->lockForUpdate()->first();
            if (!$order || $order->order_number !== ($intent->metadata->order_number ?? null)
                || (int) round((float) $order->total * 100) !== $intent->amount_received) {
                return false;
            }
            if ($order->payment_status === 'paid' || $order->payment_status === 'refunded') {
                return true;
            }
            if ($order->status !== 'pending') {
                return false;
            }
            $order->update(['payment_status' => 'paid', 'status' => 'confirmed', 'expires_at' => null]);
            return true;
        });
    }

    public function recordRefund(\Stripe\Charge $charge): bool
    {
        if (!is_string($charge->payment_intent) || $charge->currency !== 'pkr') {
            return false;
        }
        return DB::transaction(function () use ($charge) {
            $order = Order::where('stripe_payment_intent_id', $charge->payment_intent)
                ->where('payment_method', 'stripe')->lockForUpdate()->first();
            if (!$order || $charge->amount !== (int) round((float) $order->total * 100)) {
                return false;
            }
            $refunded = (int) $charge->amount_refunded;
            if ($refunded > $charge->amount || $refunded <= 0) {
                return false;
            }
            if ($refunded > (int) round((float) $order->refunded_amount * 100)) {
                $order->refunded_amount = $refunded / 100;
                if ($refunded === $charge->amount) {
                    $order->payment_status = 'refunded';
                    $order->status = 'refunded';
                    $order->expires_at = null;
                }
                $order->save();
            }
            return true;
        });
    }

    /** Verify cancellation before returning reserved inventory to the shop. */
    public function canRelease(Order $order): bool
    {
        if (!$order->stripe_payment_intent_id) {
            return true;
        }
        $intents = $this->client()->paymentIntents;
        $intent = $intents->retrieve($order->stripe_payment_intent_id);
        if ($intent->status === 'succeeded') {
            $this->recordPayment($intent);
            return false;
        }
        if ($intent->status === 'processing') {
            return false;
        }
        return $intent->status === 'canceled' ||
            $intents->cancel($intent->id, [], ['idempotency_key' => 'expire-'.$order->order_number])->status === 'canceled';
    }
}
