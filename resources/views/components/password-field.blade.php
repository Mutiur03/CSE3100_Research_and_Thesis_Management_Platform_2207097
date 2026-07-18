@props([
    'name' => 'password',
    'id' => null,
    'label' => 'Password',
    'autocomplete' => 'current-password',
    'required' => true,
    'autofocus' => false,
    'hint' => null,
    'placeholder' => null,
    'showLabel' => true,
    'minlength' => null,
])

@php
    $inputId = $id ?? $name;
    $error = $errors->first($name);
    $hintId = $hint ? "{$inputId}-hint" : null;
    $errorId = $error ? "{$inputId}-error" : null;
    $describedBy = collect([$hintId, $errorId])->filter()->implode(' ');
@endphp

<div {{ $attributes }}>
    @if($showLabel || isset($labelTrailing))
        <div class="flex items-center justify-between gap-3">
            <label for="{{ $inputId }}" class="field-label mb-0">{{ $label }}</label>
            @isset($labelTrailing)
                <div class="shrink-0">{{ $labelTrailing }}</div>
            @endisset
        </div>
    @endif

    <div class="relative mt-1.5">
        <input
            type="password"
            name="{{ $name }}"
            id="{{ $inputId }}"
            @required($required)
            @autofocus($autofocus)
            autocomplete="{{ $autocomplete }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($minlength) minlength="{{ $minlength }}" @endif
            data-password-input
            class="input-field !mt-0 pr-11 @error($name) input-error @enderror"
            @if($error) aria-invalid="true" @endif
            @if($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        >
        <button
            type="button"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-stone-400 hover:text-stone-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600/30 focus-visible:ring-inset"
            data-password-toggle
            aria-label="Show password"
            aria-controls="{{ $inputId }}"
            aria-pressed="false"
        >
            <svg data-password-icon="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg data-password-icon="hide" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
        </button>
    </div>
    @if($hint)
        <p id="{{ $hintId }}" class="field-hint">{{ $hint }}</p>
    @endif
    @if($error)
        <p id="{{ $errorId }}" class="field-error">{{ $error }}</p>
    @endif
</div>
