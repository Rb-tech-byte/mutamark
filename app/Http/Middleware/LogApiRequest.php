<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        Log::info('ERP API request', [
            'method' => $request->method(), 'route' => $request->route()?->uri(),
            'user_id' => $request->user()?->id, 'ip' => $request->ip(),
            'status' => $response->getStatusCode(), 'duration_ms' => round((microtime(true) - $start) * 1000),
        ]);
        return $response;
    }
}
