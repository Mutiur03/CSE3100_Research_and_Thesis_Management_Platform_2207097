<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThesisDocument\StoreThesisDocumentRequest;
use App\Http\Requests\ThesisDocument\StoreThesisDocumentVersionRequest;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use App\Services\ThesisDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThesisDocumentController extends Controller
{
    public function __construct(
        private readonly ThesisDocumentService $documents,
    ) {}

    public function store(StoreThesisDocumentRequest $request, Thesis $thesis): RedirectResponse
    {
        $this->authorize('create', [ThesisDocument::class, $thesis]);

        $this->documents->createDocument(
            $thesis,
            $request->user(),
            $request->safe()->only(['title', 'description', 'category', 'change_summary']),
            $request->file('file'),
        );

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'Document uploaded successfully.');
    }

    public function storeVersion(StoreThesisDocumentVersionRequest $request, Thesis $thesis, ThesisDocument $document): RedirectResponse
    {
        abort_unless($document->thesis_id === $thesis->id, 404);

        $this->authorize('addVersion', $document);

        $this->documents->storeVersion(
            $document,
            $request->user(),
            $request->file('file'),
            $request->validated('change_summary'),
        );

        return redirect()->route('supervisor.theses.show', $thesis)
            ->with('success', 'New document version uploaded.');
    }

    public function download(Thesis $thesis, ThesisDocument $document, ThesisDocumentVersion $version): StreamedResponse
    {
        abort_unless($document->thesis_id === $thesis->id, 404);
        abort_unless($version->thesis_document_id === $document->id, 404);

        $this->authorize('view', $document);

        return Storage::disk('public')->download($version->file_path, $version->file_name);
    }
}
