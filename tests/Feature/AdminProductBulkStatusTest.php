<?php

namespace Tests\Feature;

use App\Livewire\Admin\Products\ProductList;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProductBulkStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_bulk_status_toggle_enables_and_disables_selected_products(): void
    {
        $category = Category::create(['name' => 'Bulk Cat', 'slug' => 'bulk-cat', 'is_active' => true]);
        $p1 = Product::create(['category_id' => $category->id, 'name' => 'Prod 1', 'slug' => 'prod-1', 'sku' => 'P1', 'base_price' => 100, 'stock_quantity' => 10, 'is_active' => true]);
        $p2 = Product::create(['category_id' => $category->id, 'name' => 'Prod 2', 'slug' => 'prod-2', 'sku' => 'P2', 'base_price' => 200, 'stock_quantity' => 10, 'is_active' => true]);
        $p3 = Product::create(['category_id' => $category->id, 'name' => 'Prod 3', 'slug' => 'prod-3', 'sku' => 'P3', 'base_price' => 300, 'stock_quantity' => 10, 'is_active' => false]);

        Livewire::test(ProductList::class)
            ->set('selectedProducts', [$p1->id, $p2->id])
            ->call('disableSelected')
            ->assertDispatched('show-toast');

        $this->assertFalse((bool) $p1->fresh()->is_active);
        $this->assertFalse((bool) $p2->fresh()->is_active);
        $this->assertFalse((bool) $p3->fresh()->is_active);

        Livewire::test(ProductList::class)
            ->set('selectedProducts', [$p1->id, $p3->id])
            ->call('enableSelected')
            ->assertDispatched('show-toast');

        $this->assertTrue((bool) $p1->fresh()->is_active);
        $this->assertFalse((bool) $p2->fresh()->is_active);
        $this->assertTrue((bool) $p3->fresh()->is_active);
    }

    public function test_select_all_on_page_and_page_bulk_actions(): void
    {
        $category = Category::create(['name' => 'Page Cat', 'slug' => 'page-cat', 'is_active' => true]);
        for ($i = 1; $i <= 20; $i++) {
            Product::create([
                'category_id'    => $category->id,
                'name'           => "Page Product {$i}",
                'slug'           => "page-product-{$i}",
                'sku'            => "SKU-PAGE-{$i}",
                'base_price'     => 100 + $i,
                'stock_quantity' => 10,
                'is_active'      => true,
            ]);
        }

        // Test with default 15 items per page
        $component = Livewire::test(ProductList::class)
            ->set('selectAllOnPage', true);

        // Selected should match 15 items on page 1
        $selected = $component->get('selectedProducts');
        $this->assertCount(15, $selected);

        // Disable selected
        $component->call('disableSelected');
        $this->assertSame(15, Product::where('is_active', false)->count());
        $this->assertSame(5, Product::where('is_active', true)->count());

        // Test Page Bulk Actions (disable all on this page directly)
        $component->call('enablePageProducts');
        $this->assertSame(20, Product::where('is_active', true)->count());

        $component->call('disablePageProducts');
        $this->assertSame(15, Product::where('is_active', false)->count());
    }

    public function test_empty_selection_shows_warning_toast(): void
    {
        Livewire::test(ProductList::class)
            ->set('selectedProducts', [])
            ->call('disableSelected')
            ->assertDispatched('show-toast', message: 'No products selected.', type: 'warning');
    }
}
