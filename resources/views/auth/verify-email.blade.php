@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Verify your email</h2>
        <p class="mt-3 text-sm leading-relaxed text-stone-500">
            A verification link was sent to <strong class="font-medium text-stone-800">{{ auth()->user()->email }}</strong>.
            Please confirm your address before continuing.
        </p>
    </div>

    <form method="POST" action="{{ route('verification.resend') }}" class="mt-8" id="resend-verification-form">
        @csrf
        <button type="submit" id="resend-verification-submit" class="btn-primary w-full">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn-secondary w-full">
            Sign out
        </button>
    </form>
@endsection
