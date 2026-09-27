<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductImageUploadController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'image' => ImageStorage::rules(20480),
            'index' => ['required', 'integer', 'min:0', 'max:50'],
            'existing_image_id' => ['nullable', 'integer'],
        ]);
        $index = (int) $validated['index'];
        $existing = !empty($validated['existing_image_id'])
            ? $product->images()->findOrFail($validated['existing_image_id'])
            : null;

        try {
            $image = DB::transaction(function () use ($request, $product, $existing, $index) {
                $url = app(ImageStorage::class)->store($request->file('image'), 'image', 20480);
                $image = $existing ?? new ProductImage(['product_id' => $product->id]);
                $image->fill([
                    'image_url' => $url,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ])->save();

                if ($index === 0) {
                    $product->images()->where('id', '!=', $image->id)->update(['is_primary' => false]);
                }

                return $image;
            });

            $asset = MediaAsset::findOrFail(basename($image->image_url));

            return response()->json([
                'ok' => true,
                'image_id' => $image->id,
                'image_url' => $image->image_url,
                'mime_type' => $asset->mime_type,
                'file_size' => $asset->byte_size,
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $requestId = (string) Str::uuid();
            \Illuminate\Support\Facades\Log::error('Catalog image upload failed', [
                'request_id' => $requestId,
                'exception' => $exception,
            ]);

            // Useful in DevTools without returning SQL bindings, image bytes or credentials.
            return response()->json([
                'message' => 'Image could not be saved. The previous photo is unchanged. Please retry.',
                'error_code' => 'SERVER_UPLOAD_FAILED',
                'request_id' => $requestId,
                'exception_type' => class_basename($exception),
                'debug' => [
                    'gd_loaded' => extension_loaded('gd'),
                    'webp_supported' => function_exists('imagewebp'),
                    'db_driver' => config('database.default'),
                    'file_size_bytes' => $request->file('image')?->getSize(),
                    'php_upload_max_filesize' => ini_get('upload_max_filesize'),
                ],
            ], 500);
        }
    }
}
