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
                    Student: {{ $thesis->student->name }} · Supervisor: {{ $thesis->supervisor->name }}
                </p>
            </div>
            <a wire:navigate.hover href="{{ route('admin.theses.index') }}" class="btn-secondary">Back to list</a>
        </header>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @if($thesis->proposal?->abstract)
                    <div class="card">
                        <div class="card-section">
                            <h3 class="text-sm font-semibold text-stone-900">Abstract</h3>
                        </div>
                        <div class="card-body">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $thesis->proposal->abstract }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <x-thesis-reviewers-panel :thesis="$thesis" :available-reviewers="$availableReviewers" route-prefix="admin" />

                @if($thesis->reviews->isNotEmpty())
                    <x-thesis-review-summary :thesis="$thesis" />
                @endif

                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Project status</h3>
                        <p class="mt-0.5 text-sm text-stone-500">Administrative control over this thesis.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.theses.status.update', $thesis) }}" class="card-body space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="status" class="field-label">Status</label>
                            <select name="status" id="status" required class="select-field">
                                @foreach(\App\Enums\ThesisStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($thesis->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary">Update status</button>
                    </form>
                </div>

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
