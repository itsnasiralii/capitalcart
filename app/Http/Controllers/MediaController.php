<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(Request $request, MediaAsset $media): Response
    {
        abort_unless($media->mime_type === 'image/webp', 404);
        $bytes = base64_decode($media->content_base64, true);
        abort_if($bytes === false, 404);

        $response = response($bytes, 200, [
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPublic()->setMaxAge(31536000)->setImmutable()->setEtag($media->sha256);
        $response->isNotModified($request);

        return $response;
    }
}
