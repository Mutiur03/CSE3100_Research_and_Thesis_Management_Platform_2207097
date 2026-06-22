<?php

use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Setup\SetupController;
use App\Http\Controllers\Student\MilestoneController as StudentMilestoneController;
use App\Http\Controllers\Student\ProposalController as StudentProposalController;
use App\Http\Controllers\Student\ThesisController as StudentThesisController;
use App\Http\Controllers\Supervisor\MilestoneController as SupervisorMilestoneController;
use App\Http\Controllers\Supervisor\ProposalController as SupervisorProposalController;
use App\Http\Controllers\Supervisor\ThesisController as SupervisorThesisController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// Initial administrator setup (before first admin exists)
// ──────────────────────────────────────────────

Route::middleware('setup.pending')->prefix('setup')->name('setup.')->group(function () {
    Route::get('/', [SetupController::class, 'index'])->name('index');
    Route::post('/code', [SetupController::class, 'sendCode'])
        ->middleware('throttle:3,1')
        ->name('code.send');
    Route::get('/complete', [SetupController::class, 'showCompleteForm'])->name('complete');
    Route::post('/complete', [SetupController::class, 'complete'])
        ->middleware('throttle:5,1')
        ->name('complete.store');
});

// ──────────────────────────────────────────────
// Welcome (Public)
// ──────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ──────────────────────────────────────────────
// Guest Routes (redirect if authenticated)
// ──────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// ──────────────────────────────────────────────
// Email Verification (authenticated but unverified)
// ──────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ──────────────────────────────────────────────
// Authenticated + Verified + Active
// ──────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Student proposals
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/proposals', [StudentProposalController::class, 'index'])->name('proposals.index');
        Route::get('/proposals/create', [StudentProposalController::class, 'create'])->name('proposals.create');
        Route::post('/proposals', [StudentProposalController::class, 'store'])->name('proposals.store');
        Route::get('/proposals/{proposal}', [StudentProposalController::class, 'show'])->name('proposals.show');
        Route::get('/proposals/{proposal}/edit', [StudentProposalController::class, 'edit'])->name('proposals.edit');
        Route::put('/proposals/{proposal}', [StudentProposalController::class, 'update'])->name('proposals.update');
        Route::delete('/proposals/{proposal}', [StudentProposalController::class, 'destroy'])->name('proposals.destroy');
        Route::post('/proposals/{proposal}/submit', [StudentProposalController::class, 'submit'])->name('proposals.submit');

        Route::get('/theses', [StudentThesisController::class, 'index'])->name('theses.index');
        Route::get('/theses/{thesis}', [StudentThesisController::class, 'show'])->name('theses.show');
        Route::post('/theses/{thesis}/milestones/{milestone}/complete', [StudentMilestoneController::class, 'complete'])->name('theses.milestones.complete');
    });

    // Supervisor proposal reviews
    Route::middleware('role:supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/proposals', [SupervisorProposalController::class, 'index'])->name('proposals.index');
        Route::get('/proposals/{proposal}', [SupervisorProposalController::class, 'show'])->name('proposals.show');
        Route::post('/proposals/{proposal}/review', [SupervisorProposalController::class, 'review'])->name('proposals.review');

        Route::get('/theses', [SupervisorThesisController::class, 'index'])->name('theses.index');
        Route::get('/theses/{thesis}', [SupervisorThesisController::class, 'show'])->name('theses.show');
        Route::post('/theses/{thesis}/milestones', [SupervisorMilestoneController::class, 'store'])->name('theses.milestones.store');
        Route::put('/theses/{thesis}/milestones/{milestone}', [SupervisorMilestoneController::class, 'update'])->name('theses.milestones.update');
        Route::delete('/theses/{thesis}/milestones/{milestone}', [SupervisorMilestoneController::class, 'destroy'])->name('theses.milestones.destroy');
    });

    // ──────────────────────────────────────────
    // Admin Routes
    // ──────────────────────────────────────────

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

        Route::get('/departments', [AdminDepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [AdminDepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [AdminDepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}', [AdminDepartmentController::class, 'show'])->name('departments.show');
        Route::get('/departments/{department}/edit', [AdminDepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{department}', [AdminDepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [AdminDepartmentController::class, 'destroy'])->name('departments.destroy');
    });
});
