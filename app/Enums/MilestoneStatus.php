<?php

namespace App\Enums;

enum MilestoneStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-sky-50 text-sky-800 ring-1 ring-sky-200',
            self::Completed => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
        };
    }
}
