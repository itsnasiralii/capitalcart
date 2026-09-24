<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;

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

        // Force APP_URL for correct asset/Livewire URL generation
        if ($appUrl = env('APP_URL')) {
            \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
        }
    }
}
