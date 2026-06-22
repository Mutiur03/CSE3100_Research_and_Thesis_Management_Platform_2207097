<?php

namespace App\Enums;

enum ThesisStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Suspended => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::Completed => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            self::Suspended => 'bg-amber-50 text-amber-900 ring-1 ring-amber-200',
        };
    }

    /**
     * @return list<self>
     */
    public static function activeCases(): array
    {
        return [self::Active];
    }
}
