<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapAndSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_xml_renders_correctly(): void
    {
        $category = Category::create([
            'name'       => 'Test Category',
            'slug'       => 'test-cat',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Test Product',
            'slug'        => 'test-product',
            'base_price'  => 500,
            'stock_quantity' => 10,
            'is_active'   => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee(url('/'));
        $response->assertSee(route('shop'));
        $response->assertSee(route('product.show', $product->slug));
    }

    public function test_homepage_contains_schema_org_online_store(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('OnlineStore');
        $response->assertSee('CapitalCart.pk');
        $response->assertSee('WebSite');
        $response->assertSee('SearchAction');
    }

    public function test_login_and_cart_have_noindex(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('<meta name="robots" content="noindex, follow">', false);

        $cartResponse = $this->get('/cart');
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_product_page_contains_product_schema(): void
    {
        $category = Category::create([
            'name'       => 'Test Category',
            'slug'       => 'test-cat',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Test Product',
            'slug'        => 'test-product',
            'base_price'  => 500,
            'stock_quantity' => 10,
            'is_active'   => true,
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
        $response->assertSee('CapitalCart');
    }
}
