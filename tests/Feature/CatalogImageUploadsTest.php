<?php

namespace Tests\Feature;

use App\Livewire\Admin\Categories\CategoryManager;
use App\Livewire\Admin\Products\ProductForm;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\User;
use App\Services\ImageStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogImageUploadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    private function category(): Category
    {
        return Category::create(['name' => 'Iqbal Herbal Store', 'slug' => 'iqbal-herbal-store',
            'whatsapp_number' => '03009362584', 'image_url' => '/storage/categories/old.jpg', 'is_active' => true]);
    }

    public function test_optimized_image_survives_loss_of_local_files_and_supports_http_caching(): void
    {
        $file = UploadedFile::fake()->image('photo.png', 2400, 1200);
        $url = app(ImageStorage::class)->store($file);
        // Re-uploading the same photo does not consume another database row.
        $this->assertSame($url, app(ImageStorage::class)->store($file));
        $this->assertDatabaseCount('media_assets', 1);
        $asset = MediaAsset::firstOrFail();
        $this->assertSame(1600, (int) $asset->width);
        $this->assertSame(800, (int) $asset->height);
        $this->assertLessThanOrEqual(1_048_576, $asset->byte_size);
        unlink($file->getRealPath());
        Storage::disk('public')->deleteDirectory('categories');

        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->assertSame(base64_decode($asset->content_base64), $response->getContent());
        $this->assertStringContainsString('immutable', $response->headers->get('Cache-Control'));
        $this->get($url, ['If-None-Match' => $response->headers->get('ETag')])->assertStatus(304);
        $this->get('/media/00000000-0000-4000-8000-000000000000')->assertNotFound();
    }

    public function test_renamed_html_svg_and_oversized_files_are_rejected(): void
    {
        foreach ([
            UploadedFile::fake()->createWithContent('photo.png', '<script>alert(1)</script>'),
            UploadedFile::fake()->createWithContent('photo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->image('large.jpg')->size(5121),
        ] as $file) {
            try {
                app(ImageStorage::class)->store($file);
                $this->fail('Unsafe or oversized upload was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image', $exception->errors());
            }
        }
        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_admin_can_create_product_with_laptop_uploads_and_a_primary_photo(): void
    {
        $this->admin();
        $category = $this->category();
        Livewire::test(ProductForm::class)
            ->set('name', 'Herbal Cream')->set('category_id', $category->id)
            ->set('base_price', '500')->set('stock_quantity', 6)
            ->set('images', [['url' => '', 'is_primary' => true]])
            ->set('newImages', [UploadedFile::fake()->image('front.png', 100, 100), UploadedFile::fake()->image('back.jpg', 120, 100)])
            ->call('save')->assertHasNoErrors()->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Herbal Cream')->firstOrFail();
        $this->assertCount(2, $product->images);
        $this->assertTrue($product->images[0]->is_primary);
        $this->assertFalse($product->images[1]->is_primary);
        $this->assertStringStartsWith('/media/', $product->primary_image_url);
        $this->get($product->primary_image_url)->assertOk();
    }

    public function test_adding_photo_preserves_existing_urls_variant_ids_and_inventory(): void
    {
        $this->admin();
        $product = Product::create(['category_id' => $this->category()->id, 'name' => 'Cream',
            'base_price' => 500, 'stock_quantity' => 7, 'is_variable' => true, 'is_active' => true]);
        $product->images()->create(['image_url' => 'https://example.com/existing.jpg', 'is_primary' => true]);
        $variant = $product->allVariants()->create(['sku' => 'CREAM-50', 'stock_quantity' => 7, 'price_modifier' => 20, 'is_active' => true]);
        Livewire::test(ProductForm::class, ['id' => $product->id])
            ->set('newImages', [UploadedFile::fake()->image('new.png')])
            ->set('useUploadedAsPrimary', true)
            ->call('save')->assertHasNoErrors();

        $product->refresh();
        $this->assertStringStartsWith('/media/', $product->primary_image_url);
        $this->assertSame('https://example.com/existing.jpg', $product->images[1]->image_url);
        $this->assertSame($variant->id, $product->allVariants->sole()->id);
        $this->assertSame(7, (int) $variant->fresh()->stock_quantity);
    }

    public function test_category_upload_and_replacement_preserve_whatsapp_routing(): void
    {
        $this->admin();
        $category = $this->category();
        Livewire::test(CategoryManager::class)->call('openEditModal', $category->id)
            ->set('image', UploadedFile::fake()->image('category.png', 600, 400))
            ->call('saveCategory')->assertHasNoErrors()->assertDispatched('close-category-modal');
        $firstUrl = $category->fresh()->image_url;
        $this->assertStringStartsWith('/media/', $firstUrl);
        $this->get($firstUrl)->assertOk();
        Livewire::test(CategoryManager::class)->call('openEditModal', $category->id)
            ->set('image', UploadedFile::fake()->image('replacement.jpg', 500, 500))
            ->call('saveCategory')->assertHasNoErrors();
        $category->refresh();
        $this->assertNotSame($firstUrl, $category->image_url);
        $this->assertSame('03009362584', $category->whatsapp_number);
        $this->get($category->image_url)->assertOk();
    }

    public function test_failed_upload_keeps_existing_category_and_product_data(): void
    {
        $this->admin();
        $category = $this->category();
        $this->mock(ImageStorage::class)->shouldReceive('store')->andThrow(new \RuntimeException('Storage unavailable'));
        Livewire::test(CategoryManager::class)->call('openEditModal', $category->id)
            ->set('name', 'Changed')->set('image', UploadedFile::fake()->image('new.png'))
            ->call('saveCategory')->assertHasErrors(['image']);
        $this->assertSame('Iqbal Herbal Store', $category->fresh()->name);
        $this->assertSame('/storage/categories/old.jpg', $category->fresh()->image_url);

        $product = Product::create(['category_id' => $category->id, 'name' => 'Original', 'base_price' => 500]);
        $product->images()->create(['image_url' => 'https://example.com/old.jpg', 'is_primary' => true]);
        Livewire::test(ProductForm::class, ['id' => $product->id])->set('name', 'Changed')
            ->set('newImages', [UploadedFile::fake()->image('photo.png')])
            ->call('save')->assertHasErrors(['save']);
        $this->assertSame('Original', $product->fresh()->name);
        $this->assertSame('https://example.com/old.jpg', $product->fresh()->primary_image_url);
    }

    public function test_upload_forms_require_admin_access(): void
    {
        $this->get(route('admin.products.create'))->assertRedirect(route('login'));
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['is_admin' => false]));
        $this->get(route('admin.products.create'))->assertForbidden();
        $this->get(route('admin.categories.index'))->assertForbidden();
    }
}
