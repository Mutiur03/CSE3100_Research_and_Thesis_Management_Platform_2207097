<?php

namespace App\Http\Middleware;

use App\Models\Thesis;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnsThesis
{
    /**
     * @param  Closure(Request): (Response)  $next
     * @param  string  $relation  'student' or 'supervisor'
     */
    public function handle(Request $request, Closure $next, string $relation): Response
    {
        $thesis = $request->route('thesis');

        if (! $thesis instanceof Thesis) {
            abort(404);
        }

        $ownerId = match ($relation) {
            'student' => $thesis->student_id,
            'supervisor' => $thesis->supervisor_id,
            default => abort(500, 'Invalid thesis ownership relation.'),
        };

        abort_unless($ownerId === $request->user()?->id, 403);

        return $next($request);
    }
}
