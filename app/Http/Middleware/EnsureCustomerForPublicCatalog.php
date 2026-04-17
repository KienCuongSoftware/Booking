<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerForPublicCatalog
{
    /**
     * Only allow authenticated customers to access public catalog pages.
     * Others will be redirected to their dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->role->value !== 'customer') {
            return redirect()->route($user->role->dashboardRouteName());
        }

        return $next($request);
    }
}

