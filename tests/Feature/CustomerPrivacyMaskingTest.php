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

    public function test_receipt_is_private_to_its_customer(): void
    {
        $owner = User::factory()->create();
        $another = User::factory()->create();
        $order = $this->createOrder();
        $order->update(['user_id' => $owner->id, 'is_guest' => false]);
        $url = route('order.confirmation', $order->order_number);
        $this->actingAs($another)->get($url)->assertNotFound();
        $this->flushSession();
        $response = $this->actingAs($owner)->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_customer_can_change_password_and_delete_login_while_order_is_retained(): void
    {
        $old = \Illuminate\Support\Str::random();
        $new = \Illuminate\Support\Str::random();
        $user = User::factory()->create(['password' => $old]);
        $order = $this->createOrder();
        $order->update(['user_id' => $user->id]);
        $this->actingAs($user)->put(route('account.password'), [
            'current_password' => $old, 'password' => $new, 'password_confirmation' => $new,
        ])->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($new, $user->fresh()->password));
        $this->delete(route('account.destroy'), ['current_password' => $old, 'confirm_delete' => '1'])->assertSessionHasErrors('current_password');
        $this->delete(route('account.destroy'), ['current_password' => $new, 'confirm_delete' => '1'])->assertRedirect(route('privacy'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertNull($order->fresh()->user_id);
    }

    public function test_public_pages_respect_visitor_preference(): void
    {
        $this->get('/privacy')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertDatabaseCount('active_visitors', 0);
        $this->post(route('privacy.visitors'), ['allow' => '1'])->assertRedirect(route('privacy'));
        $this->get('/')->assertOk();
        $this->assertDatabaseCount('active_visitors', 1);
        $this->post(route('privacy.visitors'), ['allow' => '0']);
        $this->get('/')->assertOk();
        $this->assertDatabaseCount('active_visitors', 0);
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

        $this->get(route('order.confirmation', $order->order_number))->assertNotFound();
        $this->withSession(['whatsapp_order_access.'.$order->id => now()->addHour()->timestamp]);
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
