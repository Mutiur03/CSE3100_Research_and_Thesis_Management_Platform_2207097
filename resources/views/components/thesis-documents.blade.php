@props([
    'thesis',
    'routePrefix',
])

@php
    $canUpload = auth()->user()->can('create', [\App\Models\ThesisDocument::class, $thesis]);
@endphp

<div class="card overflow-hidden">
    <div class="card-section flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-stone-900">Documents</h3>
            <p class="mt-0.5 text-sm text-stone-500">Upload thesis files with automatic version tracking.</p>
        </div>
        @if($canUpload)
            <button type="button" class="btn-primary btn-sm" onclick="document.getElementById('upload-document-form').classList.toggle('hidden')">
                Upload document
            </button>
        @endif
    </div>

    @if($canUpload)
        <div id="upload-document-form" class="{{ $errors->hasAny(['title', 'category', 'file', 'description', 'change_summary']) && ! request('document') ? '' : 'hidden' }} border-t border-stone-100 bg-stone-50 px-6 py-5">
            <form method="POST" action="{{ route($routePrefix.'.theses.documents.store', $thesis) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="doc-title" class="field-label">Title</label>
                        <input type="text" name="title" id="doc-title" value="{{ old('title') }}" required class="input-field @error('title') input-error @enderror" placeholder="e.g. Chapter 1 draft">
                        @error('title')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="doc-category" class="field-label">Category</label>
                        <select name="category" id="doc-category" required class="input-field @error('category') input-error @enderror">
                            @foreach(\App\Enums\DocumentCategory::cases() as $categoryOption)
                                <option value="{{ $categoryOption->value }}" @selected(old('category') === $categoryOption->value)>{{ $categoryOption->label() }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="doc-file" class="field-label">File</label>
                        <input type="file" name="file" id="doc-file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="input-field @error('file') input-error @enderror">
                        @error('file')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-stone-500">PDF, Word, or image · max 10 MB</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="doc-description" class="field-label">Description <span class="font-normal text-stone-400">(optional)</span></label>
                        <textarea name="description" id="doc-description" rows="2" class="textarea-field @error('description') input-error @enderror" placeholder="Brief note about this document">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="doc-change-summary" class="field-label">Version notes <span class="font-normal text-stone-400">(optional)</span></label>
                        <input type="text" name="change_summary" id="doc-change-summary" value="{{ old('change_summary') }}" class="input-field @error('change_summary') input-error @enderror" placeholder="Initial upload">
                        @error('change_summary')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary btn-sm">Upload</button>
                    <button type="button" class="btn-secondary btn-sm" onclick="document.getElementById('upload-document-form').classList.add('hidden')">Cancel</button>
                </div>
            </form>
        </div>
    @endif

    @if($thesis->documents->isEmpty())
        <div class="card-body text-sm text-stone-500">
            No documents uploaded yet.
        </div>
    @else
        <div class="divide-y divide-stone-100 border-t border-stone-100">
            @foreach($thesis->documents as $document)
                <div class="px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium text-stone-900">{{ $document->title }}</p>
                                <x-document-category-badge :category="$document->category" />
                                <span class="text-xs text-stone-500">{{ $document->versionLabel($document->current_version) }}</span>
                            </div>
                            @if($document->description)
                                <p class="mt-1 text-sm text-stone-500">{{ $document->description }}</p>
                            @endif
                            @if($document->latestVersion)
                                <p class="mt-2 text-xs text-stone-500">
                                    Latest: {{ $document->latestVersion->file_name }}
                                    · {{ $document->latestVersion->formattedFileSize() }}
                                    · {{ $document->latestVersion->created_at->format('M j, Y') }}
                                </p>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            @if($document->latestVersion)
                                <a href="{{ route($routePrefix.'.theses.documents.versions.download', [$thesis, $document, $document->latestVersion]) }}" class="btn-secondary btn-sm">
                                    Download latest
                                </a>
                            @endif
                            @can('addVersion', $document)
                                <button type="button" class="btn-secondary btn-sm" onclick="document.getElementById('version-form-{{ $document->id }}').classList.toggle('hidden')">
                                    New version
                                </button>
                            @endcan
                        </div>
                    </div>

                    @can('addVersion', $document)
                        <div id="version-form-{{ $document->id }}" class="{{ $errors->hasAny(['file', 'change_summary']) && (string) request('document') === (string) $document->id ? '' : 'hidden' }} mt-4 rounded border border-stone-200 bg-stone-50 p-4">
                            <form method="POST" action="{{ route($routePrefix.'.theses.documents.versions.store', [$thesis, $document]) }}?document={{ $document->id }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="version-file-{{ $document->id }}" class="field-label">New file</label>
                                    <input type="file" name="file" id="version-file-{{ $document->id }}" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="input-field @error('file') input-error @enderror">
                                    @error('file')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="version-summary-{{ $document->id }}" class="field-label">Change summary <span class="font-normal text-stone-400">(optional)</span></label>
                                    <input type="text" name="change_summary" id="version-summary-{{ $document->id }}" value="{{ old('change_summary') }}" class="input-field @error('change_summary') input-error @enderror" placeholder="What changed in this version?">
                                    @error('change_summary')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="btn-primary btn-sm">Upload version</button>
                            </form>
                        </div>
                    @endcan

                    @if($document->versions->count() > 1)
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-navy-700 hover:text-navy-900">Version history ({{ $document->versions->count() }})</summary>
                            <ul class="mt-3 space-y-2">
                                @foreach($document->versions as $version)
                                    <li class="flex flex-col gap-2 rounded border border-stone-100 bg-white px-3 py-2 text-sm sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-medium text-stone-800">
                                                {{ $document->versionLabel($version->version_number) }}
                                                · {{ $version->file_name }}
                                            </p>
                                            <p class="text-xs text-stone-500">
                                                {{ $version->formattedFileSize() }}
                                                · {{ $version->uploader->name }}
                                                · {{ $version->created_at->format('M j, Y g:i A') }}
                                            </p>
                                            @if($version->change_summary)
                                                <p class="mt-1 text-xs text-stone-600">{{ $version->change_summary }}</p>
                                            @endif
                                        </div>
                                        <a href="{{ route($routePrefix.'.theses.documents.versions.download', [$thesis, $document, $version]) }}" class="btn-secondary btn-sm shrink-0">
                                            Download
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
