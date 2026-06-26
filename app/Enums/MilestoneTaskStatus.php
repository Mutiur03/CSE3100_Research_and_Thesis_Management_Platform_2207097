<?php

namespace App\Enums;

enum MilestoneTaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To do',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Blocked => 'Blocked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Todo => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            self::InProgress => 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200',
            self::Completed => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            self::Blocked => 'bg-red-50 text-red-800 ring-1 ring-red-200',
        };
    }
}
