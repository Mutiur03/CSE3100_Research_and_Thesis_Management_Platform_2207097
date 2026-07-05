<?php

namespace App\Enums;

enum ThesisReviewOutcome: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Approved = 'approved';
    case RevisionNeeded = 'revision_needed';
    case Rejected = 'rejected';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting reviewers',
            self::InProgress => 'Reviews in progress',
            self::Approved => 'All reviewers approved',
            self::RevisionNeeded => 'Revision requested',
            self::Rejected => 'Rejected by reviewer',
            self::Mixed => 'Mixed review decisions',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            self::InProgress => 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200',
            self::Approved => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::RevisionNeeded => 'bg-amber-50 text-amber-900 ring-1 ring-amber-200',
            self::Rejected => 'bg-red-50 text-red-800 ring-1 ring-red-200',
            self::Mixed => 'bg-violet-50 text-violet-800 ring-1 ring-violet-200',
        };
    }
}
