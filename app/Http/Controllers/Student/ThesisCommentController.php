<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreThesisCommentRequest;
use App\Models\Comment;
use App\Models\Thesis;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;

class ThesisCommentController extends Controller
{
    public function __construct(
        private readonly CommentService $comments,
    ) {}

    public function store(StoreThesisCommentRequest $request, Thesis $thesis): RedirectResponse
    {
        $this->authorize('create', [Comment::class, $thesis]);

        $this->comments->store(
            $thesis,
            $request->user(),
            $request->validated('body'),
            $request->validated('parent_id'),
            $request->isPrivate(),
        );

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Comment posted.');
    }

    public function destroy(Thesis $thesis, Comment $comment): RedirectResponse
    {
        abort_unless(
            $comment->commentable_type === Thesis::class && $comment->commentable_id === $thesis->id,
            404,
        );

        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Comment deleted.');
    }
}
