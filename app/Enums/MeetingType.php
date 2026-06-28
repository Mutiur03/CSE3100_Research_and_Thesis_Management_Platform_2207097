<?php

namespace App\Enums;

enum MeetingType: string
{
    case Supervision = 'supervision';
    case Committee = 'committee';
    case Defense = 'defense';
    case AdHoc = 'ad_hoc';

    public function label(): string
    {
        return match ($this) {
            self::Supervision => 'Supervision',
            self::Committee => 'Committee',
            self::Defense => 'Defense',
            self::AdHoc => 'Ad hoc',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Supervision => 'bg-sky-50 text-sky-800 ring-1 ring-sky-200',
            self::Committee => 'bg-violet-50 text-violet-800 ring-1 ring-violet-200',
            self::Defense => 'bg-rose-50 text-rose-800 ring-1 ring-rose-200',
            self::AdHoc => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
        };
    }
}
