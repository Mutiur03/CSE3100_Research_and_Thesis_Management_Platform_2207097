@extends('layouts.app')

@section('title', 'New Department')

@section('content')
    <div class="page-shell max-w-2xl">
        <a wire:navigate.hover href="{{ route('admin.departments.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to departments
        </a>

        <header class="page-header">
            <h2 class="page-title">New department</h2>
            <p class="page-lead">Add an academic department and optionally assign a head.</p>
        </header>

        @include('admin.departments._form', [
            'action' => route('admin.departments.store'),
            'method' => 'POST',
            'department' => null,
            'eligibleHeads' => $eligibleHeads,
        ])
    </div>
@endsection
