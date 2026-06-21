<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case RevisionRequested = 'revision_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::RevisionRequested => 'Revision requested',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            self::Submitted => 'bg-sky-50 text-sky-800 ring-1 ring-sky-200',
            self::UnderReview => 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200',
            self::RevisionRequested => 'bg-amber-50 text-amber-900 ring-1 ring-amber-200',
            self::Approved => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::Rejected => 'bg-red-50 text-red-800 ring-1 ring-red-200',
        };
    }

    /**
     * Statuses a student may edit or resubmit from.
     *
     * @return list<self>
     */
    public static function editableCases(): array
    {
        return [self::Draft, self::RevisionRequested];
    }

    /**
     * Statuses visible to supervisors for review.
     *
     * @return list<self>
     */
    public static function reviewableCases(): array
    {
        return [self::Submitted, self::UnderReview];
    }
}
