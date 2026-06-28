<?php

namespace App\Enums;

enum MeetingStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-sky-50 text-sky-800 ring-1 ring-sky-200',
            self::InProgress => 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200',
            self::Completed => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::Cancelled => 'bg-stone-100 text-stone-600 ring-1 ring-stone-200',
        };
    }
}
