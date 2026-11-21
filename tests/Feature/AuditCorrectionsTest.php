<?php

namespace Tests\Feature;

use App\Livewire\Admin\Products\InventoryManager;
use App\Livewire\Admin\Products\ProductForm;
use App\Livewire\Checkout\CheckoutForm;
use App\Livewire\Shop\ProductCatalog;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\MediaAsset;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ProductBulkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AuditCorrectionsTest extends TestCase
{
    use RefreshDatabase;

    private function product(float $price = 1000): Product
    {
        $category = Category::create(['name' => 'Audit category', 'is_active' => true]);

        return Product::create(['category_id' => $category->id, 'name' => 'Audit product', 'sku' => 'AUDIT-1', 'base_price' => $price, 'stock_quantity' => 10, 'is_active' => true]);
    }

    public function test_catalog_defaults_and_reset_include_high_prices_and_filter_selling_price(): void
    {
        $p = $this->product(75000);
        Livewire::test(ProductCatalog::class)->assertSee('Audit product')->set('maxPrice', 2000)->assertDontSee('Audit product')->call('resetFilters')->assertSee('Audit product');
        $p->update(['sale_price' => 730]);
        Livewire::test(ProductCatalog::class)->set('maxPrice', 1000)->assertSee('Audit product')->set('minPrice', 2000)->assertDontSee('Audit product');
    }

    public function test_deals_only_include_discounted_products(): void
    {
        $p = $this->product();
        Livewire::test(ProductCatalog::class)->set('onSale', true)->assertDontSee('Audit product');
        $p->update(['sale_price' => 500]);
        Livewire::test(ProductCatalog::class)->set('onSale', true)->assertSee('Audit product');
    }

    public function test_cancellation_releases_stock_and_coupon_once(): void
    {
        $p = $this->product();
        $cart = app(CartService::class);
        $service = app(OrderService::class);
        Coupon::create(['code' => 'AUDIT', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
        $cart->add($p->id, null, 2);
        $cart->applyCoupon('AUDIT');
        $order = $service->createOrder([], []);
        $this->assertSame(8, $p->fresh()->stock_quantity);
        $service->updateStatus($order, 'cancelled');
        $service->updateStatus($order, 'cancelled');
        $this->assertSame(10, $p->fresh()->stock_quantity);
        $this->assertEquals(0, Coupon::first()->used_count);
        $this->travel(25)->hours();
        $this->artisan('orders:expire-abandoned')->assertSuccessful();
        $this->assertSame(10, $p->fresh()->stock_quantity);
    }

    public function test_expired_order_cannot_be_reopened(): void
    {
        $p = $this->product();
        app(CartService::class)->add($p->id, null, 2);
        $service = app(OrderService::class);
        $order = $service->createOrder([], []);
        $this->travel(25)->hours();
        $this->artisan('orders:expire-abandoned')->assertSuccessful();
        $this->assertSame(10, $p->fresh()->stock_quantity);
        $this->expectException(ValidationException::class);
        $service->updateStatus($order, 'confirmed');
    }

    public function test_stale_inventory_screen_cannot_overwrite_checkout(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $screen = Livewire::test(InventoryManager::class, ['id' => $p->id]);
        app(CartService::class)->add($p->id, null, 2);
        app(OrderService::class)->createOrder([], []);
        $screen->call('save')->assertHasErrors('inventory');
        $this->assertSame(8, $p->fresh()->stock_quantity);
    }

    public function test_stale_product_form_cannot_overwrite_checkout(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $screen = Livewire::test(ProductForm::class, ['id' => $p->id]);
        app(CartService::class)->add($p->id, null, 2);
        app(OrderService::class)->createOrder([], []);
        $screen->call('save')->assertHasErrors('save');
        $this->assertSame(8, $p->fresh()->stock_quantity);
    }

    public function test_inventory_can_be_updated_twice_after_refreshing_its_snapshot(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Livewire::test(InventoryManager::class, ['id' => $p->id])->set('simpleStock', 12)->call('save')->assertHasNoErrors()->set('simpleStock', 11)->call('save')->assertHasNoErrors();
        $this->assertSame(11, $p->fresh()->stock_quantity);
    }

    public function test_discount_tracks_quantity_and_removal(): void
    {
        $p = $this->product();
        $cart = app(CartService::class);
        $cart->add($p->id, null, 2);
        Coupon::create(['code' => 'AUDIT', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
        $cart->applyCoupon('AUDIT');
        $cart->updateQuantity($p->id.'-0', 1);
        $this->assertEquals(100, $cart->getDiscount());
        $cart->remove($p->id.'-0');
        $this->assertEquals(0, $cart->getDiscount());
    }

    public function test_jpeg_fallback_is_served_with_correct_headers(): void
    {
        $media = MediaAsset::create(['sha256' => str_repeat('a', 64), 'mime_type' => 'image/jpeg', 'byte_size' => 4, 'width' => 1, 'height' => 1, 'content_base64' => base64_encode('test')]);
        $this->get($media->url)->assertOk()->assertHeader('Content-Type', 'image/jpeg')->assertContent('test');
        $media->update(['mime_type' => 'text/html']);
        $this->get($media->url)->assertNotFound();
    }

    public function test_only_whatsapp_cod_is_offered_even_with_card_configuration(): void
    {
        $p = $this->product();
        app(CartService::class)->add($p->id);
        config(['services.stripe.key' => 'pk_test_example', 'services.stripe.secret' => 'sk_test_example', 'services.stripe.webhook_secret' => 'whsec_example']);
        $this->get('/checkout')->assertOk()->assertSee('Cash on Delivery (COD)')->assertDontSee('Credit / Debit Card')->assertDontSee('Direct Bank Transfer')->assertDontSee('js.stripe.com');
        $this->assertFalse(method_exists(CheckoutForm::class, 'createPaymentIntent'));
        $this->assertFalse(method_exists(CheckoutForm::class, 'finalizeStripeOrder'));
        foreach (['stripe', 'cod', 'bank_transfer'] as $method) {
            Livewire::test(CheckoutForm::class)->set('billing_name', 'Audit Customer')->set('billing_phone', '03001234567')->set('payment_method', $method)->call('placeOrder')->assertHasErrors('payment_method');
        }
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_whatsapp_order_includes_delivery_details_and_clear_receipt_state(): void
    {
        $p = $this->product();
        app(CartService::class)->add($p->id);
        Livewire::test(CheckoutForm::class)->set('billing_name', 'Audit Customer')->set('billing_phone', '03001234567')->set('billing_address', 'House 10, Test Street')->set('customer_notes', 'Call before delivery')->call('placeOrder')->assertHasNoErrors();
        $o = Order::first();
        $this->assertSame('whatsapp', $o->payment_method);
        $msg = app(OrderService::class)->generateWhatsAppMessage($o);
        $this->assertStringContainsString('House 10, Test Street', $msg);
        $this->assertStringContainsString('Call before delivery', $msg);
        $this->get(route('order.confirmation', $o->order_number))->assertOk()->assertSee('Pending WhatsApp Confirmation')->assertDontSee('window.open');
        app(OrderService::class)->updateStatus($o, 'cancelled');
        $this->get(route('order.confirmation', $o->order_number))->assertSee('Cancelled')->assertSee('This order is closed');
    }

    public function test_configured_expiry_is_respected_beyond_twenty_four_hours(): void
    {
        $p = $this->product();
        Setting::set('order_expiration_hours', 48);
        app(CartService::class)->add($p->id);
        $o = app(OrderService::class)->createOrder([], []);
        $this->travel(25)->hours();
        $this->artisan('orders:expire-abandoned')->assertSuccessful();
        $this->assertSame('pending_whatsapp', $o->fresh()->status);
        $this->travel(24)->hours();
        $this->artisan('orders:expire-abandoned')->assertSuccessful();
        $this->assertSame('expired', $o->fresh()->status);
        $this->assertSame(10, $p->fresh()->stock_quantity);
    }

    public function test_reimport_preserves_existing_stock_visibility_and_gallery(): void
    {
        $p = $this->product();
        $p->update(['stock_quantity' => 3, 'is_active' => false]);
        $image = ProductImage::create(['product_id' => $p->id, 'image_url' => 'https://example.com/curated.jpg', 'is_primary' => true]);
        $file = tempnam(sys_get_temp_dir(), 'audit-import-');
        try {
            file_put_contents($file, "name,sku,price,image_1\nAudit product,AUDIT-1,500,https://example.com/supplier.jpg\n");
            $result = app(ProductBulkImportService::class)->importFromCsv($file, ['default_stock' => 50]);
            $this->assertSame(1, $result['updated']);
            $this->assertSame(3, $p->fresh()->stock_quantity);
            $this->assertFalse($p->fresh()->is_active);
            $this->assertSame($image->id, $p->images()->first()->id);
        } finally {
            unlink($file);
        }
    }
}
