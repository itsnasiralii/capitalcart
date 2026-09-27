<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProductImageUploadController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:20480'],
            'index' => ['required', 'integer', 'min:0', 'max:50'],
            'existing_image_id' => ['nullable', 'integer'],
        ]);

        $index = (int) $validated['index'];
        $existingId = !empty($validated['existing_image_id'])
            ? (int) $validated['existing_image_id']
            : null;

        try {
            $prepared = $this->prepareImage($request->file('image'));

            if (!$prepared) {
                return response()->json([
                    'message' => 'The selected image could not be processed.',
                    'error_code' => 'IMAGE_PROCESSING_FAILED',
                ], 422);
            }

            $image = null;

            if ($existingId) {
                $image = ProductImage::query()
                    ->where('product_id', $product->id)
                    ->find($existingId);
            }

            if (!$image) {
                $image = ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => '',
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }

            if ($index === 0) {
                ProductImage::query()
                    ->where('product_id', $product->id)
                    ->where('id', '!=', $image->id)
                    ->update(['is_primary' => false]);
            }

            $image->update([
                'image_url' => '/product-images/' . $image->id . '?v=' . time(),
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);

            $image->blob()->updateOrCreate([], [
                'image_data' => $prepared['data'],
                'mime_type' => $prepared['mime_type'],
                'file_size' => $prepared['file_size'],
            ]);

            return response()->json([
                'ok' => true,
                'image_id' => $image->id,
                'image_url' => $image->image_url,
                'mime_type' => $prepared['mime_type'],
                'file_size' => $prepared['file_size'],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Image upload failed on the server.',
                'error_code' => 'SERVER_UPLOAD_FAILED',
                'exception_type' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ], 500);
        }
    }

    private function prepareImage($upload): ?array
    {
        $path = $upload->getRealPath();
        $mime = $upload->getMimeType() ?: 'application/octet-stream';
        $binary = @file_get_contents($path);

        if ($binary === false) {
            return null;
        }

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
            'image/png' => imagepng($canvas, null, 7),
            'image/webp' => function_exists('imagewebp') ? imagewebp($canvas, null, 82) : false,
            default => false,
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

        if (strlen($optimized) >= strlen($binary) && $scale === 1.0) {
            $optimized = $binary;
        }

        return [
            'data' => $optimized,
            'mime_type' => $mime,
            'file_size' => strlen($optimized),
        ];
    }
}
