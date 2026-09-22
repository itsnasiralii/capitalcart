<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in with admin credentials to access this area.');
        }

        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Admin privileges are required.');
        }

        return $next($request);
    }
}
