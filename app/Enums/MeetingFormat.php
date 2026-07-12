<?php

namespace App\Enums;

enum MeetingFormat: string
{
    case Online = 'online';
    case InPerson = 'in_person';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::InPerson => 'In person',
        };
    }

    public function requiresLocation(): bool
    {
        return $this === self::InPerson;
    }
}
