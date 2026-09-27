<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActiveVisitor;

class TrackActiveVisitors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only track for regular GET webpage requests
        if (!$request->isMethod('GET') || $request->ajax() || $request->is('admin*', 'livewire*', 'storage*', 'media/*')) {
            return $response;
        }

        // Avoid counting authenticated administrators
        if (auth()->check() && auth()->user()->is_admin) {
            return $response;
        }

        // Detect known crawlers / bots
        $userAgent = strtolower($request->header('User-Agent', ''));
        if (preg_match('/bot|crawl|spider|slurp|facebook|whatsapp|preview|curl/i', $userAgent)) {
            return $response;
        }

        // Track session once every 60 seconds to avoid excessive DB writes
        $lastTracked = session()->get('last_visitor_ping');
        if (!$lastTracked || (time() - $lastTracked) > 60) {
            session()->put('last_visitor_ping', time());
            try {
                ActiveVisitor::track(session()->getId());

                // Probabilistic cleanup of expired records (1 in 50 requests)
                if (random_int(1, 50) === 1) {
                    ActiveVisitor::pruneExpired(10);
                }
            } catch (\Throwable $e) {
                // Fail silently without disrupting user experience
            }
        }

        return $response;
    }
}
