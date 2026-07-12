@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Verify your email</h2>
        <p class="mt-3 text-sm leading-relaxed text-stone-500">
            We sent a verification link to
            <strong class="font-medium text-stone-800">{{ auth()->user()->email }}</strong>.
            Open that email and confirm before continuing.
        </p>
    </div>

    <ul class="mt-6 space-y-2.5 rounded border border-stone-200 bg-stone-50 px-4 py-3.5 text-sm text-stone-600">
        <li class="flex gap-2.5">
            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" aria-hidden="true"></span>
            Check inbox and spam for a message from {{ config('app.name', 'ResearchHub') }}
        </li>
        <li class="flex gap-2.5">
            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" aria-hidden="true"></span>
            Use the newest link if you requested more than one
        </li>
        <li class="flex gap-2.5">
            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" aria-hidden="true"></span>
            After verifying, you can return here and continue to the dashboard
        </li>
    </ul>

    <form method="POST" action="{{ route('verification.resend') }}" class="mt-8" id="resend-verification-form" data-auth-form>
        @csrf
        <button type="submit" id="resend-verification-submit" class="btn-primary w-full" data-loading-label="Sending…">
            Resend verification email
        </button>
    </form>

    <x-logout-button class="btn-secondary mt-3 w-full text-center" label="Use a different account" />
@endsection
