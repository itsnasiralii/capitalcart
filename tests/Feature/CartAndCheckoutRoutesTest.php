<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_page_renders_successfully(): void
    {
        $response = $this->get(route('cart'));
        $response->assertStatus(200);
        $response->assertSee('Shopping Cart');
    }

    public function test_checkout_page_redirects_to_cart_when_cart_is_empty(): void
    {
        $response = $this->get(route('checkout'));
        // Empty cart redirects to /cart
        $response->assertRedirect(route('cart'));
    }

    public function test_checkout_page_renders_when_cart_has_items(): void
    {
        $category = Category::create([
            'name'       => 'Test Category',
            'slug'       => 'test-cat',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id'    => $category->id,
            'name'           => 'Test Product',
            'slug'           => 'test-product',
            'sku'            => 'TEST-01',
            'base_price'     => 1500,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        $cart = app(CartService::class);
        $cart->add($product->id, null, 1);

        $response = $this->get(route('checkout'));
        $response->assertStatus(200);
        $response->assertSee('Customer & Delivery Information', false);
        $response->assertSee('Direct Order on WhatsApp');
    }
}
