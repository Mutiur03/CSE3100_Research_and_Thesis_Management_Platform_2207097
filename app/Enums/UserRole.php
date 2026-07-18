<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Supervisor = 'supervisor';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Supervisor => 'Supervisor',
            self::Admin => 'Admin',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Student => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            self::Supervisor => 'bg-navy-50 text-navy-800 ring-1 ring-navy-200',
            self::Admin => 'bg-brand-50 text-brand-800 ring-1 ring-brand-200',
        };
    }

    /**
     * @return array<string>
     */
    public static function registrableValues(): array
    {
        return [
            self::Student->value,
            self::Supervisor->value,
        ];
    }

    /**
     * @return array<string>
     */
    public static function assignableByAdminValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<self>
     */
    public static function manageableCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $role) => $role !== self::Admin,
        ));
    }
}
