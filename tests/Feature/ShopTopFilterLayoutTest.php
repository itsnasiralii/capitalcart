<?php

namespace Tests\Feature;

use App\Livewire\Shop\ProductCatalog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShopTopFilterLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_renders_with_top_filters_and_four_column_grid(): void
    {
        $cat1 = Category::create(['name' => 'Kitchen', 'slug' => 'kitchen', 'is_active' => true]);
        $cat2 = Category::create(['name' => 'Bath', 'slug' => 'bath', 'is_active' => true]);

        Product::create([
            'category_id'    => $cat1->id,
            'name'           => 'Kitchen Spoon',
            'slug'           => 'kitchen-spoon',
            'sku'            => 'K-SP-1',
            'base_price'     => 500,
            'stock_quantity' => 20,
            'is_active'      => true,
        ]);

        Product::create([
            'category_id'    => $cat2->id,
            'name'           => 'Bath Towel',
            'slug'           => 'bath-towel',
            'sku'            => 'B-TW-1',
            'base_price'     => 1200,
            'sale_price'     => 999,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        $response = $this->get(route('shop'));
        $response->assertOk();
        $response->assertSee('shop-top-filters');
        $response->assertSee('col-lg-3');
        $response->assertSee('Kitchen Spoon');
        $response->assertSee('Bath Towel');

        // Test Livewire component category filter
        Livewire::test(ProductCatalog::class)
            ->set('category', 'kitchen')
            ->assertSee('Kitchen Spoon')
            ->assertDontSee('Bath Towel')
            ->set('category', 'bath')
            ->assertSee('Bath Towel')
            ->assertDontSee('Kitchen Spoon')
            ->set('category', '')
            ->assertSee('Kitchen Spoon')
            ->assertSee('Bath Towel');
    }
}
