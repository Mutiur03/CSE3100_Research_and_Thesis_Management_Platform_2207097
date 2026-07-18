<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! User::needsSetup()) {
            return redirect()->route('login')
                ->with('success', 'Platform setup is already complete. Please sign in.');
        }

        return $next($request);
    }
}
