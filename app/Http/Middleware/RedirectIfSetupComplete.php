<?php

namespace App\Http\Middleware;

use App\Services\AdminSetupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSetupComplete
{
    public function __construct(
        private readonly AdminSetupService $setup,
    ) {}

    /**
     * Block setup routes after the first administrator is created.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->setup->needsSetup()) {
            return redirect()->route('login')
                ->with('success', 'Platform setup is already complete. Please sign in.');
        }

        return $next($request);
    }
}
