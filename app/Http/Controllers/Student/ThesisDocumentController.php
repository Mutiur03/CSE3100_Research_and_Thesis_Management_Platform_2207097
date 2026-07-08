<?php

namespace App\Http\Controllers\Student;

use App\Enums\DocumentCategory;
use App\Http\Controllers\Controller;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThesisDocumentController extends Controller
{
    public function store(Request $request, Thesis $thesis): RedirectResponse
    {
        abort_unless($thesis->student_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::enum(DocumentCategory::class)],
            'file' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])
                    ->max(10 * 1024),
            ],
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        ThesisDocument::createDocument(
            $thesis,
            $request->user(),
            collect($validated)->only(['title', 'description', 'category', 'change_summary'])->all(),
            $request->file('file'),
        );

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'Document uploaded successfully.');
    }

    public function storeVersion(Request $request, Thesis $thesis, ThesisDocument $document): RedirectResponse
    {
        $validated = $request->validate([
            'file' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])
                    ->max(10 * 1024),
            ],
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($document->thesis_id === $thesis->id, 404);
        abort_unless($thesis->student_id === $request->user()->id, 403);

        $document->storeVersion(
            $request->user(),
            $request->file('file'),
            $validated['change_summary'] ?? null,
        );

        return redirect()->route('student.theses.show', $thesis)
            ->with('success', 'New document version uploaded.');
    }

    public function download(Thesis $thesis, ThesisDocument $document, ThesisDocumentVersion $version): StreamedResponse
    {
        abort_unless($document->thesis_id === $thesis->id, 404);
        abort_unless($version->thesis_document_id === $document->id, 404);
        abort_unless($thesis->student_id === auth()->id(), 403);

        return Storage::disk('public')->download($version->file_path, $version->file_name);
    }
}
