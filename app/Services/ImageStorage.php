<?php

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImageStorage
{
    public static function rules(int $maxKilobytes = 5120): array
    {
        return ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . $maxKilobytes];
    }

    /** Store public catalog photos in the database, independent of Render's temporary disk. */
    public function store(UploadedFile $file, string $field = 'image', int $maxKilobytes = 5120): string
    {
        $validator = Validator::make(['upload' => $file], ['upload' => self::rules($maxKilobytes)]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([$field => $validator->errors()->get('upload')]);
        }
        $info = @getimagesize($file->getRealPath());

        // Check dimensions before allocating a decoded image in GD.
        if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 16_000_000) {
            throw ValidationException::withMessages([$field => 'Choose an image no larger than 16 megapixels.']);
        }

        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$source) {
            throw ValidationException::withMessages([$field => 'This image could not be read. Please export it as JPG, PNG or WebP and try again.']);
        }

        $canvas = null;
        try {
            // Respect camera orientation before stripping metadata from the saved image.
            if (($info['mime'] ?? '') === 'image/jpeg' && function_exists('exif_read_data')) {
                $exif = @exif_read_data($file->getRealPath());
                $orientation = (int) ($exif['Orientation'] ?? 1);
                if (in_array($orientation, [2, 4, 5, 7], true)) {
                    imageflip($source, IMG_FLIP_HORIZONTAL);
                }
                $angle = match ($orientation) {
                    3, 4 => 180,
                    5, 6 => -90,
                    7, 8 => 90,
                    default => 0,
                };
                if ($angle) {
                    $rotated = imagerotate($source, $angle, 0);
                    if ($rotated !== false) {
                        imagedestroy($source);
                        $source = $rotated;
                    }
                }
            }

            $scale = min(1, 1600 / max(imagesx($source), imagesy($source)));
            $width = max(1, (int) round(imagesx($source) * $scale));
            $height = max(1, (int) round(imagesy($source) * $scale));
            $canvas = imagecreatetruecolor($width, $height);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

            $bytes = '';
            foreach ([82, 65, 45] as $quality) {
                ob_start();
                try {
                    $encoded = imagewebp($canvas, null, $quality);
                    $bytes = ob_get_contents();
                } finally {
                    ob_end_clean();
                }
                if (!$encoded || !$bytes) {
                    throw new \RuntimeException('Image encoding failed.');
                }
                if (strlen($bytes) <= 1_048_576) {
                    break;
                }
            }
            if (strlen($bytes) > 1_048_576) {
                throw ValidationException::withMessages([$field => 'This image is too detailed. Please resize it and try again.']);
            }

            $asset = MediaAsset::firstOrCreate(['sha256' => hash('sha256', $bytes)], [
                'mime_type' => 'image/webp',
                'byte_size' => strlen($bytes),
                'width' => $width,
                'height' => $height,
                'content_base64' => base64_encode($bytes),
            ]);

            return $asset->url;
        } finally {
            imagedestroy($source);
            if ($canvas) {
                imagedestroy($canvas);
            }
        }
    }
}
