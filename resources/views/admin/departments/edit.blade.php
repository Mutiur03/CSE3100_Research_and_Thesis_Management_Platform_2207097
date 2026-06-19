@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')
    <div class="page-shell max-w-2xl">
        <a wire:navigate.hover href="{{ route('admin.departments.show', $department) }}" class="mb-6 inline-flex items-center gap-1 text-sm text-stone-500 hover:text-stone-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to department
        </a>

        <header class="page-header">
            <h2 class="page-title">Edit department</h2>
            <p class="page-lead">Update details for {{ $department->name }}.</p>
        </header>

        @include('admin.departments._form', [
            'action' => route('admin.departments.update', $department),
            'method' => 'PUT',
            'department' => $department,
            'eligibleHeads' => $eligibleHeads,
        ])
    </div>
@endsection
