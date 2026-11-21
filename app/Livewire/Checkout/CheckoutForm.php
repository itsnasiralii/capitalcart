<?php

namespace App\Livewire\Checkout;

use App\Helpers\PhoneHelper;
use App\Services\CartService;
use App\Services\OrderService;
use Livewire\Component;
use Illuminate\Support\Facades\RateLimiter;

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
            'payment_method'  => ['required', 'in:whatsapp'],
        ];

        $rules['billing_email'] = ['nullable', 'email', 'max:255'];

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'billing_name.required'  => 'Please enter your full name.',
            'billing_phone.required' => 'A valid Pakistani mobile number is required to confirm your order.',
        ];
    }

    public function placeOrder(CartService $cart, OrderService $orderService)
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->validate();

        if (!$this->limitCheckout()) return;
        $this->isSubmitting = true;

        try {
            $billing = $this->billing();
            $shipping = $billing;

            $order = $orderService->createOrder(
                $billing,
                $shipping,
                $this->payment_method,
                $this->customer_notes ?: null
            );

            $this->dispatch('cart-updated');
            return redirect()->route('order.confirmation', $order->order_number);
        } catch (\DomainException $e) {
            $this->isSubmitting = false;
            $this->addError('order_error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->isSubmitting = false;
            $this->addError('order_error', 'Your order could not be placed. Please try again.');
        }
    }

    private function billing(): array
    {
        return [
            'name'    => $this->billing_name,
            'phone'   => $this->billing_phone,
            'email'   => $this->billing_email ?: null,
            'address' => $this->billing_address ?: 'Direct Order',
            'city'    => $this->billing_city ?: 'Islamabad',
            'state'   => '',
            'zip'     => '',
            'country' => 'PK',
        ];
    }

    private function limitCheckout(): bool
    {
        $key = 'checkout:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 12)) {
            $this->addError('order_error', 'Please wait a minute before trying checkout again.');
            return false;
        }
        RateLimiter::hit($key, 60);
        return true;
    }

    public function render(CartService $cart)
    {
        $this->loadCartSummary($cart);
        return view('livewire.checkout.checkout-form');
    }
}
