<?php

namespace App\Livewire\Checkout;

use App\Helpers\PhoneHelper;
use App\Services\CartService;
use App\Services\OrderService;
use Livewire\Component;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CheckoutForm extends Component
{
    // Contact & Delivery Information
    public string $billing_name    = '';
    public string $billing_phone   = '';
    public string $billing_email   = '';
    public string $billing_city    = 'Islamabad';
    public string $billing_address = '';
    public string $customer_notes  = '';

    // Payment Method (Default: WhatsApp)
    public string $payment_method  = 'whatsapp';
    public string $stripeClientSecret = '';

    // Submission guard
    public bool $isSubmitting = false;

    // Cart summary
    public float $subtotal  = 0;
    public float $discount  = 0;
    public float $shipping  = 0;
    public float $tax       = 0;
    public float $total     = 0;
    public array $items     = [];
    public ?string $couponCode = null;

    public function mount(CartService $cart): void
    {
        if (empty($cart->getItems())) {
            redirect()->route('cart');
            return;
        }

        $this->loadCartSummary($cart);

        if (auth()->check()) {
            $this->billing_name  = auth()->user()->name ?? '';
            $this->billing_email = auth()->user()->email ?? '';
        }
    }

    private function loadCartSummary(CartService $cart): void
    {
        $this->items      = $cart->getItems();
        $this->subtotal   = $cart->getSubtotal();
        $this->discount   = $cart->getDiscount();
        $this->shipping   = $cart->getShipping();
        $this->tax        = $cart->getTax();
        $this->total      = $cart->getTotal();
        $this->couponCode = $cart->getCouponCode();
    }

    protected function rules(): array
    {
        $rules = [
            'billing_name'    => ['required', 'string', 'min:2', 'max:255'],
            'billing_phone'   => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!PhoneHelper::isValidPakistaniNumber($value)) {
                        $fail('Please enter a valid Pakistani mobile number (e.g. 03001234567, +923001234567, or 923001234567).');
                    }
                },
            ],
            'billing_city'    => ['nullable', 'string', 'max:100'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'customer_notes'  => ['nullable', 'string', 'max:1000'],
            'payment_method'  => ['required', 'in:whatsapp,cod,bank_transfer,stripe'],
        ];

        if ($this->payment_method === 'stripe') {
            $rules['billing_email'] = ['required', 'email', 'max:255'];
        } else {
            $rules['billing_email'] = ['nullable', 'email', 'max:255'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'billing_name.required'  => 'Please enter your full name.',
            'billing_phone.required' => 'A valid Pakistani mobile number is required to confirm your order.',
            'billing_email.required' => 'Email address is required for online card payments.',
        ];
    }

    public function placeOrder(CartService $cart, OrderService $orderService)
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->validate();

        // Stripe is handled via client-side Stripe.js
        if ($this->payment_method === 'stripe') {
            $this->dispatch('stripe-submit');
            return;
        }

        $this->isSubmitting = true;

        try {
            $billing = [
                'name'    => $this->billing_name,
                'phone'   => $this->billing_phone,
                'email'   => $this->billing_email ?: null,
                'address' => $this->billing_address ?: 'Direct Order',
                'city'    => $this->billing_city ?: 'Islamabad',
                'state'   => 'Federal',
                'zip'     => '44000',
                'country' => 'PK',
            ];

            $shipping = $billing;

            $order = $orderService->createOrder(
                $billing,
                $shipping,
                $this->payment_method,
                $this->customer_notes ?: null
            );

            $this->dispatch('cart-updated');
            return redirect()->route('order.confirmation', $order->order_number);
        } catch (\Exception $e) {
            $this->isSubmitting = false;
            $this->addError('order_error', $e->getMessage());
        }
    }

    /** Called from JS to get client_secret for Stripe.js */
    public function createPaymentIntent(CartService $cart): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $amountInCents = (int) round($cart->getTotal() * 100);

        try {
            $intent = PaymentIntent::create([
                'amount'   => $amountInCents,
                'currency' => 'usd',
                'metadata' => [
                    'customer_phone' => $this->billing_phone,
                    'customer_name'  => $this->billing_name,
                ],
            ]);

            $this->stripeClientSecret = $intent->client_secret;
            $this->dispatch('stripe-ready', clientSecret: $intent->client_secret);
        } catch (\Exception $e) {
            $this->addError('order_error', 'Stripe initialization failed: ' . $e->getMessage());
        }
    }

    /** Called from JS after Stripe confirms payment */
    public function finalizeStripeOrder(string $paymentIntentId, CartService $cart, OrderService $orderService)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', message: 'Payment verification failed. Please try again.', type: 'error');
            return;
        }

        if ($intent->status !== 'succeeded') {
            $this->dispatch('show-toast', message: 'Payment not completed. Please try again.', type: 'error');
            return;
        }

        $billing = [
            'name'    => $this->billing_name,
            'phone'   => $this->billing_phone,
            'email'   => $this->billing_email,
            'address' => $this->billing_address ?: 'Direct Order',
            'city'    => $this->billing_city ?: 'Islamabad',
            'state'   => 'Federal',
            'zip'     => '44000',
            'country' => 'PK',
        ];

        $shipping = $billing;

        $order = $orderService->createOrder($billing, $shipping, 'stripe', $this->customer_notes ?: null, $paymentIntentId);

        $this->dispatch('cart-updated');
        return redirect()->route('order.confirmation', $order->order_number);
    }

    public function render(CartService $cart)
    {
        $this->loadCartSummary($cart);
        return view('livewire.checkout.checkout-form');
    }
}
