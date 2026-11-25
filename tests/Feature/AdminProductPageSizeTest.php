<?php

namespace Tests\Feature;

use App\Livewire\Admin\Products\ProductList;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProductPageSizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_size_changes_live_preserve_filters_and_reset_pagination(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true]);
        $rows = [];
        for ($i = 1; $i <= 405; $i++) {
            $rows[] = ['category_id' => $category->id, 'name' => "Page item {$i}",
                'slug' => "page-item-{$i}", 'sku' => "PAGE-{$i}", 'base_price' => $i,
                'stock_quantity' => 5, 'is_active' => true];
        }
        Product::insert($rows);

        $list = Livewire::test(ProductList::class)
            ->set('search', 'Page item')->set('category', (string) $category->id)
            ->set('stock', 'low')->set('sort', 'price_desc');
        $this->assertCount(15, $list->get('products'));

        foreach ([100, 150, 200, 400] as $size) {
            $list->call('setPage', 2)->set('perPage', (string) $size)
                ->assertSet('paginators.page', 1)
                ->assertSet('search', 'Page item')->assertSet('stock', 'low')
                ->assertSet('category', (string) $category->id)->assertSet('sort', 'price_desc');
            $products = $list->get('products');
            $this->assertCount($size, $products);
            $this->assertSame(405, $products->total());
            $this->assertSame('Page item 405', $products->first()->name);
        }

        $list->call('setPage', 2);
        $this->assertCount(5, $list->get('products'));
    }

    public function test_url_page_size_is_restored_and_invalid_sizes_are_bounded(): void
    {
        Livewire::withQueryParams(['perPage' => '200'])->test(ProductList::class)
            ->assertSet('perPage', 200);

        $list = Livewire::withQueryParams(['perPage' => '999999'])->test(ProductList::class)
            ->assertSet('perPage', 15);

        foreach ([0, -1, 999999, 'invalid'] as $invalid) {
            $list->set('perPage', $invalid)->assertSet('perPage', 15);
            $this->assertSame(15, $list->get('products')->perPage());
        }
    }

    public function test_large_list_batches_image_queries(): void
    {
        $category = Category::create(['name' => 'Batch', 'slug' => 'batch', 'is_active' => true]);
        $rows = [];
        for ($i = 1; $i <= 400; $i++) {
            $rows[] = ['category_id' => $category->id, 'name' => "Batch {$i}",
                'slug' => "batch-{$i}", 'sku' => "B-{$i}", 'base_price' => 100,
                'stock_quantity' => 5, 'is_active' => true];
        }
        Product::insert($rows);
        $images = Product::pluck('id')->map(fn ($id) => [
            'product_id' => $id, 'image_url' => '/images/hero-fallback.svg',
            'is_primary' => true, 'sort_order' => 0,
        ])->all();
        DB::table('product_images')->insert($images);

        DB::enableQueryLog();
        try {
            $list = Livewire::withQueryParams(['perPage' => '400'])->test(ProductList::class);
            $queries = collect(DB::getQueryLog())->filter(fn ($query) =>
                str_contains($query['query'], 'from "product_images"'));
        } finally {
            DB::disableQueryLog();
        }
        $this->assertCount(400, $list->get('products'));
        $this->assertCount(1, $queries, 'Image query count: '.$queries->count());
        $this->assertTrue($list->get('products')->every(fn ($product) => $product->relationLoaded('images')));
    }

    public function test_status_filter_combines_with_search_and_updates_after_toggling(): void
    {
        $category = Category::create(['name' => 'Status', 'slug' => 'status', 'is_active' => true]);
        $enabled = Product::create(['category_id' => $category->id, 'name' => 'Matching enabled',
            'slug' => 'matching-enabled', 'sku' => 'ENABLED', 'base_price' => 100,
            'stock_quantity' => 5, 'is_active' => true]);
        Product::create(['category_id' => $category->id, 'name' => 'Matching disabled',
            'slug' => 'matching-disabled', 'sku' => 'DISABLED', 'base_price' => 200,
            'stock_quantity' => 5, 'is_active' => false]);
        Product::create(['category_id' => $category->id, 'name' => 'Other enabled',
            'slug' => 'other-enabled', 'sku' => 'OTHER', 'base_price' => 300,
            'stock_quantity' => 5, 'is_active' => true]);

        $list = Livewire::withQueryParams(['status' => 'enabled', 'perPage' => '150'])
            ->test(ProductList::class)->set('search', 'Matching')->set('stock', 'low')
            ->set('category', (string) $category->id)->set('sort', 'price_desc');
        $this->assertSame([$enabled->id], $list->get('products')->pluck('id')->all());
        $list->call('toggleActive', $enabled->id);
        $this->assertCount(0, $list->get('products'));

        $list->call('setPage', 2)->set('status', 'disabled')->assertSet('paginators.page', 1);
        $this->assertCount(2, $list->get('products'));
        $list->call('resetFilters')->assertSet('status', '')->assertSet('search', '')
            ->assertSet('stock', '')->assertSet('category', '')->assertSet('perPage', 150);
        $this->assertCount(3, $list->get('products'));
    }
}
