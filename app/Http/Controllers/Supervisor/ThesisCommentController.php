<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThesisCommentController extends Controller
{
    public function store(Request $request, Thesis $thesis): RedirectResponse
    {
        abort_unless($thesis->supervisor_id === $request->user()->id, 403);

        if ($request->user()->isStudent()) {
            $request->merge(['is_private' => false]);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(function ($query) use ($thesis) {
                    $query->where('commentable_type', Thesis::class)
                        ->where('commentable_id', $thesis->id)
                        ->whereNull('parent_id');
                }),
            ],
            'is_private' => ['sometimes', 'boolean'],
        ]);

        Comment::store(
            $thesis,
            $request->user(),
            $validated['body'],
            $validated['parent_id'] ?? null,
            $request->user()->isSupervisor() && $request->boolean('is_private'),
        );

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Comment posted.');
    }

    public function destroy(Thesis $thesis, Comment $comment): RedirectResponse
    {
        abort_unless(
            $comment->commentable_type === Thesis::class && $comment->commentable_id === $thesis->id,
            404,
        );
        abort_unless($thesis->supervisor_id === auth()->id(), 403);

        $comment->delete();

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Comment deleted.');
    }
}
