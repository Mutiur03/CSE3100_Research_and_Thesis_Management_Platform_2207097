<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="card-section">
            <h3 class="text-sm font-semibold text-stone-900">Proposal details</h3>
            <p class="mt-0.5 text-sm text-stone-500">Describe your research topic and intended approach.</p>
        </div>
        <div class="card-body space-y-5">
            <div>
                <label for="title" class="field-label">Title</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $proposal?->title) }}"
                    required
                    class="input-field @error('title') input-error @enderror"
                    placeholder="Research title"
                >
                @error('title')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="supervisor_id" class="field-label">Supervisor</label>
                <select name="supervisor_id" id="supervisor_id" required class="select-field @error('supervisor_id') input-error @enderror">
                    <option value="">Select a supervisor</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" {{ (string) old('supervisor_id', $proposal?->supervisor_id) === (string) $supervisor->id ? 'selected' : '' }}>
                            {{ $supervisor->name }} ({{ $supervisor->email }})
                        </option>
                    @endforeach
                </select>
                @error('supervisor_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="abstract" class="field-label">Abstract</label>
                <textarea
                    name="abstract"
                    id="abstract"
                    rows="5"
                    required
                    class="textarea-field @error('abstract') input-error @enderror"
                    placeholder="Summarize the research problem, significance, and expected contribution"
                >{{ old('abstract', $proposal?->abstract) }}</textarea>
                @error('abstract')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="objectives" class="field-label">Objectives</label>
                <textarea
                    name="objectives"
                    id="objectives"
                    rows="4"
                    class="textarea-field @error('objectives') input-error @enderror"
                    placeholder="List the main research objectives"
                >{{ old('objectives', $proposal?->objectives) }}</textarea>
                @error('objectives')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="methodology" class="field-label">Methodology</label>
                <textarea
                    name="methodology"
                    id="methodology"
                    rows="4"
                    class="textarea-field @error('methodology') input-error @enderror"
                    placeholder="Outline your proposed methods and tools"
                >{{ old('methodology', $proposal?->methodology) }}</textarea>
                @error('methodology')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a wire:navigate.hover href="{{ $proposal ? route('student.proposals.show', $proposal) : route('student.proposals.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">{{ $proposal ? 'Save changes' : 'Save draft' }}</button>
    </div>
</form>
