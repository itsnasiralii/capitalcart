<?php

namespace App\Providers;

use App\Services\CartService;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');

        $this->app->singleton(\Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface::class, static function () {
            $policy = (new \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig())
                ->allowElement('p')->allowElement('br')->allowElement('ul')->allowElement('ol')
                ->allowElement('li')->allowElement('strong')->allowElement('em')->allowElement('b')
                ->allowElement('i')->allowElement('h2')->allowElement('h3')->allowElement('h4')
                ->allowElement('blockquote')->allowElement('a', ['href', 'title'])
                ->allowLinkSchemes(['https', 'http'])->allowRelativeLinks();
            return new \Symfony\Component\HtmlSanitizer\HtmlSanitizer($policy);
        });
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

        \Illuminate\Http\Middleware\TrustProxies::at(config('app.trusted_proxies', []));
        \Illuminate\Http\Middleware\TrustProxies::withHeaders(Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO);

        Livewire::addPersistentMiddleware([AdminMiddleware::class]);

        RateLimiter::for('login', static function (Request $request) {
            $email = $request->input('email');
            $email = is_string($email) ? Str::lower(trim($email)) : '';
            return [
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
                Limit::perMinute(5)->by('login-account:'.hash('sha256', $email.'|'.$request->ip())),
            ];
        });

        if (app()->environment('production') || env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
