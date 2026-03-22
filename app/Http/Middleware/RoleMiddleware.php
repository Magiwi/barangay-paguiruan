<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Abort with 403 if the authenticated user's role is not in the allowed list.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles (e.g. 'staff', 'admin').
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        // Super admin bypasses role checks.
        if ($userRole === \App\Models\User::ROLE_SUPER_ADMIN) {
            return $next($request);
        }

        if (! in_array($userRole, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
