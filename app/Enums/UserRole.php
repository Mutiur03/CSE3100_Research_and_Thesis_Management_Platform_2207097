<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Supervisor = 'supervisor';
    case Admin = 'admin';

    /**
     * Human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Supervisor => 'Supervisor',
            self::Admin => 'Admin',
        };
    }

    /**
     * Tailwind CSS color classes for role badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Student => 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            self::Supervisor => 'bg-navy-50 text-navy-800 ring-1 ring-navy-200',
            self::Admin => 'bg-brand-50 text-brand-800 ring-1 ring-brand-200',
        };
    }

    /**
     * Roles available during self-registration.
     *
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
     * Roles that can be assigned from the admin user management panel.
     * Includes administrator for promotion by an existing admin.
     *
     * @return array<string>
     */
    public static function assignableByAdminValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Roles shown in the admin user management panel.
     *
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
