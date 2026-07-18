@php
    $canReply = auth()->user()->isStudent() || auth()->user()->isSupervisor();
    $canDelete = auth()->id() === $comment->user_id || auth()->user()->isSupervisor();
    $mentionTargets = $mentionTargets ?? \App\Models\Comment::mentionableUsers($thesis)
        ->reject(fn ($user) => $user->id === auth()->id())
        ->values();
    $replyOpen = old('parent_id') == $comment->id;
@endphp

<div class="{{ $depth > 0 ? 'border-t border-stone-100 bg-stone-50/60 pl-6' : '' }} px-6 py-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1 space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-stone-900">{{ $comment->user->name }}</p>
                <span class="text-xs tabular-nums text-stone-400">{{ $comment->created_at->diffForHumans() }}</span>
                @if($comment->is_private)
                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-amber-50 text-amber-900 ring-1 ring-amber-200">Private</span>
                @endif
                @if($comment->mentions->isNotEmpty())
                    <span class="break-words text-xs text-stone-500">
                        Mentioned: {{ $comment->mentions->pluck('name')->join(', ') }}
                    </span>
                @endif
            </div>
            <div class="break-words text-sm leading-relaxed text-stone-700">
                <x-comment-body :body="$comment->body" :thesis="$thesis" />
            </div>
        </div>
        <div class="flex shrink-0 gap-2">
            @if($canReply && $depth === 0)
                <button
                    type="button"
                    class="btn-secondary btn-sm"
                    data-disclosure-toggle
                    aria-expanded="{{ $replyOpen ? 'true' : 'false' }}"
                    aria-controls="reply-form-{{ $comment->id }}"
                >
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
        <div id="reply-form-{{ $comment->id }}" class="{{ $replyOpen ? '' : 'hidden' }} mt-4 border-t border-stone-100 pt-4">
            <form method="POST" action="{{ route($routePrefix.'.theses.comments.store', $thesis) }}" class="space-y-3" autocomplete="off">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <x-mention-field
                    :id="'reply-body-'.$comment->id"
                    :label="'Reply to '.$comment->user->name"
                    :mentionables="$mentionTargets"
                    :rows="2"
                    placeholder="Write a reply."
                    :value="old('parent_id') == $comment->id ? old('body') : ''"
                    :error="old('parent_id') == $comment->id ? $errors->first('body') : null"
                />
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn-primary btn-sm">Post Reply</button>
                    <button type="button" class="btn-secondary btn-sm" data-disclosure-close aria-controls="reply-form-{{ $comment->id }}">Cancel</button>
                </div>
            </form>
        </div>
    @endif

    @foreach($comment->replies as $reply)
        @include('components.partials.thesis-comment', [
            'comment' => $reply,
            'thesis' => $thesis,
            'routePrefix' => $routePrefix,
            'depth' => 1,
            'mentionTargets' => $mentionTargets,
        ])
    @endforeach
</div>
