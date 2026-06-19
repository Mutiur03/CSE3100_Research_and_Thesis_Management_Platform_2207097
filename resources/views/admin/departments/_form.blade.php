<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="card-section">
            <h3 class="text-sm font-semibold text-stone-900">Department details</h3>
        </div>
        <div class="card-body space-y-5">
            <div>
                <label for="name" class="field-label">Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $department?->name) }}"
                    required
                    class="input-field @error('name') input-error @enderror"
                    placeholder="Computer Science & Engineering"
                >
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="code" class="field-label">Code</label>
                <input
                    type="text"
                    name="code"
                    id="code"
                    value="{{ old('code', $department?->code) }}"
                    required
                    maxlength="20"
                    class="input-field @error('code') input-error @enderror"
                    placeholder="CSE"
                >
                <p class="field-hint">Short unique code (letters and numbers only).</p>
                @error('code')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="faculty" class="field-label">Faculty</label>
                <input
                    type="text"
                    name="faculty"
                    id="faculty"
                    value="{{ old('faculty', $department?->faculty) }}"
                    class="input-field @error('faculty') input-error @enderror"
                    placeholder="Faculty of Science & Engineering"
                >
                @error('faculty')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="head_id" class="field-label">Department head</label>
                <select name="head_id" id="head_id" class="select-field @error('head_id') input-error @enderror">
                    <option value="">No head assigned</option>
                    @foreach($eligibleHeads as $head)
                        <option value="{{ $head->id }}" {{ (string) old('head_id', $department?->head_id) === (string) $head->id ? 'selected' : '' }}>
                            {{ $head->name }} ({{ $head->role->label() }})
                        </option>
                    @endforeach
                </select>
                @error('head_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="field-label">Description</label>
                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    class="textarea-field @error('description') input-error @enderror"
                    placeholder="Brief overview of the department"
                >{{ old('description', $department?->description) }}</textarea>
                @error('description')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ $department ? route('admin.departments.show', $department) : route('admin.departments.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">{{ $department ? 'Save changes' : 'Create department' }}</button>
    </div>
</form>
