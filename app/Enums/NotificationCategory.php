<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case Proposals = 'proposals';
    case Documents = 'documents';
    case Meetings = 'meetings';
    case Comments = 'comments';
    case Milestones = 'milestones';

    public function label(): string
    {
        return match ($this) {
            self::Proposals => 'Proposal updates',
            self::Documents => 'Document uploads',
            self::Meetings => 'Meetings',
            self::Comments => 'Comments & mentions',
            self::Milestones => 'Milestone reminders',
        };
    }

    public function supportsEmail(): bool
    {
        return match ($this) {
            self::Documents, self::Comments => false,
            default => true,
        };
    }

    public function supportsDatabase(): bool
    {
        return true;
    }

    /**
     * @return array<string, bool>
     */
    public static function defaults(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $category) => [$category->value => true])
            ->all();
    }
}
