<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Supervisor = 'supervisor';
    case Reviewer = 'reviewer';
    case Admin = 'admin';

    /**
     * Human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Supervisor => 'Supervisor',
            self::Reviewer => 'Reviewer',
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
            self::Reviewer => 'bg-amber-50 text-amber-900 ring-1 ring-amber-200',
            self::Admin => 'bg-brand-50 text-brand-800 ring-1 ring-brand-200',
        };
    }

    /**
     * Icon name for the role (used in UI).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Student => 'academic-cap',
            self::Supervisor => 'user-group',
            self::Reviewer => 'clipboard-document-check',
            self::Admin => 'shield-check',
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
     * Administrators are created only through the initial /setup flow.
     *
     * @return array<string>
     */
    public static function assignableByAdminValues(): array
    {
        return [
            self::Student->value,
            self::Supervisor->value,
            self::Reviewer->value,
        ];
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
