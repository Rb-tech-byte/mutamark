<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAccountEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->is_enable_login) {
            abort(403, 'Your account has been disabled.');
        }
        return $next($request);
    }
}
