@props([
    'thesis',
    'routePrefix',
])

@php
    $canManage = $routePrefix === 'supervisor';
@endphp

<x-milestone-timeline :thesis="$thesis" />

<div class="card overflow-hidden">
    <div class="card-section flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-stone-900">Milestones</h3>
            <p class="mt-0.5 text-sm text-stone-500">
                {{ $canManage ? 'Define deliverables, assign tasks, and set dependencies.' : 'Track progress on supervisor-defined deliverables and tasks.' }}
            </p>
        </div>
    </div>

    @if($thesis->milestones->isNotEmpty())
        <div class="divide-y divide-stone-100 border-t border-stone-100">
            @foreach($thesis->milestones as $milestone)
                <div class="px-6 py-5 {{ $milestone->isOverdue() ? 'bg-amber-50/40' : '' }}">
                    @if($canManage && (string) request('edit') === (string) $milestone->id)
                        <form method="POST" action="{{ route($routePrefix.'.theses.milestones.update', [$thesis, $milestone]) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="edit-title-{{ $milestone->id }}" class="field-label">Title</label>
                                    <input type="text" name="title" id="edit-title-{{ $milestone->id }}" value="{{ old('title', $milestone->title) }}" required class="input-field @error('title') input-error @enderror">
                                    @error('title')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="edit-due-{{ $milestone->id }}" class="field-label">Due date</label>
                                    <input type="date" name="due_date" id="edit-due-{{ $milestone->id }}" value="{{ old('due_date', $milestone->due_date->format('Y-m-d')) }}" required class="input-field @error('due_date') input-error @enderror">
                                    @error('due_date')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="edit-depends-{{ $milestone->id }}" class="field-label">Depends on</label>
                                    <select name="depends_on_id" id="edit-depends-{{ $milestone->id }}" class="input-field @error('depends_on_id') input-error @enderror">
                                        <option value="">None</option>
                                        @foreach($thesis->milestones->where('id', '!=', $milestone->id) as $candidate)
                                            <option value="{{ $candidate->id }}" @selected((string) old('depends_on_id', $milestone->depends_on_id) === (string) $candidate->id)>{{ $candidate->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('depends_on_id')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="edit-description-{{ $milestone->id }}" class="field-label">Description</label>
                                    <textarea name="description" id="edit-description-{{ $milestone->id }}" rows="2" class="textarea-field @error('description') input-error @enderror">{{ old('description', $milestone->description) }}</textarea>
                                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary btn-sm">Save changes</button>
                                <a wire:navigate.hover href="{{ route($routePrefix.'.theses.show', $thesis) }}" class="btn-secondary btn-sm">Cancel</a>
                            </div>
                        </form>
                    @else
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-stone-900">{{ $milestone->title }}</p>
                                    <x-milestone-status-badge :status="$milestone->status" :overdue="$milestone->isOverdue()" />
                                </div>
                                @if($milestone->description)
                                    <p class="mt-1 text-sm text-stone-500">{{ $milestone->description }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-500">
                                    <span>Due {{ $milestone->due_date->format('M j, Y') }}</span>
                                    @if($milestone->dependency)
                                        <span>Depends on: {{ $milestone->dependency->title }}</span>
                                    @endif
                                    @if($milestone->status === \App\Enums\MilestoneStatus::Completed && $milestone->completed_at)
                                        <span>Completed {{ $milestone->completed_at->format('M j, Y') }}</span>
                                    @endif
                                </div>
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-stone-500">
                                        <span>Progress</span>
                                        <span>{{ $milestone->progress_percentage }}%</span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-stone-100">
                                        <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $milestone->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-2">
                                @if($canManage)
                                    @can('update', $milestone)
                                        <a wire:navigate.hover href="{{ route($routePrefix.'.theses.show', [$thesis, 'edit' => $milestone->id]) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route($routePrefix.'.theses.milestones.destroy', [$thesis, $milestone]) }}" class="inline" onsubmit="return confirm('Delete this milestone?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary btn-sm text-red-700">Delete</button>
                                        </form>
                                    @endcan
                                @else
                                    @can('complete', $milestone)
                                        <form method="POST" action="{{ route('student.theses.milestones.complete', [$thesis, $milestone]) }}" class="inline" onsubmit="return confirm('Mark this milestone as complete?')">
                                            @csrf
                                            <button type="submit" class="btn-primary btn-sm">Mark complete</button>
                                        </form>
                                    @elseif(!$milestone->isDependencyMet())
                                        <span class="text-xs text-amber-700">Complete dependency first</span>
                                    @elseif($milestone->tasks->isNotEmpty() && $milestone->tasks->contains(fn ($task) => $task->status !== \App\Enums\MilestoneTaskStatus::Completed))
                                        <span class="text-xs text-amber-700">Finish all tasks first</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if($milestone->tasks->isNotEmpty())
                            <div class="mt-4 overflow-x-auto rounded border border-stone-100">
                                <table class="data-table text-sm">
                                    <thead class="table-head">
                                        <tr>
                                            <th class="px-4 py-2">Task</th>
                                            <th class="px-4 py-2">Priority</th>
                                            <th class="px-4 py-2">Status</th>
                                            <th class="px-4 py-2 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-100 bg-white">
                                        @foreach($milestone->tasks as $task)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <p class="font-medium text-stone-800">{{ $task->title }}</p>
                                                    @if($task->description)
                                                        <p class="mt-0.5 text-xs text-stone-500">{{ $task->description }}</p>
                                                    @endif
                                                    @if($task->due_date)
                                                        <p class="mt-0.5 text-xs text-stone-500">Due {{ $task->due_date->format('M j, Y') }}</p>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-stone-600">{{ $task->priority->label() }}</td>
                                                <td class="px-4 py-3">
                                                    <x-milestone-task-status-badge :status="$task->status" />
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    @if($canManage && (string) request('edit_task') === (string) $task->id)
                                                        <form method="POST" action="{{ route('supervisor.theses.milestones.tasks.update', [$thesis, $milestone, $task]) }}" class="space-y-2 text-left">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="text" name="title" value="{{ old('title', $task->title) }}" required class="input-field">
                                                            <select name="priority" class="input-field">
                                                                @foreach(\App\Enums\MilestoneTaskPriority::cases() as $priority)
                                                                    <option value="{{ $priority->value }}" @selected(old('priority', $task->priority->value) === $priority->value)>{{ $priority->label() }}</option>
                                                                @endforeach
                                                            </select>
                                                            <select name="status" class="input-field">
                                                                @foreach(\App\Enums\MilestoneTaskStatus::cases() as $taskStatus)
                                                                    <option value="{{ $taskStatus->value }}" @selected(old('status', $task->status->value) === $taskStatus->value)>{{ $taskStatus->label() }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="flex gap-2">
                                                                <button type="submit" class="btn-primary btn-sm">Save</button>
                                                                <a href="{{ route($routePrefix.'.theses.show', $thesis) }}" class="btn-secondary btn-sm">Cancel</a>
                                                            </div>
                                                        </form>
                                                    @elseif($canManage)
                                                        <a href="{{ route($routePrefix.'.theses.show', [$thesis, 'edit_task' => $task->id]) }}" class="btn-secondary btn-sm">Edit</a>
                                                        <form method="POST" action="{{ route('supervisor.theses.milestones.tasks.destroy', [$thesis, $milestone, $task]) }}" class="ml-1 inline" onsubmit="return confirm('Delete this task?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-secondary btn-sm text-red-700">Delete</button>
                                                        </form>
                                                    @elseif(auth()->user()->can('updateStatus', $task))
                                                        <form method="POST" action="{{ route('student.theses.milestones.tasks.update-status', [$thesis, $milestone, $task]) }}" class="inline-flex items-center gap-2">
                                                            @csrf
                                                            @method('PATCH')
                                                            <select name="status" class="input-field py-1 text-xs" onchange="this.form.submit()">
                                                                @foreach(\App\Enums\MilestoneTaskStatus::cases() as $taskStatus)
                                                                    <option value="{{ $taskStatus->value }}" @selected($task->status === $taskStatus)>{{ $taskStatus->label() }}</option>
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($canManage)
                            @can('create', [\App\Models\MilestoneTask::class, $milestone])
                                <details class="mt-4">
                                    <summary class="cursor-pointer text-sm font-medium text-navy-700">Add task</summary>
                                    <form method="POST" action="{{ route('supervisor.theses.milestones.tasks.store', [$thesis, $milestone]) }}" class="mt-3 space-y-3 rounded border border-stone-200 bg-stone-50 p-4">
                                        @csrf
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div class="sm:col-span-2">
                                                <label class="field-label">Task title</label>
                                                <input type="text" name="title" required class="input-field" placeholder="e.g. Draft introduction section">
                                            </div>
                                            <div>
                                                <label class="field-label">Priority</label>
                                                <select name="priority" class="input-field">
                                                    @foreach(\App\Enums\MilestoneTaskPriority::cases() as $priority)
                                                        <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="field-label">Due date (optional)</label>
                                                <input type="date" name="due_date" class="input-field">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="field-label">Description (optional)</label>
                                                <textarea name="description" rows="2" class="textarea-field"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn-primary btn-sm">Add task</button>
                                    </form>
                                </details>
                            @endcan
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @elseif(!$thesis->isActive())
        <div class="card-body text-sm text-stone-500">No milestones defined for this thesis.</div>
    @endif

    @if($canManage)
        @can('create', [\App\Models\Milestone::class, $thesis])
            <div class="border-t border-stone-100 {{ $thesis->milestones->isNotEmpty() ? 'px-6 py-4' : 'card-body' }}">
                <form method="POST" action="{{ route('supervisor.theses.milestones.store', $thesis) }}" class="space-y-4">
                    @csrf
                    <p class="text-sm font-medium text-stone-800">Add milestone</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="title" class="field-label">Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required class="input-field @error('title') input-error @enderror" placeholder="e.g. Literature review draft">
                            @error('title')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="due_date" class="field-label">Due date</label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" required class="input-field @error('due_date') input-error @enderror">
                            @error('due_date')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="depends_on_id" class="field-label">Depends on</label>
                            <select name="depends_on_id" id="depends_on_id" class="input-field @error('depends_on_id') input-error @enderror">
                                <option value="">None</option>
                                @foreach($thesis->milestones as $candidate)
                                    <option value="{{ $candidate->id }}" @selected((string) old('depends_on_id') === (string) $candidate->id)>{{ $candidate->title }}</option>
                                @endforeach
                            </select>
                            @error('depends_on_id')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="description" class="field-label">Description</label>
                            <textarea name="description" id="description" rows="2" class="textarea-field @error('description') input-error @enderror" placeholder="Optional details for the student">{{ old('description') }}</textarea>
                            @error('description')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Add milestone</button>
                </form>
            </div>
        @elseif($thesis->milestones->isEmpty())
            <div class="card-body text-sm text-stone-500">Milestones can only be added while the thesis is active.</div>
        @endif
    @elseif($thesis->milestones->isEmpty())
        <div class="card-body text-sm text-stone-500">No milestones yet. Your supervisor will add deliverables and due dates here.</div>
    @endif
</div>
