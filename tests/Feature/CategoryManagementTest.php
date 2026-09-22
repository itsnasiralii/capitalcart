<?php

namespace Tests\Feature;

use App\Livewire\Admin\Categories\CategoryManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Nasir Ali',
            'email'    => 'admin@capitalcart.pk',
            'is_admin' => true,
        ]);

        $this->customer = User::factory()->create([
            'name'     => 'Regular Customer',
            'email'    => 'customer@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_guest_cannot_access_admin_categories(): void
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden_from_admin_categories(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.categories.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CategoryManager::class)
            ->set('name', 'Smart Watches')
            ->set('slug', 'smart-watches')
            ->set('description', 'Latest wearable technology')
            ->set('sort_order', 1)
            ->set('is_active', true)
            ->call('saveCategory')
            ->assertDispatched('close-category-modal');

        $this->assertDatabaseHas('categories', [
            'name'        => 'Smart Watches',
            'slug'        => 'smart-watches',
            'description' => 'Latest wearable technology',
            'sort_order'  => 1,
            'is_active'   => true,
        ]);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create([
            'name'       => 'Home Appliances',
            'slug'       => 'home-appliances',
            'sort_order' => 2,
            'is_active'  => true,
        ]);

        Livewire::test(CategoryManager::class)
            ->call('toggleStatus', $category->id);

        $this->assertFalse((bool) $category->fresh()->is_active);

        Livewire::test(CategoryManager::class)
            ->call('toggleStatus', $category->id);

        $this->assertTrue((bool) $category->fresh()->is_active);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create([
            'name'       => 'Empty Category',
            'slug'       => 'empty-cat',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        Livewire::test(CategoryManager::class)
            ->call('confirmDelete', $category->id)
            ->call('deleteCategory');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_safe_deletion_prevents_deleting_category_with_products_without_reassignment(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create([
            'name'       => 'Electronics',
            'slug'       => 'electronics',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        Product::create([
            'category_id'    => $category->id,
            'name'           => 'Wireless Earbuds',
            'slug'           => 'wireless-earbuds',
            'sku'            => 'CC-EAR-001',
            'base_price'     => 3500,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        // Attempt deletion without reassignment
        Livewire::test(CategoryManager::class)
            ->call('confirmDelete', $category->id)
            ->call('deleteCategory')
            ->assertHasErrors(['reassign']);

        // Category should still exist in database
        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        // Now create a target category and reassign
        $targetCategory = Category::create([
            'name'       => 'General Gadgets',
            'slug'       => 'general-gadgets',
            'sort_order' => 2,
            'is_active'  => true,
        ]);

        Livewire::test(CategoryManager::class)
            ->call('confirmDelete', $category->id)
            ->set('reassignToCategoryId', $targetCategory->id)
            ->call('deleteCategory');

        // Original category is deleted, product reassigned to target category
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', [
            'name'        => 'Wireless Earbuds',
            'category_id' => $targetCategory->id,
        ]);
    }
}
