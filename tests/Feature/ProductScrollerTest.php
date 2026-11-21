<?php

namespace Tests\Feature;

use App\Livewire\ProductScroller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductScrollerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_scroller_renders_on_homepage(): void
    {
        $category = Category::create([
            'name'       => 'Electronics',
            'slug'       => 'electronics',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id'    => $category->id,
            'name'           => 'Wireless Headphones',
            'slug'           => 'wireless-headphones',
            'sku'            => 'HEAD-001',
            'base_price'     => 4500,
            'stock_quantity' => 15,
            'is_active'      => true,
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSeeLivewire(ProductScroller::class);
        $response->assertSee('Wireless Headphones');
        $response->assertDontSee('Flash Sale — Up to 50% OFF');
    }

    public function test_product_scroller_loads_products_and_links(): void
    {
        $category = Category::create([
            'name'       => 'Accessories',
            'slug'       => 'accessories',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id'    => $category->id,
            'name'           => 'Smart Watch Series 7',
            'slug'           => 'smart-watch-series-7',
            'sku'            => 'WATCH-007',
            'base_price'     => 12000,
            'sale_price'     => 9999,
            'stock_quantity' => 20,
            'is_active'      => true,
        ]);

        Livewire::test(ProductScroller::class)
            ->assertStatus(200)
            ->assertSee('Smart Watch Series 7')
            ->assertSee(route('product.show', $product->slug))
            ->assertSee('Rs. 9,999');
    }

    public function test_product_scroller_batches_load_more(): void
    {
        $category = Category::create([
            'name'       => 'General',
            'slug'       => 'general',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        for ($i = 1; $i <= 15; $i++) {
            Product::create([
                'category_id'    => $category->id,
                'name'           => "Batch Item {$i}",
                'slug'           => "batch-item-{$i}",
                'sku'            => "BATCH-{$i}",
                'base_price'     => 1000 + $i,
                'stock_quantity' => 10,
                'is_active'      => true,
            ]);
        }

        $component = Livewire::test(ProductScroller::class, ['initial' => 5])
            ->assertSet('loadedCount', 5)
            ->assertSet('hasMore', true)
            ->call('loadMore')
            ->assertSet('loadedCount', 15)
            ->assertSet('hasMore', false);
    }
}
