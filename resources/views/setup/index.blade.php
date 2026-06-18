@extends('layouts.auth')

@section('title', 'Platform Setup')

@section('content')
    <div>
        <h2 class="font-display text-2xl text-stone-900">Initial administrator setup</h2>
        <p class="mt-2 text-sm text-stone-500">
            Create the first administrator account before anyone else can use the platform.
        </p>
    </div>

    @if(! $isConfigured)
        <div class="mt-8 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium">Setup email not configured</p>
            <p class="mt-1">Set <code class="rounded bg-white px-1 py-0.5 text-xs">SETUP_ADMIN_EMAIL</code> in your <code class="rounded bg-white px-1 py-0.5 text-xs">.env</code> file, then reload this page.</p>
        </div>
    @else
        <div class="mt-8 space-y-4">
            <div class="rounded border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                <p class="font-medium text-stone-900">Authorized setup email</p>
                <p class="mt-1">{{ $maskedEmail }}</p>
                <p class="mt-2 text-xs text-stone-500">Only this address can receive the setup code and become the first admin.</p>
            </div>

            <form method="POST" action="{{ route('setup.code.send') }}">
                @csrf
                <button type="submit" class="btn-primary w-full">
                    Send setup code
                </button>
            </form>

            <p class="text-xs leading-relaxed text-stone-500">
                A one-time code will be emailed to the configured address. Codes expire after {{ $tokenLifetime }} minutes.
            </p>

            <p class="text-center text-sm text-stone-500">
                Already have a code?
                <a href="{{ route('setup.complete') }}" class="font-medium text-brand-700 hover:text-brand-800">Complete setup</a>
            </p>
        </div>
    @endif
@endsection
