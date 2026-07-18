<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'avatar',
        'bio',
        'phone',
        'research_interests',
        'is_active',
        'last_login_at',
        'composio_google_connected_account_id',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'research_interests' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole(UserRole::Supervisor);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(UserRole::Student);
    }

    public function hasGoogleCalendarConnected(): bool
    {
        return filled($this->composio_google_connected_account_id);
    }

    public static function needsSetup(): bool
    {
        return ! self::query()->where('role', UserRole::Admin)->exists();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function proposalsAsStudent(): HasMany
    {
        return $this->hasMany(Proposal::class, 'student_id');
    }

    public function proposalsAsSupervisor(): HasMany
    {
        return $this->hasMany(Proposal::class, 'supervisor_id');
    }

    public function thesesAsStudent(): HasMany
    {
        return $this->hasMany(Thesis::class, 'student_id');
    }

    public function thesesAsSupervisor(): HasMany
    {
        return $this->hasMany(Thesis::class, 'supervisor_id');
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        $hash = md5(strtolower(trim($this->email)));

        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }
}
