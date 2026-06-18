<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\CompleteAdminSetupRequest;
use App\Services\AdminSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class SetupController extends Controller
{
    public function __construct(
        private readonly AdminSetupService $setup,
    ) {}

    public function index(): View
    {
        return view('setup.index', [
            'isConfigured' => $this->setup->isConfigured(),
            'maskedEmail' => $this->setup->maskedEmail(),
            'tokenLifetime' => config('setup.token_lifetime', 60),
        ]);
    }

    public function sendCode(): RedirectResponse
    {
        if (! $this->setup->isConfigured()) {
            return back()->with('error', 'SETUP_ADMIN_EMAIL is not configured in the environment file.');
        }

        try {
            $this->setup->sendSetupCode();
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('setup.complete')
            ->with('success', 'A setup code has been sent to the configured administrator email.');
    }

    public function showCompleteForm(): View|RedirectResponse
    {
        if (! $this->setup->isConfigured()) {
            return redirect()
                ->route('setup.index')
                ->with('error', 'SETUP_ADMIN_EMAIL is not configured in the environment file.');
        }

        return view('setup.complete', [
            'maskedEmail' => $this->setup->maskedEmail(),
            'adminEmail' => $this->setup->configuredEmail(),
            'tokenLifetime' => config('setup.token_lifetime', 60),
        ]);
    }

    public function complete(CompleteAdminSetupRequest $request): RedirectResponse
    {
        try {
            $admin = $this->setup->completeSetup(
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
}
