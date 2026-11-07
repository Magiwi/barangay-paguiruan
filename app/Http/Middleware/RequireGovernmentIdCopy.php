<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireGovernmentIdCopy
{
    /**
     * Send walk-in residents who still owe an ID copy to the upload page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->mustUploadGovernmentId() && ! $request->routeIs('resident.government-id.edit', 'resident.government-id.update')) {
            return redirect()->route('resident.government-id.edit');
        }

        return $next($request);
    }
}
