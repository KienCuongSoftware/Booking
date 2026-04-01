<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  string  $roles  Pipe-separated role values, e.g. "admin|host"
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $allowed = array_map(
            static fn (string $r): string => strtolower(trim($r)),
            explode('|', $roles)
        );

        if (! in_array($user->role->value, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
