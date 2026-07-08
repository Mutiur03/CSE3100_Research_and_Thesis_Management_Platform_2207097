<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupComplete
{
    /**
     * Redirect to setup while no administrator account exists.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (User::needsSetup() && ! $request->routeIs('setup.*')) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
