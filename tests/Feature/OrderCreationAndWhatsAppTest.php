<?php

namespace Tests\Feature;

use App\Helpers\PhoneHelper;
use App\Livewire\Checkout\CheckoutForm;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCreationAndWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name'       => 'Mobile Accessories',
            'slug'       => 'mobile-accessories',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'category_id'    => $this->category->id,
            'name'           => 'Fast Wireless Charger',
            'slug'           => 'fast-wireless-charger',
            'sku'            => 'CC-CHG-001',
            'base_price'     => 2500,
            'stock_quantity' => 15,
            'manages_stock'  => true,
            'is_active'      => true,
        ]);

        Setting::set('whatsapp_number', '03002922584', 'whatsapp');
        Setting::set('shipping_cost', '200', 'shipping');
        Setting::set('free_shipping_threshold', '5000', 'shipping');
    }

    public function test_generates_cryptographic_order_number_with_cc_prefix(): void
    {
        $orderNumber = Order::generateOrderNumber();

        $this->assertMatchesRegularExpression('/^CC-[0-9A-F]{8}$/', $orderNumber);
    }

    public function test_creates_whatsapp_order_with_stock_decrement(): void
    {
        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($this->product->id, null, 2);

        $this->assertEquals(2, $cart->getCount());

        $orderService = app(OrderService::class);

        $billing = [
            'name'    => 'Nasir Ali',
            'phone'   => '03001234567',
            'email'   => 'customer@capitalcart.pk',
            'address' => 'House 12, Street 4, F-10/2',
            'city'    => 'Islamabad',
            'state'   => 'Federal',
            'zip'     => '44000',
            'country' => 'PK',
        ];

        $order = $orderService->createOrder($billing, $billing, 'whatsapp', 'Please call before delivery');

        $this->assertInstanceOf(Order::class, $order);
        $this->assertMatchesRegularExpression('/^CC-[0-9A-F]{8}$/', $order->order_number);
        $this->assertEquals('pending_whatsapp', $order->status);
        $this->assertTrue((bool) $order->is_guest);
        $this->assertNotNull($order->expires_at);

        // Stock decreased from 15 to 13
        $this->assertEquals(13, $this->product->fresh()->stock_quantity);

        // Cart is cleared after order
        $this->assertEquals(0, $cart->getCount());
    }

    public function test_order_creation_fails_if_insufficient_stock(): void
    {
        $this->product->update(['stock_quantity' => 1]);

        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($this->product->id, null, 5); // Requested 5, only 1 available

        $orderService = app(OrderService::class);

        $billing = [
            'name'  => 'Test Customer',
            'phone' => '03001234567',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/no longer available/i');

        $orderService->createOrder($billing, $billing, 'whatsapp');
    }

    public function test_generates_accurate_whatsapp_message_and_url(): void
    {
        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($this->product->id, null, 1);

        $orderService = app(OrderService::class);

        $billing = [
            'name'  => 'Nasir Ali',
            'phone' => '03002922584',
            'city'  => 'Islamabad',
        ];

        $order = $orderService->createOrder($billing, $billing, 'whatsapp');

        $message = $orderService->generateWhatsAppMessage($order);
        $this->assertStringContainsString('CapitalCart.pk', $message);
        $this->assertStringContainsString($order->order_number, $message);
        $this->assertStringContainsString('Nasir Ali', $message);
        $this->assertStringContainsString('Fast Wireless Charger', $message);
        $this->assertStringContainsString('Rs. 2,500', $message);

        $waUrl = $orderService->getWhatsAppUrlForOrder($order);
        $this->assertStringStartsWith('https://wa.me/923002922584?text=', $waUrl);
        $this->assertTrue(mb_check_encoding($message, 'UTF-8'));
        $this->assertStringNotContainsString("\u{FFFD}", $message);
        foreach (["\u{1F6D2}", "\u{1F4E6}", "\u{1F464}", "\u{1F4DE}", "\u{1F4CB}", "\u{1F69A}", "\u{1F4B0}", "\u{2705}"] as $emoji) {
            $this->assertStringContainsString($emoji, $message);
        }
        $this->assertStringContainsString('03002922584', $message);
        $this->assertStringContainsString('1 x Rs. 2,500.00', $message);
        $this->assertSame($message, rawurldecode(explode('?text=', $waUrl, 2)[1]));
        $this->assertStringContainsString('%F0%9F%9B%92', $waUrl);
    }

    public function test_iqbal_herbal_orders_keep_their_own_whatsapp_number_and_unicode_names(): void
    {
        $this->category->update(['whatsapp_number' => '03009362584']);
        $this->product->update(['name' => 'Herbal Cream & Oil + 50%']);
        $cart = app(CartService::class);
        $cart->add($this->product->id, null, 2);
        $service = app(OrderService::class);
        $billing = ['name' => 'ناصر علی', 'phone' => '03001234567'];
        $order = $service->createOrder($billing, $billing);
        $url = $service->getWhatsAppUrlForOrder($order);
        $this->assertStringStartsWith('https://wa.me/923009362584?text=', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame($service->generateWhatsAppMessage($order), $query['text']);
        $this->assertStringContainsString('ناصر علی', $query['text']);
        $this->assertStringContainsString('Herbal Cream & Oil + 50%', $query['text']);
        $this->assertStringContainsString('03001234567', $query['text']);
    }

    public function test_guest_checkout_via_livewire_rejects_invalid_phone(): void
    {
        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($this->product->id, null, 1);

        Livewire::test(CheckoutForm::class)
            ->set('billing_name', 'Nasir Ali')
            ->set('billing_phone', 'invalid-phone-123')
            ->call('placeOrder')
            ->assertHasErrors(['billing_phone']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_checkout_via_livewire_succeeds_with_pakistani_phone(): void
    {
        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($this->product->id, null, 1);

        Livewire::test(CheckoutForm::class)
            ->set('billing_name', 'Nasir Ali')
            ->set('billing_phone', '03002922584')
            ->set('billing_city', 'Islamabad')
            ->set('payment_method', 'whatsapp')
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
        $order = Order::first();
        $this->assertEquals('Nasir Ali', $order->billing_name);
        $this->assertEquals('03002922584', $order->billing_phone);
        $this->assertEquals('pending_whatsapp', $order->status);
    }
}
