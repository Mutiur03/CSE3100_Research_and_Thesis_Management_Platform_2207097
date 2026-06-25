<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case Proposal = 'proposal';
    case Chapter = 'chapter';
    case Appendix = 'appendix';
    case Final = 'final';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Proposal => 'Proposal',
            self::Chapter => 'Chapter',
            self::Appendix => 'Appendix',
            self::Final => 'Final thesis',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Proposal => 'bg-sky-50 text-sky-800 ring-1 ring-sky-200',
            self::Chapter => 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200',
            self::Appendix => 'bg-violet-50 text-violet-800 ring-1 ring-violet-200',
            self::Final => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::Other => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
        };
    }
}
