<?php

namespace Tests\Feature;

use App\Livewire\Admin\Products\ProductList;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
