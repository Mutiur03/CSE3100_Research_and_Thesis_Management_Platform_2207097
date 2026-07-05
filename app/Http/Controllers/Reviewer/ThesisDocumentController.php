<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThesisDocumentController extends Controller
{
    public function download(Thesis $thesis, ThesisDocument $document, ThesisDocumentVersion $version): StreamedResponse
    {
        abort_unless($document->thesis_id === $thesis->id, 404);
        abort_unless($version->thesis_document_id === $document->id, 404);

        $this->authorize('view', $thesis);

        return Storage::disk('public')->download($version->file_path, $version->file_name);
    }
}
