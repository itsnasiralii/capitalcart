<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductBulkImportService
{
    /**
     * Import products from a CSV file.
     *
     * @param string $filePath Absolute path to the CSV file.
     * @param array $options [
     *   'markup' => float,              // Default 250
     *   'default_category_id' => ?int,  // Fallback category ID
     *   'default_stock' => int,         // Default 50
     *   'update_existing' => bool       // Update if SKU/Name already exists
     * ]
     * @return array [
     *   'total' => int,
     *   'imported' => int,
     *   'updated' => int,
     *   'skipped' => int,
     *   'errors' => array
     * ]
     */
    public function importFromCsv(string $filePath, array $options = []): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException("CSV file not found or not readable: {$filePath}");
        }

        $markup            = (float) ($options['markup'] ?? 250);
        $defaultCategoryId = !empty($options['default_category_id']) ? (int) $options['default_category_id'] : null;
        $defaultStock      = (int) ($options['default_stock'] ?? 50);
        $updateExisting    = (bool) ($options['update_existing'] ?? true);

        // Ensure at least one category exists if no default was provided
        if (!$defaultCategoryId) {
            $firstCategory = Category::first();
            if (!$firstCategory) {
                $firstCategory = Category::create([
                    'name'        => 'General',
                    'slug'        => 'general',
                    'description' => 'General merchandise and essentials',
                    'is_active'   => true,
                    'sort_order'  => 1,
                ]);
            }
            $defaultCategoryId = $firstCategory->id;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Unable to open CSV stream.");
        }

        // Read and normalize headers
        $rawHeaders = fgetcsv($handle);
        if (!$rawHeaders) {
            fclose($handle);
            return ['total' => 0, 'imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['CSV file is empty.']];
        }

        $headers = array_map(function ($h) {
            // Remove UTF-8 BOM if present and lowercase
            $clean = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($h));
            return strtolower($clean ?: trim($h));
        }, $rawHeaders);

        $results = [
            'total'    => 0,
            'imported' => 0,
            'updated'  => 0,
            'skipped'  => 0,
            'errors'   => [],
        ];

        $categoryCache = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $results['total']++;

                // Map header to row values
                $data = [];
                foreach ($headers as $index => $key) {
                    $data[$key] = $row[$index] ?? null;
                }

                $name = trim($data['name'] ?? '');
                if ($name === '') {
                    $results['skipped']++;
                    continue;
                }

                try {
                    // 1. Price calculation with markup
                    $rawPrice = preg_replace('/[^0-9.]/', '', $data['price'] ?? '0');
                    $originalPrice = (float) $rawPrice;
                    $costPrice = $originalPrice > 0 ? $originalPrice : null;

                    $rawMrp = preg_replace('/[^0-9.]/', '', $data['mrp'] ?? '');
                    $originalMrp = $rawMrp !== '' ? (float) $rawMrp : null;

                    if ($originalMrp && $originalMrp > $originalPrice) {
                        $basePrice = round($originalMrp + $markup, 2);
                        $salePrice = round($originalPrice + $markup, 2);
                    } else {
                        $basePrice = round($originalPrice + $markup, 2);
                        $salePrice = null;
                    }

                    // 2. SKU determination
                    $sku = trim($data['external_id'] ?? $data['sku'] ?? '');
                    if ($sku === '') {
                        $sku = 'CC-' . strtoupper(substr(md5($name), 0, 8));
                    }

                    // 3. Category determination
                    $categoryName = trim($data['category'] ?? '');
                    $categoryId = $defaultCategoryId;

                    if ($categoryName !== '') {
                        if (!isset($categoryCache[$categoryName])) {
                            $cat = Category::firstOrCreate(
                                ['name' => $categoryName],
                                [
                                    'slug'        => Str::slug($categoryName),
                                    'description' => $categoryName . ' collection',
                                    'is_active'   => true,
                                ]
                            );
                            $categoryCache[$categoryName] = $cat->id;
                        }
                        $categoryId = $categoryCache[$categoryName];
                    }

                    // 4. Description cleanup
                    $rawDesc = trim($data['description'] ?? '');
                    if ($rawDesc === '' || strcasecmp($rawDesc, 'dgenerationz') === 0) {
                        $description = "<p>{$name} - High quality item available at CapitalCart.</p>";
                    } else {
                        $description = nl2br(e($rawDesc));
                    }
                    $shortDescription = Str::limit(strip_tags($description), 250);

                    // 5. Stock status
                    $stockText = strtolower(trim($data['stock'] ?? 'in stock'));
                    $stockQuantity = str_contains($stockText, 'out') ? 0 : $defaultStock;

                    // 6. Check for existing product by SKU or exact Name
                    $product = Product::where('sku', $sku)->orWhere('name', $name)->first();

                    if ($product) {
                        if (!$updateExisting) {
                            $results['skipped']++;
                            continue;
                        }

                        $product->update([
                            'category_id'       => $categoryId,
                            'name'              => $name,
                            'short_description' => $shortDescription,
                            'description'       => $description,
                            'base_price'        => $basePrice,
                            'sale_price'        => $salePrice,
                            'cost_price'        => $costPrice,
                            'stock_quantity'    => $stockQuantity,
                            'is_active'         => true,
                        ]);

                        $results['updated']++;
                    } else {
                        $slug = Str::slug($name);
                        $slugCount = Product::where('slug', 'like', "{$slug}%")->count();
                        if ($slugCount > 0) {
                            $slug .= '-' . ($slugCount + 1);
                        }

                        $product = Product::create([
                            'category_id'       => $categoryId,
                            'name'              => $name,
                            'slug'              => $slug,
                            'short_description' => $shortDescription,
                            'description'       => $description,
                            'base_price'        => $basePrice,
                            'sale_price'        => $salePrice,
                            'cost_price'        => $costPrice,
                            'sku'               => $sku,
                            'stock_quantity'    => $stockQuantity,
                            'manages_stock'     => true,
                            'is_featured'       => false,
                            'is_active'         => true,
                            'is_variable'       => false,
                        ]);

                        $results['imported']++;
                    }

                    // 7. Process Shopify CDN image URLs
                    $imageUrls = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $col = "image_{$i}";
                        if (!empty($data[$col])) {
                            $url = trim($data[$col]);
                            if (filter_var($url, FILTER_VALIDATE_URL)) {
                                $imageUrls[] = $url;
                            }
                        }
                    }

                    // If we have image URLs, store them in product_images
                    if (!empty($imageUrls)) {
                        // Delete old images to prevent duplicates if re-importing
                        $product->images()->delete();

                        foreach ($imageUrls as $idx => $url) {
                            ProductImage::create([
                                'product_id' => $product->id,
                                'image_url'  => $url,
                                'alt_text'   => $product->name,
                                'is_primary' => $idx === 0,
                                'sort_order' => $idx,
                            ]);
                        }
                    }

                } catch (\Throwable $e) {
                    $results['errors'][] = "Row {$results['total']} ('{$name}'): " . $e->getMessage();
                    Log::warning("CSV Import Error on row {$results['total']}: " . $e->getMessage());
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);
        return $results;
    }
}
