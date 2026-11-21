<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\AttributeType;
use App\Models\ProductImage;
use App\Services\ImageStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Illuminate\Support\Str;

class ProductForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $productId = null;

    // Basic Info
    public string $name            = '';
    public string $short_description = '';
    public string $description     = '';
    public int $category_id        = 0;
    public string $base_price      = '';
    public string $sale_price      = '';
    public string $cost_price      = '';
    public string $sku             = '';
    public int $stock_quantity     = 0;
    public bool $is_featured       = false;
    public bool $is_active         = true;
    public bool $is_variable       = false;

    // Images
    // Existing database image IDs are kept here; new files are handled by $imageUploads.
    public array $images = [['id' => null, 'url' => '', 'is_primary' => true]];
    public array $imageUploads = [];

    #[Locked]
    public array $loadedImageIds = [];

    #[Locked]
    public array $loadedStock = [];

    // Variants
    public array $variants = [];

    public function mount(?int $id = null): void
    {
        $this->productId = $id;

        if ($id) {
            $product = Product::with(['images', 'allVariants.variantAttributes'])->findOrFail($id);
            $this->name              = $product->name;
            $this->short_description = $product->short_description ?? '';
            $this->description       = $product->description ?? '';
            $this->category_id       = $product->category_id;
            $this->base_price        = (string) $product->base_price;
            $this->sale_price        = $product->sale_price ? (string) $product->sale_price : '';
            $this->cost_price        = $product->cost_price ? (string) $product->cost_price : '';
            $this->sku               = $product->sku ?? '';
            $this->stock_quantity    = $product->stock_quantity;
            $this->is_featured       = $product->is_featured;
            $this->is_active         = $product->is_active;
            $this->is_variable       = $product->is_variable;

            $this->loadedImageIds = $product->images->modelKeys();
            $this->loadedStock = ['quantity' => $product->stock_quantity, 'variants' => $product->allVariants->pluck('stock_quantity', 'id')->all()];
            $this->images = $product->images->map(fn($img) => [
                'id'         => $img->id,
                'url'        => $img->image_url,
                'is_primary' => $img->is_primary,
            ])->toArray();

            if (empty($this->images)) {
                $this->images = [['id' => null, 'url' => '', 'is_primary' => true]];
            }

            $this->variants = $product->allVariants->map(fn($v) => [
                'id'             => $v->id,
                'is_active'      => $v->is_active,
                'sku'            => $v->sku ?? '',
                'price_modifier' => (string) $v->price_modifier,
                'stock_quantity' => $v->stock_quantity,
                'attribute_ids'  => $v->variantAttributes->pluck('id')->toArray(),
            ])->toArray();
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function attributeTypes()
    {
        return AttributeType::with('attributes')->get();
    }

    public function addImage(): void
    {
        if (count($this->images) < 51) {
            $this->images[] = ['id' => null, 'url' => '', 'is_primary' => false, 'key' => (string) Str::uuid()];
        }
    }

    public function reservePhotoSlots(int $count): array
    {
        $slots = [];
        foreach ($this->images as $index => $image) {
            if (empty($image['id']) && empty($this->imageUploads[$index])) {
                $slots[] = $index;
            }
        }

        if ($count < 1 || $count > 51 - count($this->images) + count($slots)) {
            return [];
        }

        while (count($slots) < $count) {
            $this->addImage();
            $slots[] = array_key_last($this->images);
        }

        return array_slice($slots, 0, $count);
    }

    public function removeImage(int $index): void
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);

        // Keep pending uploads aligned with their image rows.
        $reindexedUploads = [];
        foreach ($this->imageUploads as $uploadIndex => $upload) {
            $uploadIndex = (int) $uploadIndex;

            if ($uploadIndex === $index) {
                continue;
            }

            $newIndex = $uploadIndex > $index ? $uploadIndex - 1 : $uploadIndex;
            $reindexedUploads[$newIndex] = $upload;
        }
        $this->imageUploads = $reindexedUploads;
    }

    public function registerDirectImageUpload(int $index, int $imageId, string $imageUrl): void
    {
        if (!$this->productId) {
            return;
        }

        $image = ProductImage::query()
            ->where('product_id', $this->productId)
            ->find($imageId);

        if (!$image) {
            return;
        }

        if (!isset($this->images[$index])) {
            $this->images[$index] = [
                'id' => null,
                'url' => '',
                'is_primary' => $index === 0,
            ];
        }

        if (!in_array($image->id, $this->loadedImageIds, true)) {
            $this->loadedImageIds[] = $image->id;
        }
        $this->images[$index]['id'] = $image->id;
        $this->images[$index]['url'] = $image->image_url;
        $this->images[$index]['is_primary'] = $index === 0;
    }

    public function addVariant(): void
    {
        $this->variants[] = [
            'sku'            => '',
            'price_modifier' => '0',
            'stock_quantity' => 0,
            'attribute_ids'  => [],
        ];
    }

    public function removeVariant(int $index): void
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'array|max:51',
            'imageUploads' => 'array|max:51',
            'imageUploads.*' => array_merge(['nullable'], array_slice(ImageStorage::rules(20480), 1)),
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.price_modifier' => 'required|numeric',
            'variants.*.attribute_ids.*' => 'integer|exists:attributes,id',
        ]);

        try {
            DB::transaction(function () {
                $product = $this->productId ? Product::lockForUpdate()->findOrFail($this->productId) : new Product;
                if ($product->exists) {
                    $stock = ['quantity' => $product->stock_quantity, 'variants' => $product->allVariants()->orderBy('id')->lockForUpdate()->pluck('stock_quantity', 'id')->all()];
                    if ($stock != $this->loadedStock) {
                        throw ValidationException::withMessages(['save' => 'Stock changed since this product opened. Reload before saving.']);
                    }
                }
                if ($product->exists && collect($this->loadedImageIds)->sort()->values()->all()
                    !== $product->images()->reorder('id')->pluck('id')->all()) {
                    throw ValidationException::withMessages(['save' => 'The gallery changed in another window. Reload the product before saving.']);
                }
                $product->fill([
                    'category_id' => $this->category_id,
                    'name' => $this->name,
                    'short_description' => $this->short_description ?: null,
                    'description' => $this->description ?: null,
                    'base_price' => $this->base_price,
                    'sale_price' => $this->sale_price ?: null,
                    'cost_price' => $this->cost_price ?: null,
                    'sku' => $this->sku ?: null,
                    'stock_quantity' => $this->is_variable ? collect($this->variants)->sum('stock_quantity') : $this->stock_quantity,
                    'is_featured' => $this->is_featured,
                    'is_active' => $this->is_active,
                    'is_variable' => $this->is_variable,
                ])->save();

                $keptIds = [];
                foreach ($this->images as $index => $metadata) {
                    $image = !empty($metadata['id'])
                        ? $product->images()->findOrFail($metadata['id'])
                        : null;
                    if ($upload = ($this->imageUploads[$index] ?? null)) {
                        $url = app(ImageStorage::class)->store($upload, "imageUploads.$index", 20480);
                        $image ??= $product->images()->make();
                        $image->image_url = $url;
                    }
                    if ($image) {
                        $image->is_primary = count($keptIds) === 0;
                        $image->sort_order = count($keptIds);
                        $image->save();
                        $keptIds[] = $image->id;
                    }
                }
                $product->images()->whereNotIn('id', $keptIds)->delete();

                // Preserve the IDs referenced by existing carts, stock movements and orders.
                if ($this->is_variable) {
                    $variantIds = [];
                    foreach ($this->variants as $data) {
                        $variant = !empty($data['id'])
                            ? $product->allVariants()->findOrFail($data['id'])
                            : $product->allVariants()->make();
                        $variant->fill([
                            'sku' => $data['sku'] ?: null,
                            'price_modifier' => $data['price_modifier'] ?? 0,
                            'stock_quantity' => $data['stock_quantity'] ?? 0,
                            'is_active' => $data['is_active'] ?? true,
                        ])->save();
                        $variant->variantAttributes()->sync($data['attribute_ids'] ?? []);
                        $variantIds[] = $variant->id;
                    }
                    $product->allVariants()->whereNotIn('id', $variantIds)->delete();
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('save', 'The product could not be saved. Your previous data is unchanged. Please retry.');
            return;
        }

        session()->flash('success', 'Product saved successfully!');
        redirect()->route('admin.products.index');
    }

    public function render()
    {
        return view('livewire.admin.products.product-form')
            ->layout('layouts.admin', ['title' => $this->productId ? 'Edit Product' : 'Add Product']);
    }
}
