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

                <div class="card overflow-hidden">
                    <div class="card-section flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-stone-900">Milestones</h3>
                            <p class="mt-0.5 text-sm text-stone-500">Define deliverables and due dates for this thesis.</p>
                        </div>
                    </div>

                    @if($thesis->milestones->isNotEmpty())
                        <div class="overflow-x-auto border-t border-stone-100">
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
                                        @if((string) request('edit') === (string) $milestone->id)
                                            <tr>
                                                <td colspan="4" class="bg-stone-50 px-6 py-4">
                                                    <form method="POST" action="{{ route('supervisor.theses.milestones.update', [$thesis, $milestone]) }}" class="space-y-4">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="grid gap-4 sm:grid-cols-2">
                                                            <div class="sm:col-span-2">
                                                                <label for="edit-title-{{ $milestone->id }}" class="field-label">Title</label>
                                                                <input type="text" name="title" id="edit-title-{{ $milestone->id }}" value="{{ old('title', $milestone->title) }}" required class="input-field @error('title') input-error @enderror">
                                                                @error('title')
                                                                    <p class="field-error">{{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                            <div>
                                                                <label for="edit-due-{{ $milestone->id }}" class="field-label">Due date</label>
                                                                <input type="date" name="due_date" id="edit-due-{{ $milestone->id }}" value="{{ old('due_date', $milestone->due_date->format('Y-m-d')) }}" required class="input-field @error('due_date') input-error @enderror">
                                                                @error('due_date')
                                                                    <p class="field-error">{{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                            <div class="sm:col-span-2">
                                                                <label for="edit-description-{{ $milestone->id }}" class="field-label">Description</label>
                                                                <textarea name="description" id="edit-description-{{ $milestone->id }}" rows="2" class="textarea-field @error('description') input-error @enderror">{{ old('description', $milestone->description) }}</textarea>
                                                                @error('description')
                                                                    <p class="field-error">{{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="submit" class="btn-primary btn-sm">Save changes</button>
                                                            <a wire:navigate.hover href="{{ route('supervisor.theses.show', $thesis) }}" class="btn-secondary btn-sm">Cancel</a>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        @else
                                            <tr class="{{ $milestone->isOverdue() ? 'bg-amber-50/60' : 'hover:bg-stone-50/80' }}">
                                                <td class="px-6 py-4">
                                                    <p class="font-medium text-stone-800">{{ $milestone->title }}</p>
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
                                                    @can('update', $milestone)
                                                        <a wire:navigate.hover href="{{ route('supervisor.theses.show', [$thesis, 'edit' => $milestone->id]) }}" class="btn-secondary btn-sm">Edit</a>
                                                        <form method="POST" action="{{ route('supervisor.theses.milestones.destroy', [$thesis, $milestone]) }}" class="ml-1 inline" onsubmit="return confirm('Delete this milestone?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-secondary btn-sm text-red-700">Delete</button>
                                                        </form>
                                                    @elseif($milestone->status === \App\Enums\MilestoneStatus::Completed && $milestone->completed_at)
                                                        <span class="text-xs text-stone-500">Completed {{ $milestone->completed_at->format('M j, Y') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif(!$thesis->isActive())
                        <div class="card-body text-sm text-stone-500">
                            No milestones defined for this thesis.
                        </div>
                    @endif

                    @can('create', [\App\Models\Milestone::class, $thesis])
                        <div class="border-t border-stone-100 {{ $thesis->milestones->isNotEmpty() ? '' : 'card-body' }}">
                            <form method="POST" action="{{ route('supervisor.theses.milestones.store', $thesis) }}" class="space-y-4 {{ $thesis->milestones->isNotEmpty() ? 'px-6 py-4' : '' }}">
                                @csrf
                                <p class="text-sm font-medium text-stone-800">Add milestone</p>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="title" class="field-label">Title</label>
                                        <input type="text" name="title" id="title" value="{{ old('title') }}" required class="input-field @error('title') input-error @enderror" placeholder="e.g. Literature review draft">
                                        @error('title')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="due_date" class="field-label">Due date</label>
                                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" required class="input-field @error('due_date') input-error @enderror">
                                        @error('due_date')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="description" class="field-label">Description</label>
                                        <textarea name="description" id="description" rows="2" class="textarea-field @error('description') input-error @enderror" placeholder="Optional details for the student">{{ old('description') }}</textarea>
                                        @error('description')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <button type="submit" class="btn-primary btn-sm">Add milestone</button>
                            </form>
                        </div>
                    @elseif($thesis->milestones->isEmpty())
                        <div class="card-body text-sm text-stone-500">
                            Milestones can only be added while the thesis is active.
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
