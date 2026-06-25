<?php

namespace App\Models;

use Database\Factories\ThesisDocumentVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ThesisDocumentVersion extends Model
{
    /** @use HasFactory<ThesisDocumentVersionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'thesis_document_id',
        'version_number',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'change_summary',
        'checksum',
        'uploaded_by',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'file_size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ThesisDocument::class, 'thesis_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function formattedFileSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function deleteStoredFile(): void
    {
        Storage::disk('public')->delete($this->file_path);
    }
}
