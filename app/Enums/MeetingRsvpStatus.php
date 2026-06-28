<?php

namespace App\Enums;

enum MeetingRsvpStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Tentative = 'tentative';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Tentative => 'Tentative',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
            self::Accepted => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::Declined => 'bg-rose-50 text-rose-800 ring-1 ring-rose-200',
            self::Tentative => 'bg-sky-50 text-sky-800 ring-1 ring-sky-200',
        };
    }
}
