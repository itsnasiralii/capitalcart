<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DebugLivewireUpload
{
    public function handle(Request $request, Closure $next): Response
    {
        $isUploadRequest = str_contains($request->path(), 'livewire')
            || $request->hasFile('files')
            || $request->hasFile('file');

        if (!$isUploadRequest) {
            return $next($request);
        }

        $requestId = bin2hex(random_bytes(4));

        Log::info('[UPLOAD-DEBUG][REQUEST]', [
            'id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'host' => $request->getHost(),
            'scheme' => $request->getScheme(),
            'content_length' => $request->header('content-length'),
            'content_type' => $request->header('content-type'),
            'x_forwarded_host' => $request->header('x-forwarded-host'),
            'x_forwarded_proto' => $request->header('x-forwarded-proto'),
            'route_name' => optional($request->route())->getName(),
            'php_upload_max_filesize' => ini_get('upload_max_filesize'),
            'php_post_max_size' => ini_get('post_max_size'),
        ]);

        foreach ($this->flattenFiles($request->allFiles()) as $field => $file) {
            Log::info('[UPLOAD-DEBUG][FILE]', [
                'id' => $requestId,
                'field' => $field,
                'original_name' => method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : null,
                'size' => method_exists($file, 'getSize') ? $file->getSize() : null,
                'client_mime' => method_exists($file, 'getClientMimeType') ? $file->getClientMimeType() : null,
                'detected_mime' => method_exists($file, 'getMimeType') ? $file->getMimeType() : null,
                'upload_error_code' => method_exists($file, 'getError') ? $file->getError() : null,
                'upload_error_message' => method_exists($file, 'getErrorMessage') ? $file->getErrorMessage() : null,
                'is_valid' => method_exists($file, 'isValid') ? $file->isValid() : null,
            ]);
        }

        try {
            $response = $next($request);

            $context = [
                'id' => $requestId,
                'status' => $response->getStatusCode(),
                'response_class' => get_class($response),
            ];

            if ($response->getStatusCode() >= 400 && method_exists($response, 'getContent')) {
                $body = $response->getContent();

                if (is_string($body) && $body !== '') {
                    $body = preg_replace('/("signature"\s*:\s*")[^"]+(")/i', '$1[redacted]$2', $body);
                    $context['response_preview'] = mb_substr(strip_tags($body), 0, 1500);
                }
            }

            Log::info('[UPLOAD-DEBUG][RESPONSE]', $context);

            return $response;
        } catch (Throwable $e) {
            Log::error('[UPLOAD-DEBUG][EXCEPTION]', [
                'id' => $requestId,
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    private function flattenFiles(array $files, string $prefix = ''): array
    {
        $flat = [];

        foreach ($files as $key => $value) {
            $field = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $flat += $this->flattenFiles($value, $field);
            } else {
                $flat[$field] = $value;
            }
        }

        return $flat;
    }
}
