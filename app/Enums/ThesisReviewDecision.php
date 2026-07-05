<?php

namespace App\Enums;

enum ThesisReviewDecision: string
{
    case Approve = 'approve';
    case RequestRevision = 'request_revision';
    case Reject = 'reject';

    public function label(): string
    {
        return match ($this) {
            self::Approve => 'Approve',
            self::RequestRevision => 'Request revision',
            self::Reject => 'Reject',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Approve => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::RequestRevision => 'bg-amber-50 text-amber-900 ring-1 ring-amber-200',
            self::Reject => 'bg-red-50 text-red-800 ring-1 ring-red-200',
        };
    }
}
