<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StripeCheckout;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController
{
    public function __invoke(Request $request, StripeCheckout $checkout)
    {
        abort_unless(StripeCheckout::enabled(), 404);
        try {
            $event = Webhook::constructEvent($request->getContent(), (string) $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret'));
        } catch (SignatureVerificationException|\UnexpectedValueException) {
            abort(400);
        }
        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;
            if (isset($intent->metadata->order_number) && !$checkout->recordPayment($intent)) {
                abort(409);
            }
        } elseif ($event->type === 'charge.refunded') {
            $charge = $event->data->object;
            $belongsToStore = isset($charge->metadata->order_number)
                || Order::where('stripe_payment_intent_id', (string) $charge->payment_intent)->exists();
            if ($belongsToStore && !$checkout->recordRefund($charge)) {
                abort(409);
            }
        }
        return response()->noContent();
    }
}
