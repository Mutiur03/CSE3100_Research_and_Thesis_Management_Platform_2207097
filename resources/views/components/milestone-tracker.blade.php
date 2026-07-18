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
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-stone-900">Milestones</h3>
            <p class="mt-0.5 text-sm text-stone-500">
                {{ $canManage ? 'Define deliverables, assign tasks, and set dependencies.' : 'Track progress on supervisor-defined deliverables and tasks.' }}
            </p>
        </div>
    </div>

    @if($thesis->milestones->isNotEmpty())
        <div class="divide-y divide-stone-100 border-t border-stone-100">
            @foreach($thesis->milestones as $milestone)
                @php
                    $isEditing = $canManage && (string) request('edit') === (string) $milestone->id;
                    $isEditingTask = $canManage && $milestone->tasks->contains(fn ($task) => (string) request('edit_task') === (string) $task->id);
                    $milestoneOpen = $isEditing || $isEditingTask;
                @endphp
                <details class="group {{ $milestone->isOverdue() ? 'bg-amber-50/40' : '' }}" @if($milestoneOpen) open @endif>
                    <summary class="disclosure-summary flex cursor-pointer list-none items-center gap-3 px-6 py-4 marker:content-none [&::-webkit-details-marker]:hidden">
                        <svg class="disclosure-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-medium text-stone-900" title="{{ $milestone->title }}">{{ $milestone->title }}</p>
                                <x-milestone-status-badge :status="$milestone->status" :overdue="$milestone->isOverdue()" />
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-xs tabular-nums text-stone-500">
                                <span>Due {{ $milestone->due_date->format('M j, Y') }}</span>
                                <span>{{ $milestone->progress_percentage }}% · {{ $milestone->tasks->count() }} {{ Str::plural('task', $milestone->tasks->count()) }}</span>
                            </div>
                        </div>
                        <div
                            class="hidden h-1.5 w-16 shrink-0 overflow-hidden rounded-full bg-stone-100 sm:block"
                            role="progressbar"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="{{ $milestone->progress_percentage }}"
                            aria-label="{{ $milestone->title }} progress"
                        >
                            <div class="h-full rounded-full bg-brand-600 transition-[width] duration-300 motion-reduce:transition-none" style="width: {{ $milestone->progress_percentage }}%"></div>
                        </div>
                    </summary>

                    <div class="border-t border-stone-100 px-6 py-5">
                        @if($isEditing)
                            <form method="POST" action="{{ route($routePrefix.'.theses.milestones.update', [$thesis, $milestone]) }}" class="space-y-4" autocomplete="off">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="edit-title-{{ $milestone->id }}" class="field-label">Title</label>
                                        <input type="text" name="title" id="edit-title-{{ $milestone->id }}" value="{{ old('title', $milestone->title) }}" required maxlength="255" autocomplete="off" class="input-field @error('title') input-error @enderror">
                                        @error('title')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="edit-due-{{ $milestone->id }}" class="field-label">Due date</label>
                                        <input type="date" name="due_date" id="edit-due-{{ $milestone->id }}" value="{{ old('due_date', $milestone->due_date->format('Y-m-d')) }}" required min="{{ $milestone->due_date->isPast() ? $milestone->due_date->format('Y-m-d') : now()->toDateString() }}" class="input-field @error('due_date') input-error @enderror">
                                        @error('due_date')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="edit-depends-{{ $milestone->id }}" class="field-label">Depends on</label>
                                        <select name="depends_on_id" id="edit-depends-{{ $milestone->id }}" class="input-field @error('depends_on_id') input-error @enderror">
                                            <option value="">None</option>
                                            @foreach($thesis->milestones->where('id', '!=', $milestone->id) as $candidate)
                                                <option value="{{ $candidate->id }}" @selected((string) old('depends_on_id', $milestone->depends_on_id) === (string) $candidate->id)>{{ $candidate->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('depends_on_id')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="edit-description-{{ $milestone->id }}" class="field-label">Description</label>
                                        <textarea name="description" id="edit-description-{{ $milestone->id }}" rows="2" maxlength="2000" class="textarea-field @error('description') input-error @enderror">{{ old('description', $milestone->description) }}</textarea>
                                        @error('description')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="btn-primary btn-sm">Save Changes</button>
                                    <a wire:navigate.hover href="{{ route($routePrefix.'.theses.show', $thesis) }}" class="btn-secondary btn-sm">Cancel</a>
                                </div>
                            </form>
                        @else
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    @if($milestone->description)
                                        <p class="break-words text-sm text-stone-500">{{ $milestone->description }}</p>
                                    @endif
                                    <div class="{{ $milestone->description ? 'mt-2' : '' }} flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-500">
                                        @if($milestone->dependency)
                                            <span class="break-words">Depends on: {{ $milestone->dependency->title }}</span>
                                        @endif
                                        @if($milestone->status === \App\Enums\MilestoneStatus::Completed && $milestone->completed_at)
                                            <span>Completed {{ $milestone->completed_at->format('M j, Y') }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-xs tabular-nums text-stone-500">
                                            <span>Progress</span>
                                            <span>{{ $milestone->progress_percentage }}%</span>
                                        </div>
                                        <div
                                            class="mt-1 h-2 overflow-hidden rounded-full bg-stone-100"
                                            role="progressbar"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            aria-valuenow="{{ $milestone->progress_percentage }}"
                                            aria-label="{{ $milestone->title }} progress"
                                        >
                                            <div class="h-full rounded-full bg-brand-600 transition-[width] duration-300 motion-reduce:transition-none" style="width: {{ $milestone->progress_percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex shrink-0 flex-wrap gap-2">
                                    @if($canManage)
                                        @if(auth()->user()->isSupervisor())
                                            <a wire:navigate.hover href="{{ route($routePrefix.'.theses.show', [$thesis, 'edit' => $milestone->id]) }}" class="btn-secondary btn-sm">Edit</a>
                                            <form method="POST" action="{{ route($routePrefix.'.theses.milestones.destroy', [$thesis, $milestone]) }}" class="inline" onsubmit="return confirm('Delete this milestone?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-secondary btn-sm text-red-700">Delete</button>
                                            </form>
                                        @endif
                                    @else
                                        @if($milestone->tasks->isNotEmpty())
                                            @if(!$milestone->isDependencyMet())
                                                <span class="text-xs text-amber-700">Complete dependency first</span>
                                            @elseif($milestone->tasks->contains(fn ($task) => $task->status !== \App\Enums\MilestoneTaskStatus::Completed))
                                                <span class="text-xs text-stone-500">Completes when all tasks are done</span>
                                            @endif
                                        @elseif($milestone->isCompletable())
                                            <form method="POST" action="{{ route('student.theses.milestones.complete', [$thesis, $milestone]) }}" class="inline" onsubmit="return confirm('Mark this milestone as complete?')">
                                                @csrf
                                                <button type="submit" class="btn-primary btn-sm">Mark Complete</button>
                                            </form>
                                        @elseif(!$milestone->isDependencyMet())
                                            <span class="text-xs text-amber-700">Complete dependency first</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            @if($milestone->tasks->isNotEmpty())
                                <div class="mt-4 overflow-x-auto rounded border border-stone-100">
                                    <table class="data-table text-sm">
                                        <thead class="table-head">
                                            <tr>
                                                <th scope="col" class="px-4 py-2">Task</th>
                                                <th scope="col" class="px-4 py-2">Priority</th>
                                                <th scope="col" class="px-4 py-2">Status</th>
                                                <th scope="col" class="px-4 py-2 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-stone-100 bg-white">
                                            @foreach($milestone->tasks as $task)
                                                @if($canManage && (string) request('edit_task') === (string) $task->id)
                                                    <tr class="bg-brand-50/40">
                                                        <td colspan="4" class="px-4 py-4">
                                                            <form method="POST" action="{{ route('supervisor.theses.milestones.tasks.update', [$thesis, $milestone, $task]) }}" class="space-y-4" autocomplete="off">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="grid gap-4 sm:grid-cols-2">
                                                                    <div class="sm:col-span-2">
                                                                        <label for="edit-task-title-{{ $task->id }}" class="field-label">Title</label>
                                                                        <input type="text" name="title" id="edit-task-title-{{ $task->id }}" value="{{ old('title', $task->title) }}" required maxlength="255" autocomplete="off" class="input-field @error('title') input-error @enderror">
                                                                        @error('title')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                                                    </div>
                                                                    <div class="sm:col-span-2">
                                                                        <label for="edit-task-description-{{ $task->id }}" class="field-label">Description <span class="font-normal text-stone-400">(optional)</span></label>
                                                                        <textarea name="description" id="edit-task-description-{{ $task->id }}" rows="2" maxlength="2000" class="textarea-field @error('description') input-error @enderror">{{ old('description', $task->description) }}</textarea>
                                                                        @error('description')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                                                    </div>
                                                                    <div>
                                                                        <label for="edit-task-priority-{{ $task->id }}" class="field-label">Priority</label>
                                                                        <select name="priority" id="edit-task-priority-{{ $task->id }}" required class="input-field @error('priority') input-error @enderror">
                                                                            @foreach(\App\Enums\MilestoneTaskPriority::cases() as $priority)
                                                                                <option value="{{ $priority->value }}" @selected(old('priority', $task->priority->value) === $priority->value)>{{ $priority->label() }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('priority')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                                                    </div>
                                                                    <div>
                                                                        <label for="edit-task-status-{{ $task->id }}" class="field-label">Status</label>
                                                                        <select name="status" id="edit-task-status-{{ $task->id }}" required class="input-field @error('status') input-error @enderror">
                                                                            @foreach(\App\Enums\MilestoneTaskStatus::cases() as $taskStatus)
                                                                                <option value="{{ $taskStatus->value }}" @selected(old('status', $task->status->value) === $taskStatus->value)>{{ $taskStatus->label() }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('status')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                                                    </div>
                                                                    <div class="sm:col-span-2">
                                                                        <label for="edit-task-due-{{ $task->id }}" class="field-label">Due date <span class="font-normal text-stone-400">(optional)</span></label>
                                                                        <input type="date" name="due_date" id="edit-task-due-{{ $task->id }}" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" min="{{ ($task->due_date && $task->due_date->isPast()) ? $task->due_date->format('Y-m-d') : now()->toDateString() }}" class="input-field @error('due_date') input-error @enderror">
                                                                        @error('due_date')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                                                                    </div>
                                                                </div>
                                                                <div class="flex flex-wrap gap-2">
                                                                    <button type="submit" class="btn-primary btn-sm">Save Changes</button>
                                                                    <a wire:navigate.hover href="{{ route($routePrefix.'.theses.show', $thesis) }}" class="btn-secondary btn-sm">Cancel</a>
                                                                </div>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td class="px-4 py-3 align-top">
                                                            <p class="break-words font-medium text-stone-800">{{ $task->title }}</p>
                                                            @if($task->description)
                                                                <p class="mt-0.5 break-words text-xs text-stone-500">{{ $task->description }}</p>
                                                            @endif
                                                            @if($task->due_date)
                                                                <p class="mt-0.5 text-xs tabular-nums text-stone-500">Due {{ $task->due_date->format('M j, Y') }}</p>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 align-top text-stone-600">{{ $task->priority->label() }}</td>
                                                        <td class="px-4 py-3 align-top">
                                                            <x-milestone-task-status-badge :status="$task->status" />
                                                        </td>
                                                        <td class="px-4 py-3 align-top text-right">
                                                            @if($canManage)
                                                                <div class="flex justify-end gap-1">
                                                                    <a wire:navigate.hover href="{{ route($routePrefix.'.theses.show', [$thesis, 'edit_task' => $task->id]) }}" class="btn-secondary btn-sm">Edit</a>
                                                                    <form method="POST" action="{{ route('supervisor.theses.milestones.tasks.destroy', [$thesis, $milestone, $task]) }}" class="inline" onsubmit="return confirm('Delete this task?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn-secondary btn-sm text-red-700">Delete</button>
                                                                    </form>
                                                                </div>
                                                            @elseif(auth()->user()->isStudent())
                                                                <form method="POST" action="{{ route('student.theses.milestones.tasks.update-status', [$thesis, $milestone, $task]) }}" class="inline-flex items-center gap-2">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <label class="sr-only" for="task-status-{{ $task->id }}">Status for {{ $task->title }}</label>
                                                                    <select name="status" id="task-status-{{ $task->id }}" class="input-field py-1 text-xs" onchange="this.form.submit()">
                                                                        @foreach(\App\Enums\MilestoneTaskStatus::cases() as $taskStatus)
                                                                            <option value="{{ $taskStatus->value }}" @selected($task->status === $taskStatus)>{{ $taskStatus->label() }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if($canManage)
                                @if(auth()->user()->isSupervisor())
                                    <details class="group mt-4">
                                        <summary class="disclosure-summary flex cursor-pointer list-none items-center gap-2 text-sm font-medium text-navy-700 marker:content-none [&::-webkit-details-marker]:hidden">
                                            <svg class="disclosure-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                            Add Task
                                        </summary>
                                        <form method="POST" action="{{ route('supervisor.theses.milestones.tasks.store', [$thesis, $milestone]) }}" class="mt-3 space-y-3 rounded border border-stone-200 bg-stone-50 p-4" autocomplete="off">
                                            @csrf
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div class="sm:col-span-2">
                                                    <label for="add-task-title-{{ $milestone->id }}" class="field-label">Task title</label>
                                                    <input type="text" name="title" id="add-task-title-{{ $milestone->id }}" required maxlength="255" autocomplete="off" class="input-field" placeholder="e.g. Draft introduction section.">
                                                </div>
                                                <div>
                                                    <label for="add-task-priority-{{ $milestone->id }}" class="field-label">Priority</label>
                                                    <select name="priority" id="add-task-priority-{{ $milestone->id }}" required class="input-field">
                                                        @foreach(\App\Enums\MilestoneTaskPriority::cases() as $priority)
                                                            <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="add-task-due-{{ $milestone->id }}" class="field-label">Due date <span class="font-normal text-stone-400">(optional)</span></label>
                                                    <input type="date" name="due_date" id="add-task-due-{{ $milestone->id }}" min="{{ now()->toDateString() }}" class="input-field">
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <label for="add-task-description-{{ $milestone->id }}" class="field-label">Description <span class="font-normal text-stone-400">(optional)</span></label>
                                                    <textarea name="description" id="add-task-description-{{ $milestone->id }}" rows="2" maxlength="2000" class="textarea-field"></textarea>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn-primary btn-sm">Add Task</button>
                                        </form>
                                    </details>
                                @endif
                            @endif
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    @elseif(!$thesis->isActive())
        <div class="card-body text-sm text-stone-500">No milestones defined for this thesis.</div>
    @endif

    @if($canManage)
        @if(auth()->user()->isSupervisor())
            @php
                $addMilestoneOpen = $thesis->milestones->isEmpty()
                    || (! request()->has('edit') && ! request()->has('edit_task') && $errors->hasAny(['title', 'due_date', 'depends_on_id', 'description']));
            @endphp
            <details class="group border-t border-stone-100" @if($addMilestoneOpen) open @endif>
                <summary class="disclosure-summary flex cursor-pointer list-none items-center gap-2 {{ $thesis->milestones->isNotEmpty() ? 'px-6 py-4' : 'card-body' }} marker:content-none [&::-webkit-details-marker]:hidden">
                    <svg class="disclosure-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span class="text-sm font-medium text-navy-700">Add Milestone</span>
                </summary>
                <div class="{{ $thesis->milestones->isNotEmpty() ? 'px-6 pb-4' : 'px-6 pb-6' }}">
                    <form method="POST" action="{{ route('supervisor.theses.milestones.store', $thesis) }}" class="space-y-4 rounded border border-stone-200 bg-stone-50 p-4" autocomplete="off">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="title" class="field-label">Title</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255" autocomplete="off" class="input-field @error('title') input-error @enderror" placeholder="e.g. Literature review draft.">
                                @error('title')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="due_date" class="field-label">Due date</label>
                                <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" required min="{{ now()->toDateString() }}" class="input-field @error('due_date') input-error @enderror">
                                @error('due_date')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="depends_on_id" class="field-label">Depends on</label>
                                <select name="depends_on_id" id="depends_on_id" class="input-field @error('depends_on_id') input-error @enderror">
                                    <option value="">None</option>
                                    @foreach($thesis->milestones as $candidate)
                                        <option value="{{ $candidate->id }}" @selected((string) old('depends_on_id') === (string) $candidate->id)>{{ $candidate->title }}</option>
                                    @endforeach
                                </select>
                                @error('depends_on_id')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="description" class="field-label">Description</label>
                                <textarea name="description" id="description" rows="2" maxlength="2000" class="textarea-field @error('description') input-error @enderror" placeholder="Optional details for the student.">{{ old('description') }}</textarea>
                                @error('description')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <button type="submit" class="btn-primary btn-sm">Add Milestone</button>
                    </form>
                </div>
            </details>
        @elseif($thesis->milestones->isEmpty())
            <div class="card-body text-sm text-stone-500">Milestones can only be added while the thesis is active.</div>
        @endif
    @elseif($thesis->milestones->isEmpty())
        <div class="card-body text-sm text-stone-500">No milestones yet. Your supervisor will add deliverables and due dates here.</div>
    @endif
</div>
