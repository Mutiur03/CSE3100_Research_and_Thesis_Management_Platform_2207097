<?php

namespace App\Http\Middleware;

use App\Services\AdminSetupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupComplete
{
    public function __construct(
        private readonly AdminSetupService $setup,
    ) {}

    /**
     * Redirect to setup while no administrator account exists.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->setup->needsSetup() && ! $request->routeIs('setup.*')) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
