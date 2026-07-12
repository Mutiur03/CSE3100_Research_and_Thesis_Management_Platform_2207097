@props([
    'thesis',
    'routePrefix',
])

@php
    $canSchedule = auth()->user()->isSupervisor();
    $isSupervisor = $routePrefix === 'supervisor';
    $showScheduleForm = $errors->hasAny(['title', 'type', 'format', 'scheduled_at', 'duration_minutes', 'location', 'agenda', 'description']) && ! request('meeting');
    $googleConnected = $canSchedule && auth()->user()->hasGoogleCalendarConnected();
    $oldFormat = old('format', \App\Enums\MeetingFormat::Online->value);
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
            @if($googleConnected)
                <p class="mb-4 text-sm text-stone-600">
                    Google Calendar is connected — online meetings get a Google Meet link automatically.
                </p>
            @else
                <p class="mb-4 text-sm text-stone-600">
                    <a href="{{ route('profile.show') }}" class="font-medium text-navy-700 hover:text-navy-900">Connect Google Calendar</a>
                    in your profile to auto-create Meet links for online meetings.
                </p>
            @endif
            <form method="POST" action="{{ route($routePrefix.'.theses.meetings.store', $thesis) }}" class="space-y-4" data-meeting-form>
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="meeting-title" class="field-label">Title</label>
                        <input type="text" name="title" id="meeting-title" value="{{ old('title') }}" required class="input-field @error('title') input-error @enderror" placeholder="e.g. Weekly supervision check-in…">
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
                        <fieldset>
                            <legend class="field-label">Format</legend>
                            <div class="mt-2 grid grid-cols-2 gap-3">
                                @foreach(\App\Enums\MeetingFormat::cases() as $formatOption)
                                    <label class="flex cursor-pointer flex-col rounded border border-stone-300 p-3 transition-colors has-[:checked]:border-navy-700 has-[:checked]:bg-navy-50">
                                        <input
                                            type="radio"
                                            name="format"
                                            value="{{ $formatOption->value }}"
                                            class="sr-only"
                                            data-meeting-format
                                            @checked($oldFormat === $formatOption->value)
                                            required
                                        >
                                        <span class="text-sm font-medium text-stone-800">{{ $formatOption->label() }}</span>
                                        <span class="mt-0.5 text-xs text-stone-500">
                                            {{ $formatOption === \App\Enums\MeetingFormat::Online ? 'Video call via Google Meet' : 'Requires a physical location' }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        @error('format')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="meeting-scheduled-at" class="field-label">Date & time <span class="font-normal text-stone-400">(Asia/Dhaka)</span></label>
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
                    <div class="sm:col-span-2" data-location-field @if($oldFormat !== \App\Enums\MeetingFormat::InPerson->value) hidden @endif>
                        <label for="meeting-location" class="field-label">Location</label>
                        <input
                            type="text"
                            name="location"
                            id="meeting-location"
                            value="{{ old('location') }}"
                            class="input-field @error('location') input-error @enderror"
                            placeholder="e.g. Room 204, CSE Building…"
                            @if($oldFormat === \App\Enums\MeetingFormat::InPerson->value) required @endif
                            data-location-input
                        >
                        @error('location')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="meeting-agenda" class="field-label">Agenda <span class="font-normal text-stone-400">(optional)</span></label>
                        <textarea name="agenda" id="meeting-agenda" rows="3" class="textarea-field @error('agenda') input-error @enderror" placeholder="Topics to cover in this meeting…">{{ old('agenda') }}</textarea>
                        @error('agenda')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="meeting-description" class="field-label">Notes <span class="font-normal text-stone-400">(optional)</span></label>
                        <textarea name="description" id="meeting-description" rows="2" class="textarea-field @error('description') input-error @enderror" placeholder="Additional context…">{{ old('description') }}</textarea>
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
                    $showEditForm = request('meeting') == $meeting->id || ($errors->any() && old('_meeting_id') == $meeting->id);
                    $editFormat = old('_meeting_id') == $meeting->id
                        ? old('format', $meeting->format->value)
                        : $meeting->format->value;
                @endphp
                <div class="px-6 py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-semibold text-stone-900">{{ $meeting->title }}</h4>
                                <x-meeting-type-badge :type="$meeting->type" />
                                <span class="inline-flex items-center rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-600 ring-1 ring-stone-200">
                                    {{ $meeting->format->label() }}
                                </span>
                            </div>
                            <p class="text-sm text-stone-600">
                                {{ $meeting->scheduled_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                                · {{ $meeting->duration_minutes }} min
                                @if($meeting->format->requiresLocation() && $meeting->location)
                                    · {{ $meeting->location }}
                                @endif
                            </p>
                            @if($meeting->format === \App\Enums\MeetingFormat::Online && $meeting->meeting_link)
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
                                        <span class="inline-flex items-center rounded-full bg-stone-100 px-2.5 py-1 text-xs text-stone-700">
                                            {{ $attendee->user->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            @if($isSupervisor)
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

                    @if($isSupervisor)
                        <div id="edit-meeting-{{ $meeting->id }}" class="{{ $showEditForm ? '' : 'hidden' }} mt-4 border-t border-stone-100 pt-4">
                            <form method="POST" action="{{ route('supervisor.theses.meetings.update', [$thesis, $meeting]) }}" class="space-y-4" data-meeting-form>
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
                                        <fieldset>
                                            <legend class="field-label">Format</legend>
                                            <div class="mt-2 grid grid-cols-2 gap-3">
                                                @foreach(\App\Enums\MeetingFormat::cases() as $formatOption)
                                                    <label class="flex cursor-pointer flex-col rounded border border-stone-300 p-3 transition-colors has-[:checked]:border-navy-700 has-[:checked]:bg-navy-50">
                                                        <input
                                                            type="radio"
                                                            name="format"
                                                            value="{{ $formatOption->value }}"
                                                            class="sr-only"
                                                            data-meeting-format
                                                            @checked($editFormat === $formatOption->value)
                                                            required
                                                        >
                                                        <span class="text-sm font-medium text-stone-800">{{ $formatOption->label() }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div>
                                        <label for="edit-scheduled-at-{{ $meeting->id }}" class="field-label">Date & time <span class="font-normal text-stone-400">(Asia/Dhaka)</span></label>
                                        <input type="datetime-local" name="scheduled_at" id="edit-scheduled-at-{{ $meeting->id }}" value="{{ old('scheduled_at', $meeting->scheduled_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i')) }}" required class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit-duration-{{ $meeting->id }}" class="field-label">Duration (minutes)</label>
                                        <input type="number" name="duration_minutes" id="edit-duration-{{ $meeting->id }}" value="{{ old('duration_minutes', $meeting->duration_minutes) }}" min="15" max="480" class="input-field">
                                    </div>
                                    <div class="sm:col-span-2" data-location-field @if($editFormat !== \App\Enums\MeetingFormat::InPerson->value) hidden @endif>
                                        <label for="edit-location-{{ $meeting->id }}" class="field-label">Location</label>
                                        <input
                                            type="text"
                                            name="location"
                                            id="edit-location-{{ $meeting->id }}"
                                            value="{{ old('location', $meeting->location) }}"
                                            class="input-field"
                                            placeholder="e.g. Room 204, CSE Building…"
                                            @if($editFormat === \App\Enums\MeetingFormat::InPerson->value) required @endif
                                            data-location-input
                                        >
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="edit-agenda-{{ $meeting->id }}" class="field-label">Agenda</label>
                                        <textarea name="agenda" id="edit-agenda-{{ $meeting->id }}" rows="3" class="textarea-field">{{ old('agenda', $meeting->agenda) }}</textarea>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="edit-minutes-{{ $meeting->id }}" class="field-label">Minutes</label>
                                        <textarea name="minutes" id="edit-minutes-{{ $meeting->id }}" rows="4" class="textarea-field" placeholder="Record outcomes and action items…">{{ old('minutes', $meeting->minutes) }}</textarea>
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

@once
    @push('scripts')
        <script>
            document.addEventListener('change', function (e) {
                const input = e.target.closest('[data-meeting-format]');
                if (!input) return;

                const form = input.closest('[data-meeting-form]');
                if (!form) return;

                const locationField = form.querySelector('[data-location-field]');
                const locationInput = form.querySelector('[data-location-input]');
                const isInPerson = input.value === @json(\App\Enums\MeetingFormat::InPerson->value) && input.checked;

                if (!locationField || !locationInput) return;

                locationField.hidden = !isInPerson;
                locationInput.required = isInPerson;
                if (!isInPerson) {
                    locationInput.value = '';
                }
            });
        </script>
    @endpush
@endonce
