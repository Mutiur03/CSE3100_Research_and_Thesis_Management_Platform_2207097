@props([
    'id',
    'name' => 'body',
    'label',
    'mentionables',
    'rows' => 3,
    'placeholder' => 'Share an update or ask a question…',
    'value' => '',
    'required' => true,
    'error' => null,
])

@php
    $listboxId = $id.'-mentions';
    $mentionablesJson = $mentionables
        ->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ])
        ->values()
        ->toJson();
@endphp

<div class="relative" data-mention-root data-mentionables="{{ e($mentionablesJson) }}">
    <label for="{{ $id }}" class="field-label">{{ $label }}</label>
    <p class="mt-0.5 text-xs text-stone-500">Type <kbd class="rounded border border-stone-200 bg-stone-50 px-1 font-sans text-[11px]">@</kbd> or pick someone below.</p>

    @if($mentionables->isNotEmpty())
        <div class="mt-2 flex flex-wrap items-center gap-2 rounded border border-navy-100 bg-navy-50/60 px-3 py-2">
            <span class="text-xs font-semibold text-navy-800">Mention</span>
            @foreach($mentionables as $user)
                <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded border border-navy-200 bg-white px-2.5 py-1 text-xs font-medium text-navy-800 touch-manipulation hover:border-navy-700 hover:bg-navy-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy-700"
                    data-mention-chip
                    data-mention-email="{{ $user->email }}"
                    data-mention-name="{{ $user->name }}"
                >
                    <span aria-hidden="true">@</span>{{ $user->name }}
                </button>
            @endforeach
        </div>
    @endif

    <div class="relative z-10 mt-1.5">
        <textarea
            name="{{ $name }}"
            id="{{ $id }}"
            rows="{{ $rows }}"
            @if($required) required @endif
            class="textarea-field !mt-0 @if($error) input-error @endif"
            placeholder="{{ $placeholder }}"
            data-mention-input
            aria-autocomplete="list"
            aria-controls="{{ $listboxId }}"
            aria-expanded="false"
            @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >{{ $value }}</textarea>

        <ul
            id="{{ $listboxId }}"
            role="listbox"
            hidden
            class="absolute left-0 right-0 z-50 mt-1 max-h-48 overflow-y-auto rounded border border-stone-200 bg-white py-1 shadow-lg"
            data-mention-listbox
        ></ul>
    </div>

    @if($error)
        <p id="{{ $id }}-error" class="field-error">{{ $error }}</p>
    @endif
</div>
