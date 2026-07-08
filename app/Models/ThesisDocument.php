<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use Database\Factories\ThesisDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ThesisDocument extends Model
{
    /** @use HasFactory<ThesisDocumentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'thesis_id',
        'title',
        'description',
        'category',
        'current_version',
        'uploaded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
            'current_version' => 'integer',
        ];
    }

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ThesisDocumentVersion::class)->orderByDesc('version_number');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ThesisDocumentVersion::class)->latestOfMany('version_number');
    }

    public function versionLabel(int $versionNumber): string
    {
        return 'v'.$versionNumber.'.0';
    }

    /**
     * @param  array{title: string, description?: string|null, category: DocumentCategory|string, change_summary?: string|null}  $data
     */
    public static function createDocument(Thesis $thesis, User $user, array $data, UploadedFile $file): self
    {
        return DB::transaction(function () use ($thesis, $user, $data, $file) {
            $document = self::query()->create([
                'thesis_id' => $thesis->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'current_version' => 0,
                'uploaded_by' => $user->id,
            ]);

            $document->storeVersion(
                $user,
                $file,
                $data['change_summary'] ?? null,
            );

            return $document->fresh(['versions', 'uploader']);
        });
    }

    public function storeVersion(
        User $user,
        UploadedFile $file,
        ?string $changeSummary = null,
    ): ThesisDocumentVersion {
        return DB::transaction(function () use ($user, $file, $changeSummary) {
            $versionNumber = ($this->versions()->max('version_number') ?? 0) + 1;
            $directory = sprintf(
                'thesis-documents/%d/%d/v%d',
                $this->thesis_id,
                $this->id,
                $versionNumber,
            );

            $path = $file->store($directory, 'public');
            $checksum = hash_file('sha256', $file->getRealPath());

            $version = $this->versions()->create([
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

            $this->update(['current_version' => $versionNumber]);

            return $version;
        });
    }
}
