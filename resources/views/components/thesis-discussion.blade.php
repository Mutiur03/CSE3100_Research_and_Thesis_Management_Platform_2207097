@props([
    'thesis',
    'routePrefix',
])

@php
    $canComment = auth()->user()->isStudent() || auth()->user()->isSupervisor();
    $mentionTargets = \App\Models\Comment::mentionableUsers($thesis)
        ->reject(fn ($user) => $user->id === auth()->id())
        ->values();
@endphp

<div class="card overflow-hidden">
    <div class="card-section">
        <h3 class="text-sm font-semibold text-stone-900">Discussion</h3>
        <p class="mt-0.5 text-sm text-stone-500">Project thread for updates and feedback.</p>
    </div>

    @if($canComment)
        <div class="border-t border-stone-100 bg-stone-50 px-6 py-5">
            <form method="POST" action="{{ route($routePrefix.'.theses.comments.store', $thesis) }}" class="space-y-4" autocomplete="off">
                @csrf
                <x-mention-field
                    id="discussion-body"
                    label="New comment"
                    :mentionables="$mentionTargets"
                    :rows="3"
                    placeholder="Share an update or ask a question."
                    :value="old('body')"
                    :error="$errors->first('body')"
                />
                @if($routePrefix === 'supervisor')
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600 touch-manipulation">
                        <input type="checkbox" name="is_private" value="1" @checked(old('is_private')) class="rounded border-stone-300 text-navy-700 focus-visible:ring-navy-500">
                        Private note (visible to supervisors only)
                    </label>
                @endif
                <button type="submit" class="btn-primary btn-sm">Post Comment</button>
            </form>
        </div>
    @endif

    @if($thesis->comments->isEmpty())
        <div class="card-body text-sm text-stone-500">
            No discussion yet. Start the conversation above.
        </div>
    @else
        <div class="divide-y divide-stone-100 border-t border-stone-100">
            @foreach($thesis->comments as $comment)
                @include('components.partials.thesis-comment', [
                    'comment' => $comment,
                    'thesis' => $thesis,
                    'routePrefix' => $routePrefix,
                    'depth' => 0,
                    'mentionTargets' => $mentionTargets,
                ])
            @endforeach
        </div>
    @endif
</div>
