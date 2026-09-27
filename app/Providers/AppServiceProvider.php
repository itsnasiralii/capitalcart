<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');

        $this->app->singleton(CartService::class, fn() => new CartService());
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Use a versioned prefix so browsers that cached the old Nginx 404
        // immediately request a fresh Livewire asset after deployment.
        Livewire::setScriptRoute(function ($handle, $path) {
            return Route::get('/livewire-v2' . $path, $handle);
        });

        // Trust all proxies — Render runs behind Cloudflare + its own load balancer
        // This ensures Livewire AJAX, redirects, and URL generation work correctly
        Request::setTrustedProxies(
            ['REMOTE_ADDR', '0.0.0.0/0'],
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_PREFIX
        );

        if (app()->environment('production') || env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Do not force APP_URL as the request host in production.
        // Livewire temporary uploads use signed URLs, and forcing a different
        // hostname (for example an old Render service URL) invalidates the
        // signature. Because Render's proxy headers are trusted above, Laravel
        // can safely generate URLs from the actual incoming host.
    }
}
