@extends('layouts.app')

@section('title', $thesis->title)

@section('content')
    <div class="page-shell">
        <header class="page-header flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-2">
                    <x-thesis-status-badge :status="$thesis->status" />
                </div>
                <h2 class="page-title">{{ $thesis->title }}</h2>
                <p class="page-lead">
                    Student: {{ $thesis->student->name }} ({{ $thesis->student->email }})
                </p>
            </div>
            <a wire:navigate.hover href="{{ route('supervisor.theses.index') }}" class="btn-secondary">Back to list</a>
        </header>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Source proposal</h3>
                    </div>
                    <div class="card-body space-y-3 text-sm text-stone-600">
                        <p>This thesis was created when you approved the student's proposal.</p>
                        <a wire:navigate.hover href="{{ route('supervisor.proposals.show', $thesis->proposal) }}" class="inline-flex font-medium text-navy-700 hover:text-navy-900">
                            View original proposal →
                        </a>
                    </div>
                </div>

                @if($thesis->proposal->abstract)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Abstract</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $thesis->proposal->abstract }}</p>
                        </div>
                    </div>
                @endif

                <x-milestone-tracker :thesis="$thesis" route-prefix="supervisor" />

                <x-thesis-documents :thesis="$thesis" route-prefix="supervisor" />

                <x-thesis-meetings :thesis="$thesis" route-prefix="supervisor" />

                <x-thesis-discussion :thesis="$thesis" route-prefix="supervisor" />
            </div>

            <div class="space-y-6">
                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Timeline</h3>
                    </div>
                    <div class="card-body space-y-3 text-sm text-stone-600">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Started</p>
                            <p>{{ $thesis->started_at->format('M j, Y g:i A') }}</p>
                        </div>
                        @if($thesis->completed_at)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Completed</p>
                                <p>{{ $thesis->completed_at->format('M j, Y g:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
