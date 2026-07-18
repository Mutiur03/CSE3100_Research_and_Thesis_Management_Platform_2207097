<?php

namespace App\Http\Middleware;

use App\Models\Proposal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnsProposal
{
    /**
     * @param  Closure(Request): (Response)  $next
     * @param  string  $relation  'student' or 'supervisor'
     */
    public function handle(Request $request, Closure $next, string $relation): Response
    {
        $proposal = $request->route('proposal');

        if (! $proposal instanceof Proposal) {
            abort(404);
        }

        $ownerId = match ($relation) {
            'student' => $proposal->student_id,
            'supervisor' => $proposal->supervisor_id,
            default => abort(500, 'Invalid proposal ownership relation.'),
        };

        abort_unless($ownerId === $request->user()?->id, 403);

        return $next($request);
    }
}
