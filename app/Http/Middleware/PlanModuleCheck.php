<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PlanModuleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$moduleName = null): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        // Dedicated enterprise system for Mutamark ERP - perpetual access without SaaS plan expiration lockouts
        $coreAlwaysActive = ['ProductService', 'Account', 'Pos', 'Inventory', 'DoubleEntry', 'Lead', 'Hrm'];

        if ($moduleName != null) {
            $moduleNames = explode('-', $moduleName);
            foreach ($moduleNames as $m) {
                if (in_array($m, $coreAlwaysActive) || module_is_active($m)) {
                    return $next($request);
                }
            }
            return redirect()->route('dashboard')->with('error', __('Permission denied '));
        }

        $response = $next($request);
        return $response;
    }
}
