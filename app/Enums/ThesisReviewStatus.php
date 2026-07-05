<?php

namespace App\Enums;

enum ThesisReviewStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In progress',
            self::Submitted => 'Submitted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            self::InProgress => 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200',
            self::Submitted => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
        };
    }

    /**
     * @return list<self>
     */
    public static function openCases(): array
    {
        return [self::Pending, self::InProgress];
    }
}
