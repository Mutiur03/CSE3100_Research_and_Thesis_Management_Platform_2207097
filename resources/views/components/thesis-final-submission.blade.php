@props(['thesis'])

<div class="card">
    <div class="card-section">
        <h3 class="text-sm font-semibold text-stone-900">Final submission</h3>
        <p class="mt-0.5 text-sm text-stone-500">Submit your completed thesis for supervisor review.</p>
    </div>
    <div class="card-body space-y-4 text-sm text-stone-600">
        @if($thesis->isFinalSubmitted())
            <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                Final thesis submitted on {{ $thesis->final_submitted_at->format('M j, Y g:i A') }}.
            </div>
        @elseif($thesis->hasFinalDocument())
            <p>Upload a document with category <strong>Final thesis</strong>, then submit it here.</p>
            @if(auth()->user()->isStudent())
                <form method="POST" action="{{ route('student.theses.submit-final', $thesis) }}" onsubmit="return confirm('Submit your final thesis for review?')">
                    @csrf
                    <button type="submit" class="btn-primary w-full">Submit final thesis</button>
                </form>
            @endif
        @else
            <p>Upload a document with category <strong>Final thesis</strong> in the documents section before submitting.</p>
        @endif
    </div>
</div>
