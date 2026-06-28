@props([
    'thesis',
    'routePrefix',
])

@php
    $canComment = auth()->user()->can('create', [\App\Models\Comment::class, $thesis]);
    $mentionableUsers = app(\App\Services\CommentService::class)->mentionableUsers($thesis);
    $mentionHint = $mentionableUsers->map(fn ($user) => '@'.$user->email)->join(', ');
@endphp

<div class="card overflow-hidden">
    <div class="card-section">
        <h3 class="text-sm font-semibold text-stone-900">Discussion</h3>
        <p class="mt-0.5 text-sm text-stone-500">Project thread for updates and feedback. Mention someone with their email, e.g. {{ $mentionHint }}.</p>
    </div>

    @if($canComment)
        <div class="border-t border-stone-100 bg-stone-50 px-6 py-5">
            <form method="POST" action="{{ route($routePrefix.'.theses.comments.store', $thesis) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="discussion-body" class="field-label">New comment</label>
                    <textarea name="body" id="discussion-body" rows="3" required class="textarea-field @error('body') input-error @enderror" placeholder="Share an update or ask a question">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                @if($routePrefix === 'supervisor')
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="is_private" value="1" @checked(old('is_private')) class="rounded border-stone-300 text-navy-700 focus:ring-navy-500">
                        Private note (visible to supervisors only)
                    </label>
                @endif
                <button type="submit" class="btn-primary btn-sm">Post comment</button>
            </form>
        </div>
    @endif

    @if($thesis->comments->isEmpty())
        <div class="card-body text-sm text-stone-500">
            No discussion yet. Start the conversation above.
        </div>
    @else
        <div class="divide-y divide-stone-100">
            @foreach($thesis->comments as $comment)
                @include('components.partials.thesis-comment', [
                    'comment' => $comment,
                    'thesis' => $thesis,
                    'routePrefix' => $routePrefix,
                    'depth' => 0,
                ])
            @endforeach
        </div>
    @endif
</div>
