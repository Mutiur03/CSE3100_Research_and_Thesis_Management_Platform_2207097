<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Mail\AdminSetupCodeMail;
use App\Models\AdminSetupToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class AdminSetupService
{
    public function needsSetup(): bool
    {
        return ! User::query()->where('role', UserRole::Admin)->exists();
    }

    public function configuredEmail(): ?string
    {
        $email = config('setup.admin_email');

        if (! is_string($email) || $email === '') {
            return null;
        }

        return Str::lower(trim($email));
    }

    public function isConfigured(): bool
    {
        return $this->configuredEmail() !== null;
    }

    public function maskedEmail(): ?string
    {
        $email = $this->configuredEmail();

        if ($email === null) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = Str::substr($local, 0, 1);
        $hiddenLength = max(strlen($local) - 1, 3);

        return $visible.str_repeat('*', $hiddenLength).'@'.$domain;
    }

    public function sendSetupCode(): void
    {
        $email = $this->configuredEmail();

        if ($email === null) {
            throw new RuntimeException('Setup admin email is not configured.');
        }

        if (! $this->needsSetup()) {
            throw new RuntimeException('Platform setup is already complete.');
        }

        AdminSetupToken::query()->where('email', $email)->delete();

        $plainCode = $this->generatePlainCode();
        $normalizedCode = $this->normalizeCode($plainCode);

        AdminSetupToken::create([
            'email' => $email,
            'token' => Hash::make($normalizedCode),
            'expires_at' => now()->addMinutes(config('setup.token_lifetime', 60)),
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new AdminSetupCodeMail($plainCode));
    }

    public function completeSetup(string $code, string $name, string $password): User
    {
        $email = $this->configuredEmail();

        if ($email === null) {
            throw new RuntimeException('Setup admin email is not configured.');
        }

        if (! $this->needsSetup()) {
            throw new RuntimeException('Platform setup is already complete.');
        }

        $token = AdminSetupToken::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        if (! $token || $token->isExpired() || ! Hash::check($this->normalizeCode($code), $token->token)) {
            throw new RuntimeException('The setup code is invalid or has expired.');
        }

        return DB::transaction(function () use ($token, $email, $name, $password): User {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => UserRole::Admin,
                'is_active' => true,
            ]);

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $token->delete();
            AdminSetupToken::query()->where('email', $email)->delete();

            return $user;
        });
    }

    private function generatePlainCode(): string
    {
        $segments = [];

        for ($i = 0; $i < 4; $i++) {
            $segments[] = Str::upper(Str::random(4));
        }

        return implode('-', $segments);
    }

    private function normalizeCode(string $code): string
    {
        return Str::upper(preg_replace('/[\s\-]+/', '', trim($code)));
    }
}
