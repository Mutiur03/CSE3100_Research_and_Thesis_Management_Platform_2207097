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
                    Supervisor: {{ $thesis->supervisor->name }}
                    @if($thesis->department)
                        · {{ $thesis->department->code }}
                    @endif
                </p>
            </div>
            <a wire:navigate.hover href="{{ route('student.theses.index') }}" class="btn-secondary">Back to list</a>
        </header>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="card">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Approved proposal</h3>
                    </div>
                    <div class="card-body space-y-3 text-sm text-stone-600">
                        <p>This thesis project was created from your approved proposal.</p>
                        <a wire:navigate.hover href="{{ route('student.proposals.show', $thesis->proposal) }}" class="inline-flex font-medium text-navy-700 hover:text-navy-900">
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

                <div class="card overflow-hidden">
                    <div class="card-section">
                        <h3 class="text-sm font-semibold text-stone-900">Milestones</h3>
                        <p class="mt-0.5 text-sm text-stone-500">Track progress on supervisor-defined deliverables.</p>
                    </div>

                    @if($thesis->milestones->isEmpty())
                        <div class="card-body text-sm text-stone-500">
                            No milestones yet. Your supervisor will add deliverables and due dates here.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead class="table-head">
                                    <tr>
                                        <th class="px-6 py-3">Milestone</th>
                                        <th class="px-6 py-3">Due</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100 bg-white">
                                    @foreach($thesis->milestones as $milestone)
                                        <tr class="{{ $milestone->isOverdue() ? 'bg-amber-50/60' : 'hover:bg-stone-50/80' }}">
                                            <td class="px-6 py-4">
                                                <p class="font-medium {{ $milestone->isOverdue() ? 'text-amber-900' : 'text-stone-800' }}">{{ $milestone->title }}</p>
                                                @if($milestone->description)
                                                    <p class="mt-1 text-sm text-stone-500">{{ $milestone->description }}</p>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                                {{ $milestone->due_date->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <x-milestone-status-badge :status="$milestone->status" :overdue="$milestone->isOverdue()" />
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                                @can('complete', $milestone)
                                                    <form method="POST" action="{{ route('student.theses.milestones.complete', [$thesis, $milestone]) }}" class="inline" onsubmit="return confirm('Mark this milestone as complete?')">
                                                        @csrf
                                                        <button type="submit" class="btn-primary btn-sm">Mark complete</button>
                                                    </form>
                                                @elseif($milestone->status === \App\Enums\MilestoneStatus::Completed && $milestone->completed_at)
                                                    <span class="text-xs text-stone-500">Done {{ $milestone->completed_at->format('M j, Y') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
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
