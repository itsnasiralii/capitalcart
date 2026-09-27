<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\AttributeType;
use App\Models\Attribute;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;

class ProductForm extends Component
{
    use WithFileUploads;

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
                'id'         => $img->id,
                'url'        => $img->image_url,
                'is_primary' => $img->is_primary,
            ])->toArray();

            if (empty($this->images)) {
                $this->images = [['id' => null, 'url' => '', 'is_primary' => true]];
            }

            $this->variants = $product->allVariants->map(fn($v) => [
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

    public function updatedImageUploads($value, $key): void
    {
        Log::info('[UPLOAD-DEBUG][COMPONENT]', [
            'product_id' => $this->productId,
            'array_key' => $key,
            'received' => $value !== null,
            'class' => is_object($value) ? get_class($value) : gettype($value),
            'original_name' => is_object($value) && method_exists($value, 'getClientOriginalName')
                ? $value->getClientOriginalName()
                : null,
            'size' => is_object($value) && method_exists($value, 'getSize')
                ? $value->getSize()
                : null,
        ]);
    }

    public function addImage(): void
    {
        $this->images[] = ['id' => null, 'url' => '', 'is_primary' => false];
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

        $this->images[$index]['id'] = $image->id;
        $this->images[$index]['url'] = $imageUrl;
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
            'name'           => 'required|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'base_price'     => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'imageUploads.*' => 'nullable|image|max:20480',
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

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
        } else {
            $product = Product::create($data);
        }

        // Sync images. New/replacement images are stored directly in the database.
        $keptImageIds = [];

        foreach ($this->images as $idx => $imgMeta) {
            $upload = $this->imageUploads[$idx] ?? null;
            $imageId = !empty($imgMeta['id']) ? (int) $imgMeta['id'] : null;

            $image = $imageId
                ? ProductImage::where('product_id', $product->id)->find($imageId)
                : null;

            if ($upload) {
                $prepared = $this->prepareImageForDatabase($upload);

                if (!$prepared) {
                    $this->addError("imageUploads.$idx", 'The selected image could not be processed. Please try a JPG, PNG, WebP or GIF file.');
                    return;
                }

                if (!$image) {
                    $image = ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => '',
                        'is_primary' => $idx === 0,
                        'sort_order' => $idx,
                    ]);
                }

                $image->update([
                    'image_url'  => '/product-images/' . $image->id . '?v=' . time(),
                    'is_primary' => $idx === 0,
                    'sort_order' => $idx,
                ]);

                $image->blob()->updateOrCreate([], [
                    'image_data' => $prepared['data'],
                    'mime_type'  => $prepared['mime_type'],
                    'file_size'  => $prepared['file_size'],
                ]);

                $keptImageIds[] = $image->id;
                continue;
            }

            // Keep an existing image if this row was not replaced.
            if ($image) {
                $image->update([
                    'is_primary' => $idx === 0,
                    'sort_order' => $idx,
                ]);
                $keptImageIds[] = $image->id;
            }
        }

        if (empty($keptImageIds)) {
            $product->images()->delete();
        } else {
            $product->images()->whereNotIn('id', $keptImageIds)->delete();
        }

        // Sync Variants
        if ($this->is_variable) {
            $product->allVariants()->delete();
            foreach ($this->variants as $vData) {
                $variant = ProductVariant::create([
                    'product_id'     => $product->id,
                    'sku'            => $vData['sku'] ?: null,
                    'price_modifier' => $vData['price_modifier'] ?? 0,
                    'stock_quantity' => $vData['stock_quantity'] ?? 0,
                    'is_active'      => true,
                ]);
                if (!empty($vData['attribute_ids'])) {
                    $variant->variantAttributes()->attach($vData['attribute_ids']);
                }
            }
        }

        session()->flash('success', 'Product saved successfully!');
        redirect()->route('admin.products.index');
    }

    private function prepareImageForDatabase($upload): ?array
    {
        $path = $upload->getRealPath();
        $mime = $upload->getMimeType() ?: 'application/octet-stream';
        $binary = @file_get_contents($path);

        if ($binary === false) {
            return null;
        }

        // Keep small files and animated GIFs untouched.
        if (strlen($binary) <= 3 * 1024 * 1024 || $mime === 'image/gif') {
            return [
                'data' => $binary,
                'mime_type' => $mime,
                'file_size' => strlen($binary),
            ];
        }

        $source = @imagecreatefromstring($binary);

        if (!$source) {
            return [
                'data' => $binary,
                'mime_type' => $mime,
                'file_size' => strlen($binary),
            ];
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $maxDimension = 1800;
        $scale = min(1, $maxDimension / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        ob_start();

        $written = match ($mime) {
            'image/jpeg' => imagejpeg($canvas, null, 82),
            'image/png'  => imagepng($canvas, null, 7),
            'image/webp' => function_exists('imagewebp') ? imagewebp($canvas, null, 82) : false,
            default      => false,
        };

        $optimized = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($source);

        if (!$written || !$optimized) {
            return [
                'data' => $binary,
                'mime_type' => $mime,
                'file_size' => strlen($binary),
            ];
        }

        // Never store a recompressed copy if it is larger than the source.
        if (strlen($optimized) >= strlen($binary) && $scale === 1.0) {
            $optimized = $binary;
        }

        return [
            'data' => $optimized,
            'mime_type' => $mime,
            'file_size' => strlen($optimized),
        ];
    }

    public function render()
    {
        return view('livewire.admin.products.product-form')
            ->layout('layouts.admin', ['title' => $this->productId ? 'Edit Product' : 'Add Product']);
    }
}
