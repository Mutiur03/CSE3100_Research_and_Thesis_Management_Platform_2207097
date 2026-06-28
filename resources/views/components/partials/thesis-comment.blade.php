@php
    $canReply = auth()->user()->can('create', [\App\Models\Comment::class, $thesis]);
    $canDelete = auth()->user()->can('delete', $comment);
@endphp

<div class="{{ $depth > 0 ? 'border-t border-stone-100 bg-stone-50/60 pl-6' : '' }} px-6 py-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1 space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-stone-900">{{ $comment->user->name }}</p>
                <span class="text-xs text-stone-400">{{ $comment->created_at->diffForHumans() }}</span>
                @if($comment->is_private)
                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-amber-50 text-amber-900 ring-1 ring-amber-200">Private</span>
                @endif
                @if($comment->mentions->isNotEmpty())
                    <span class="text-xs text-stone-500">
                        Mentioned: {{ $comment->mentions->pluck('name')->join(', ') }}
                    </span>
                @endif
            </div>
            <div class="text-sm leading-relaxed text-stone-700">
                <x-comment-body :body="$comment->body" :thesis="$thesis" />
            </div>
        </div>
        <div class="flex shrink-0 gap-2">
            @if($canReply && $depth === 0)
                <button type="button" class="btn-secondary btn-sm" onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('hidden')">
                    Reply
                </button>
            @endif
            @if($canDelete)
                <form method="POST" action="{{ route($routePrefix.'.theses.comments.destroy', [$thesis, $comment]) }}" onsubmit="return confirm('Delete this comment?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary btn-sm text-rose-700 hover:bg-rose-50">Delete</button>
                </form>
            @endif
        </div>
    </div>

    @if($canReply && $depth === 0)
        <div id="reply-form-{{ $comment->id }}" class="{{ old('parent_id') == $comment->id ? '' : 'hidden' }} mt-4 border-t border-stone-100 pt-4">
            <form method="POST" action="{{ route($routePrefix.'.theses.comments.store', $thesis) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div>
                    <label for="reply-body-{{ $comment->id }}" class="field-label">Reply to {{ $comment->user->name }}</label>
                    <textarea name="body" id="reply-body-{{ $comment->id }}" rows="2" required class="textarea-field">{{ old('parent_id') == $comment->id ? old('body') : '' }}</textarea>
                </div>
                <button type="submit" class="btn-primary btn-sm">Post reply</button>
            </form>
        </div>
    @endif

    @foreach($comment->replies as $reply)
        @include('components.partials.thesis-comment', [
            'comment' => $reply,
            'thesis' => $thesis,
            'routePrefix' => $routePrefix,
            'depth' => 1,
        ])
    @endforeach
</div>
