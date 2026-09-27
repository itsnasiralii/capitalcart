<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\AttributeType;
use App\Models\ProductImage;
use App\Services\ImageStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

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
    public array $images = [['url' => '', 'is_primary' => true]];
    public array $newImages = [];
    public bool $useUploadedAsPrimary = false;

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

            $this->images = $product->images->map(fn($img) => [
                'url'        => $img->image_url,
                'is_primary' => $img->is_primary,
                'alt_text'   => $img->alt_text,
            ])->toArray();

            if (empty($this->images)) {
                $this->images = [['url' => '', 'is_primary' => true]];
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
        $this->images[] = ['url' => '', 'is_primary' => false];
    }

    public function removeImage(int $index): void
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function makePrimary(int $index): void
    {
        if (isset($this->images[$index])) {
            $image = $this->images[$index];
            unset($this->images[$index]);
            array_unshift($this->images, $image);
            $this->images = array_values($this->images);
            $this->useUploadedAsPrimary = false;
        }
    }

    public function removeNewImage(int $index): void
    {
        unset($this->newImages[$index]);
        $this->newImages = array_values($this->newImages);
        $this->resetValidation('newImages');
    }

    public function updatedNewImages(): void
    {
        $this->validate([
            'newImages' => 'array|max:8',
            'newImages.*' => ImageStorage::rules(),
        ]);
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
            'name'       => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'array|max:20',
            'images.*.url' => [
                'nullable', 'string', 'max:255',
                function ($attribute, $value, $fail) {
                    $isHttpUrl = filter_var($value, FILTER_VALIDATE_URL)
                        && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true);
                    if ($value && !$isHttpUrl && !preg_match('#^/(?:media/[a-f0-9-]{36}|storage/[a-zA-Z0-9_./-]+)$#', $value)) {
                        $fail('Use a direct http(s) image URL, or upload the image from your device.');
                    }
                },
            ],
            'newImages' => 'array|max:8',
            'newImages.*' => ImageStorage::rules(),
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.price_modifier' => 'required|numeric',
            'variants.*.attribute_ids.*' => 'integer|exists:attributes,id',
        ]);

        $data = [
            'category_id'       => $this->category_id,
            'name'              => $this->name,
            'short_description' => $this->short_description ?: null,
            'description'       => $this->description ?: null,
            'base_price'        => $this->base_price,
            'sale_price'        => $this->sale_price ?: null,
            'cost_price'        => $this->cost_price ?: null,
            'sku'               => $this->sku ?: null,
            'stock_quantity'    => $this->is_variable
                ? collect($this->variants)->sum('stock_quantity')
                : $this->stock_quantity,
            'is_featured'       => $this->is_featured,
            'is_active'         => $this->is_active,
            'is_variable'       => $this->is_variable,
        ];

        try {
            DB::transaction(function () use ($data) {
                $uploaded = [];
                foreach ($this->newImages as $index => $file) {
                    $uploaded[] = ['url' => app(ImageStorage::class)->store($file, "newImages.$index")];
                }
                $existing = array_values(array_filter($this->images, fn ($i) => !empty($i['url'])));
                $images = $this->useUploadedAsPrimary
                    ? array_merge($uploaded, $existing)
                    : array_merge($existing, $uploaded);

                $product = $this->productId ? Product::findOrFail($this->productId) : new Product;
                $product->fill($data)->save();
                $product->images()->delete();
                foreach ($images as $idx => $img) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $img['url'],
                        'alt_text' => $img['alt_text'] ?? null,
                        'is_primary' => $idx === 0,
                        'sort_order' => $idx,
                    ]);
                }

                // Editing photos must preserve variant IDs used by carts and order items.
                if ($this->is_variable) {
                    $keptIds = [];
                    foreach ($this->variants as $vData) {
                        $variant = !empty($vData['id'])
                            ? $product->allVariants()->findOrFail($vData['id'])
                            : $product->allVariants()->make();
                        $variant->fill([
                            'sku' => $vData['sku'] ?: null,
                            'price_modifier' => $vData['price_modifier'] ?? 0,
                            'stock_quantity' => $vData['stock_quantity'] ?? 0,
                            'is_active' => $vData['is_active'] ?? true,
                        ])->save();
                        $variant->variantAttributes()->sync($vData['attribute_ids'] ?? []);
                        $keptIds[] = $variant->id;
                    }
                    $product->allVariants()->whereNotIn('id', $keptIds)->delete();
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('save', 'The product could not be saved. Your previous images are unchanged. Please try again.');
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
