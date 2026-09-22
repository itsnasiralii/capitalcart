<?php

namespace Tests\Feature;

use App\Livewire\Admin\Orders\OrderDetail;
use App\Livewire\RecentOrders;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerPrivacyMaskingTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(string $phone = '03451234567', string $name = 'Nasir Ali'): Order
    {
        return Order::create([
            'order_number'     => Order::generateOrderNumber(),
            'is_guest'         => true,
            'status'           => 'pending_whatsapp',
            'payment_method'   => 'whatsapp',
            'payment_status'   => 'pending',
            'billing_name'     => $name,
            'billing_phone'    => $phone,
            'billing_email'    => 'guest@capitalcart.pk',
            'billing_address'  => 'Sector F-7/2, Street 14',
            'billing_city'     => 'Islamabad',
            'billing_state'    => 'Federal',
            'billing_zip'      => '44000',
            'billing_country'  => 'PK',
            'shipping_name'    => $name,
            'shipping_address' => 'Sector F-7/2, Street 14',
            'shipping_city'    => 'Islamabad',
            'shipping_state'   => 'Federal',
            'shipping_zip'     => '44000',
            'shipping_country' => 'PK',
            'subtotal'         => 3500,
            'total'            => 3700,
            'created_at'       => now(),
        ]);
    }

    public function test_order_model_provides_masked_attributes(): void
    {
        $order = $this->createOrder('03451234567', 'Nasir Ali');

        $this->assertEquals('N***', $order->masked_name);
        $this->assertEquals('034*******7', $order->masked_phone);
    }

    public function test_public_order_confirmation_masks_sensitive_data(): void
    {
        $order = $this->createOrder('03451234567', 'Nasir Ali');

        $response = $this->get(route('order.confirmation', $order->order_number));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('N***');
        $response->assertSee('034*******7');

        // Verify the customer's raw phone number is masked and middle digits do not appear
        $response->assertDontSee('03451234567');
    }

    public function test_recent_orders_component_masks_customer_data(): void
    {
        Setting::set('show_recent_orders', '1', 'storefront');
        $this->createOrder('03451234567', 'Nasir Ali');

        Livewire::test(RecentOrders::class)
            ->assertSee('N***')
            ->assertSee('034*******7')
            ->assertDontSee('03451234567');
    }

    public function test_recent_orders_component_can_be_disabled_via_settings(): void
    {
        Setting::set('show_recent_orders', '0', 'storefront');
        $this->createOrder();

        Livewire::test(RecentOrders::class)
            ->assertDontSee('Recent Order Activity')
            ->assertDontSee('034*******7');
    }

    public function test_admin_sees_full_unmasked_customer_details(): void
    {
        $admin = User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@capitalcart.pk',
            'is_admin' => true,
        ]);

        $order = $this->createOrder('03451234567', 'Nasir Ali');

        $this->actingAs($admin);

        Livewire::test(OrderDetail::class, ['id' => $order->id])
            ->assertSee('Nasir Ali')
            ->assertSee('03451234567')
            ->assertSee('Sector F-7/2, Street 14');
    }
}
