<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use Database\Factories\ThesisDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
