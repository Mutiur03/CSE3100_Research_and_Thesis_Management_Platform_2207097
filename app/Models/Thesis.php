<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use App\Enums\ProposalStatus;
use App\Enums\ThesisReviewDecision;
use App\Enums\ThesisReviewOutcome;
use App\Enums\ThesisReviewStatus;
use App\Enums\ThesisStatus;
use Database\Factories\ThesisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin IdeHelperThesis
 */
class Thesis extends Model
{
    /** @use HasFactory<ThesisFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'proposal_id',
        'student_id',
        'department_id',
        'supervisor_id',
        'title',
        'status',
        'started_at',
        'completed_at',
        'final_submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ThesisStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'final_submitted_at' => 'datetime',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('sort_order')->orderBy('due_date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ThesisDocument::class)->latest();
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class)->orderBy('scheduled_at');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ThesisReview::class)->latest('assigned_at');
    }

    public function isActive(): bool
    {
        return $this->status === ThesisStatus::Active;
    }

    public function isFinalSubmitted(): bool
    {
        return $this->final_submitted_at !== null;
    }

    public function hasFinalDocument(): bool
    {
        return $this->documents()
            ->where('category', DocumentCategory::Final)
            ->exists();
    }

    public function reviewOutcome(): ?ThesisReviewOutcome
    {
        $reviews = $this->relationLoaded('reviews')
            ? $this->reviews
            : $this->reviews()->get();

        if ($reviews->isEmpty()) {
            return null;
        }

        if ($reviews->contains(fn (ThesisReview $review) => in_array($review->status, ThesisReviewStatus::openCases(), true))) {
            return $reviews->every(fn (ThesisReview $review) => $review->status === ThesisReviewStatus::Pending)
                ? ThesisReviewOutcome::Pending
                : ThesisReviewOutcome::InProgress;
        }

        $decisions = $reviews->pluck('decision');

        if ($decisions->contains(ThesisReviewDecision::Reject)) {
            return ThesisReviewOutcome::Rejected;
        }

        if ($decisions->contains(ThesisReviewDecision::RequestRevision)) {
            return ThesisReviewOutcome::RevisionNeeded;
        }

        if ($decisions->every(fn (?ThesisReviewDecision $decision) => $decision === ThesisReviewDecision::Approve)) {
            return ThesisReviewOutcome::Approved;
        }

        return ThesisReviewOutcome::Mixed;
    }

    public function showUrlFor(User $user): string
    {
        if ($user->isStudent()) {
            return route('student.theses.show', $this);
        }

        if ($user->isReviewer()) {
            $review = $this->reviews()->where('reviewer_id', $user->id)->first();

            return $review
                ? route('reviewer.reviews.show', $review)
                : route('reviewer.reviews.index');
        }

        return route('supervisor.theses.show', $this);
    }

    public static function createFromApprovedProposal(Proposal $proposal): self
    {
        if ($proposal->status !== ProposalStatus::Approved) {
            throw new \InvalidArgumentException('Thesis can only be created from an approved proposal.');
        }

        return self::firstOrCreate(
            ['proposal_id' => $proposal->id],
            [
                'student_id' => $proposal->student_id,
                'department_id' => $proposal->department_id,
                'supervisor_id' => $proposal->supervisor_id,
                'title' => $proposal->title,
                'status' => ThesisStatus::Active,
                'started_at' => $proposal->reviewed_at ?? now(),
            ],
        );
    }
}
