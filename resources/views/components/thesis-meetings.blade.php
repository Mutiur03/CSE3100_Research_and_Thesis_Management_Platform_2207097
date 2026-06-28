@props([
    'thesis',
    'routePrefix',
])

@php
    $canSchedule = auth()->user()->can('create', [\App\Models\Meeting::class, $thesis]);
    $isSupervisor = $routePrefix === 'supervisor';
    $showScheduleForm = $errors->hasAny(['title', 'type', 'scheduled_at', 'duration_minutes', 'location', 'meeting_link', 'agenda', 'description']) && ! request('meeting');
@endphp

<div class="card overflow-hidden">
    <div class="card-section flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-stone-900">Meetings</h3>
            <p class="mt-0.5 text-sm text-stone-500">Schedule supervision sessions and record agendas and minutes.</p>
        </div>
        @if($canSchedule)
            <button type="button" class="btn-primary btn-sm" onclick="document.getElementById('schedule-meeting-form').classList.toggle('hidden')">
                Schedule meeting
            </button>
        @endif
    </div>

    @if($canSchedule)
        <div id="schedule-meeting-form" class="{{ $showScheduleForm ? '' : 'hidden' }} border-t border-stone-100 bg-stone-50 px-6 py-5">
            <form method="POST" action="{{ route($routePrefix.'.theses.meetings.store', $thesis) }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="meeting-title" class="field-label">Title</label>
                        <input type="text" name="title" id="meeting-title" value="{{ old('title') }}" required class="input-field @error('title') input-error @enderror" placeholder="e.g. Weekly supervision check-in">
                        @error('title')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="meeting-type" class="field-label">Type</label>
                        <select name="type" id="meeting-type" required class="input-field @error('type') input-error @enderror">
                            @foreach(\App\Enums\MeetingType::cases() as $typeOption)
                                <option value="{{ $typeOption->value }}" @selected(old('type') === $typeOption->value)>{{ $typeOption->label() }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="meeting-scheduled-at" class="field-label">Date & time</label>
                        <input type="datetime-local" name="scheduled_at" id="meeting-scheduled-at" value="{{ old('scheduled_at') }}" required class="input-field @error('scheduled_at') input-error @enderror">
                        @error('scheduled_at')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="meeting-duration" class="field-label">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" id="meeting-duration" value="{{ old('duration_minutes', 60) }}" min="15" max="480" class="input-field @error('duration_minutes') input-error @enderror">
                        @error('duration_minutes')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="meeting-location" class="field-label">Location <span class="font-normal text-stone-400">(optional)</span></label>
                        <input type="text" name="location" id="meeting-location" value="{{ old('location') }}" class="input-field @error('location') input-error @enderror" placeholder="Room 204 or Online">
                        @error('location')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="meeting-link" class="field-label">Video link <span class="font-normal text-stone-400">(optional)</span></label>
                        <input type="url" name="meeting_link" id="meeting-link" value="{{ old('meeting_link') }}" class="input-field @error('meeting_link') input-error @enderror" placeholder="https://zoom.us/j/...">
                        @error('meeting_link')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="meeting-agenda" class="field-label">Agenda <span class="font-normal text-stone-400">(optional)</span></label>
                        <textarea name="agenda" id="meeting-agenda" rows="3" class="textarea-field @error('agenda') input-error @enderror" placeholder="Topics to cover in this meeting">{{ old('agenda') }}</textarea>
                        @error('agenda')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="meeting-description" class="field-label">Notes <span class="font-normal text-stone-400">(optional)</span></label>
                        <textarea name="description" id="meeting-description" rows="2" class="textarea-field @error('description') input-error @enderror" placeholder="Additional context">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary btn-sm">Schedule</button>
                    <button type="button" class="btn-secondary btn-sm" onclick="document.getElementById('schedule-meeting-form').classList.add('hidden')">Cancel</button>
                </div>
            </form>
        </div>
    @endif

    @if($thesis->meetings->isEmpty())
        <div class="card-body text-sm text-stone-500">
            No meetings scheduled yet.
        </div>
    @else
        <div class="divide-y divide-stone-100">
            @foreach($thesis->meetings as $meeting)
                @php
                    $myAttendee = $meeting->attendees->firstWhere('user_id', auth()->id());
                    $showEditForm = request('meeting') == $meeting->id || ($errors->any() && old('_meeting_id') == $meeting->id);
                @endphp
                <div class="px-6 py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-semibold text-stone-900">{{ $meeting->title }}</h4>
                                <x-meeting-type-badge :type="$meeting->type" />
                                <x-meeting-status-badge :status="$meeting->status" />
                            </div>
                            <p class="text-sm text-stone-600">
                                {{ $meeting->scheduled_at->format('M j, Y g:i A') }}
                                · {{ $meeting->duration_minutes }} min
                                @if($meeting->location)
                                    · {{ $meeting->location }}
                                @endif
                            </p>
                            @if($meeting->meeting_link)
                                <p class="text-sm">
                                    <a href="{{ $meeting->meeting_link }}" target="_blank" rel="noopener noreferrer" class="font-medium text-navy-700 hover:text-navy-900">
                                        Join video call →
                                    </a>
                                </p>
                            @endif
                            @if($meeting->agenda)
                                <div class="rounded-lg bg-stone-50 px-4 py-3 text-sm text-stone-700">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Agenda</p>
                                    <p class="mt-1 whitespace-pre-wrap">{{ $meeting->agenda }}</p>
                                </div>
                            @endif
                            @if($meeting->minutes)
                                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-stone-700">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Minutes</p>
                                    <p class="mt-1 whitespace-pre-wrap">{{ $meeting->minutes }}</p>
                                </div>
                            @endif
                            @if($meeting->attendees->isNotEmpty())
                                <div class="flex flex-wrap gap-2 pt-1">
                                    @foreach($meeting->attendees as $attendee)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2.5 py-1 text-xs text-stone-700">
                                            {{ $attendee->user->name }}
                                            <span class="font-medium text-stone-500">· {{ $attendee->rsvp_status->label() }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            @if($isSupervisor && auth()->user()->can('update', $meeting))
                                <button type="button" class="btn-secondary btn-sm" onclick="document.getElementById('edit-meeting-{{ $meeting->id }}').classList.toggle('hidden')">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('supervisor.theses.meetings.destroy', [$thesis, $meeting]) }}" onsubmit="return confirm('Delete this meeting?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary btn-sm text-rose-700 hover:bg-rose-50">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if(!$isSupervisor && $myAttendee && auth()->user()->can('respond', $meeting))
                        <form method="POST" action="{{ route('student.theses.meetings.rsvp', [$thesis, $meeting]) }}" class="mt-4 flex flex-wrap items-end gap-3 border-t border-stone-100 pt-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="rsvp-{{ $meeting->id }}" class="field-label">Your RSVP</label>
                                <select name="rsvp_status" id="rsvp-{{ $meeting->id }}" class="input-field">
                                    @foreach(\App\Enums\MeetingRsvpStatus::cases() as $rsvpOption)
                                        <option value="{{ $rsvpOption->value }}" @selected($myAttendee->rsvp_status === $rsvpOption)>{{ $rsvpOption->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn-primary btn-sm">Update RSVP</button>
                        </form>
                    @endif

                    @if($isSupervisor && auth()->user()->can('update', $meeting))
                        <div id="edit-meeting-{{ $meeting->id }}" class="{{ $showEditForm ? '' : 'hidden' }} mt-4 border-t border-stone-100 pt-4">
                            <form method="POST" action="{{ route('supervisor.theses.meetings.update', [$thesis, $meeting]) }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_meeting_id" value="{{ $meeting->id }}">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="edit-title-{{ $meeting->id }}" class="field-label">Title</label>
                                        <input type="text" name="title" id="edit-title-{{ $meeting->id }}" value="{{ old('title', $meeting->title) }}" required class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit-type-{{ $meeting->id }}" class="field-label">Type</label>
                                        <select name="type" id="edit-type-{{ $meeting->id }}" required class="input-field">
                                            @foreach(\App\Enums\MeetingType::cases() as $typeOption)
                                                <option value="{{ $typeOption->value }}" @selected(old('type', $meeting->type->value) === $typeOption->value)>{{ $typeOption->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="edit-status-{{ $meeting->id }}" class="field-label">Status</label>
                                        <select name="status" id="edit-status-{{ $meeting->id }}" required class="input-field">
                                            @foreach(\App\Enums\MeetingStatus::cases() as $statusOption)
                                                <option value="{{ $statusOption->value }}" @selected(old('status', $meeting->status->value) === $statusOption->value)>{{ $statusOption->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="edit-scheduled-at-{{ $meeting->id }}" class="field-label">Date & time</label>
                                        <input type="datetime-local" name="scheduled_at" id="edit-scheduled-at-{{ $meeting->id }}" value="{{ old('scheduled_at', $meeting->scheduled_at->format('Y-m-d\TH:i')) }}" required class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit-duration-{{ $meeting->id }}" class="field-label">Duration (minutes)</label>
                                        <input type="number" name="duration_minutes" id="edit-duration-{{ $meeting->id }}" value="{{ old('duration_minutes', $meeting->duration_minutes) }}" min="15" max="480" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit-location-{{ $meeting->id }}" class="field-label">Location</label>
                                        <input type="text" name="location" id="edit-location-{{ $meeting->id }}" value="{{ old('location', $meeting->location) }}" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit-link-{{ $meeting->id }}" class="field-label">Video link</label>
                                        <input type="url" name="meeting_link" id="edit-link-{{ $meeting->id }}" value="{{ old('meeting_link', $meeting->meeting_link) }}" class="input-field">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="edit-agenda-{{ $meeting->id }}" class="field-label">Agenda</label>
                                        <textarea name="agenda" id="edit-agenda-{{ $meeting->id }}" rows="3" class="textarea-field">{{ old('agenda', $meeting->agenda) }}</textarea>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="edit-minutes-{{ $meeting->id }}" class="field-label">Minutes</label>
                                        <textarea name="minutes" id="edit-minutes-{{ $meeting->id }}" rows="4" class="textarea-field" placeholder="Record outcomes and action items">{{ old('minutes', $meeting->minutes) }}</textarea>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="edit-description-{{ $meeting->id }}" class="field-label">Notes</label>
                                        <textarea name="description" id="edit-description-{{ $meeting->id }}" rows="2" class="textarea-field">{{ old('description', $meeting->description) }}</textarea>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" class="btn-primary btn-sm">Save changes</button>
                                    <button type="button" class="btn-secondary btn-sm" onclick="document.getElementById('edit-meeting-{{ $meeting->id }}').classList.add('hidden')">Cancel</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
