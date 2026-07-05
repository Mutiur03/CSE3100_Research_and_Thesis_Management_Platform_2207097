<?php

namespace App\Models;

use App\Enums\ThesisReviewDecision;
use App\Enums\ThesisReviewStatus;
use Database\Factories\ThesisReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesisReview extends Model
{
    /** @use HasFactory<ThesisReviewFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'thesis_id',
        'reviewer_id',
        'status',
        'decision',
        'review_notes',
        'assigned_by',
        'assigned_at',
        'submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ThesisReviewStatus::class,
            'decision' => ThesisReviewDecision::class,
            'assigned_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isSubmittable(): bool
    {
        return in_array($this->status, ThesisReviewStatus::openCases(), true)
            && $this->thesis?->isActive();
    }
}
