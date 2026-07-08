<?php

namespace App\Http\Controllers\Setup;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Mail\AdminSetupCodeMail;
use App\Models\AdminSetupToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;

class SetupController extends Controller
{
    public function index(): View
    {
        return view('setup.index', [
            'isConfigured' => $this->isConfigured(),
            'maskedEmail' => $this->maskedEmail(),
            'tokenLifetime' => config('setup.token_lifetime', 60),
        ]);
    }

    public function sendCode(): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return back()->with('error', 'SETUP_ADMIN_EMAIL is not configured in the environment file.');
        }

        try {
            $this->sendSetupCode();
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('setup.complete')
            ->with('success', 'A setup code has been sent to the configured administrator email.');
    }

    public function showCompleteForm(): View|RedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()
                ->route('setup.index')
                ->with('error', 'SETUP_ADMIN_EMAIL is not configured in the environment file.');
        }

        return view('setup.complete', [
            'maskedEmail' => $this->maskedEmail(),
            'adminEmail' => $this->configuredEmail(),
            'tokenLifetime' => config('setup.token_lifetime', 60),
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'min:8', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'code.required' => 'Enter the setup code from your email.',
        ]);

        try {
            $admin = $this->completeSetup(
                $request->input('code'),
                $request->input('name'),
                $request->input('password'),
            );
        } catch (RuntimeException $exception) {
            return back()
                ->withInput($request->except('password', 'password_confirmation', 'code'))
                ->withErrors(['code' => $exception->getMessage()]);
        }

        Auth::login($admin);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Administrator account created successfully.');
    }

    private function configuredEmail(): ?string
    {
        $email = config('setup.admin_email');

        if (! is_string($email) || $email === '') {
            return null;
        }

        return Str::lower(trim($email));
    }

    private function isConfigured(): bool
    {
        return $this->configuredEmail() !== null;
    }

    private function maskedEmail(): ?string
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

    private function sendSetupCode(): void
    {
        $email = $this->configuredEmail();

        if ($email === null) {
            throw new RuntimeException('Setup admin email is not configured.');
        }

        if (! User::needsSetup()) {
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

    private function completeSetup(string $code, string $name, string $password): User
    {
        $email = $this->configuredEmail();

        if ($email === null) {
            throw new RuntimeException('Setup admin email is not configured.');
        }

        if (! User::needsSetup()) {
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
