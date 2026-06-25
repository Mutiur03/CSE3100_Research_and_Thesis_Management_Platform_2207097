<?php

namespace App\Services;

use App\Enums\DocumentCategory;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ThesisDocumentService
{
    /**
     * @param  array{title: string, description?: string|null, category: DocumentCategory|string, change_summary?: string|null}  $data
     */
    public function createDocument(Thesis $thesis, User $user, array $data, UploadedFile $file): ThesisDocument
    {
        return DB::transaction(function () use ($thesis, $user, $data, $file) {
            $document = ThesisDocument::query()->create([
                'thesis_id' => $thesis->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'current_version' => 0,
                'uploaded_by' => $user->id,
            ]);

            $this->storeVersion(
                $document,
                $user,
                $file,
                $data['change_summary'] ?? null,
            );

            return $document->fresh(['versions', 'uploader']);
        });
    }

    public function storeVersion(
        ThesisDocument $document,
        User $user,
        UploadedFile $file,
        ?string $changeSummary = null,
    ): ThesisDocumentVersion {
        return DB::transaction(function () use ($document, $user, $file, $changeSummary) {
            $versionNumber = ($document->versions()->max('version_number') ?? 0) + 1;
            $directory = sprintf(
                'thesis-documents/%d/%d/v%d',
                $document->thesis_id,
                $document->id,
                $versionNumber,
            );

            $path = $file->store($directory, 'public');
            $checksum = hash_file('sha256', $file->getRealPath());

            $version = $document->versions()->create([
                'version_number' => $versionNumber,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'change_summary' => $changeSummary,
                'checksum' => $checksum,
                'uploaded_by' => $user->id,
                'created_at' => now(),
            ]);

            $document->update(['current_version' => $versionNumber]);

            return $version;
        });
    }
}
