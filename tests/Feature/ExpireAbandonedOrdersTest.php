<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireAbandonedOrdersTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name'       => 'Home Decor',
            'slug'       => 'home-decor',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'category_id'    => $category->id,
            'name'           => 'Ceramic Vase',
            'slug'           => 'ceramic-vase',
            'sku'            => 'CC-VASE-01',
            'base_price'     => 1800,
            'stock_quantity' => 10,
            'manages_stock'  => true,
            'is_active'      => true,
        ]);
    }

    private function createTestOrder(array $attributes = []): Order
    {
        $defaults = [
            'order_number'     => Order::generateOrderNumber(),
            'is_guest'         => true,
            'status'           => 'pending_whatsapp',
            'payment_method'   => 'whatsapp',
            'payment_status'   => 'pending',
            'billing_name'     => 'Ali Khan',
            'billing_phone'    => '03001234567',
            'billing_email'    => 'guest@capitalcart.pk',
            'billing_address'  => 'Street 1, Islamabad',
            'billing_city'     => 'Islamabad',
            'billing_state'    => 'Federal',
            'billing_zip'      => '44000',
            'billing_country'  => 'PK',
            'shipping_name'    => 'Ali Khan',
            'shipping_address' => 'Street 1, Islamabad',
            'shipping_city'    => 'Islamabad',
            'shipping_state'   => 'Federal',
            'shipping_zip'     => '44000',
            'shipping_country' => 'PK',
            'subtotal'         => 3600,
            'total'            => 3800,
            'created_at'       => now(),
        ];

        return Order::create(array_merge($defaults, $attributes));
    }

    public function test_expires_abandoned_whatsapp_orders_and_restores_stock(): void
    {
        // Reduce stock by 2 for the order
        $this->product->decrement('stock_quantity', 2);
        $this->assertEquals(8, $this->product->fresh()->stock_quantity);

        // Order placed 26 hours ago
        $abandonedOrder = $this->createTestOrder([
            'order_number'    => 'CC-ABAND001',
            'created_at'      => now()->subHours(26),
            'expires_at'      => now()->subHours(2),
        ]);

        OrderItem::create([
            'order_id'     => $abandonedOrder->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price'   => 1800,
            'quantity'     => 2,
            'line_total'   => 3600,
        ]);

        $this->artisan('orders:expire-abandoned')
            ->expectsOutputToContain('Successfully expired 1 abandoned order(s) and restored stock.')
            ->assertExitCode(0);

        // Verify order is now expired
        $this->assertEquals('expired', $abandonedOrder->fresh()->status);

        // Verify stock was restored from 8 back to 10
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);
    }

    public function test_does_not_expire_confirmed_or_paid_orders(): void
    {
        $this->product->decrement('stock_quantity', 1);

        $paidOrder = $this->createTestOrder([
            'order_number'    => 'CC-PAID0001',
            'is_guest'        => false,
            'status'          => 'processing',
            'payment_status'  => 'paid',
            'created_at'      => now()->subDays(3),
        ]);

        OrderItem::create([
            'order_id'     => $paidOrder->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price'   => 1800,
            'quantity'     => 1,
            'line_total'   => 1800,
        ]);

        $this->artisan('orders:expire-abandoned')
            ->expectsOutputToContain('No abandoned orders found to expire.')
            ->assertExitCode(0);

        // Status must remain processing
        $this->assertEquals('processing', $paidOrder->fresh()->status);
        $this->assertEquals(9, $this->product->fresh()->stock_quantity);
    }

    public function test_does_not_expire_recent_orders_within_timeframe(): void
    {
        $this->product->decrement('stock_quantity', 1);

        $recentOrder = $this->createTestOrder([
            'order_number'    => 'CC-REC00001',
            'created_at'      => now()->subHours(2),
            'expires_at'      => now()->addHours(22),
        ]);

        OrderItem::create([
            'order_id'     => $recentOrder->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price'   => 1800,
            'quantity'     => 1,
            'line_total'   => 1800,
        ]);

        $this->artisan('orders:expire-abandoned')
            ->expectsOutputToContain('No abandoned orders found to expire.')
            ->assertExitCode(0);

        $this->assertEquals('pending_whatsapp', $recentOrder->fresh()->status);
    }
}
