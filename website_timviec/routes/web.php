<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC PAGES
// ═══════════════════════════════════════════════════════════════════════════

Route::get('/', function () {
    return view('job.index', [
        'listings' => \App\Models\Listing::latest()->take(6)->get(),
        'total'    => \App\Models\Listing::count(),
    ]);
});

// ─── Auth (Guest only) ─────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Register — chọn role
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

    // Register employee
    Route::get('/register/employee',  [AuthController::class, 'showRegisterEmployee'])->name('register.employee');
    Route::post('/register/employee', [AuthController::class, 'registerEmployee'])->name('register.employee.submit');

    // Register employer
    Route::get('/register/employer',  [AuthController::class, 'showRegisterEmployer'])->name('register.employer');
    Route::post('/register/employer', [AuthController::class, 'registerEmployer'])->name('register.employer.submit');

    // Forgot / Reset Password
    Route::get('/forgot-password',         [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password',        [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}',  [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',         [PasswordResetController::class, 'resetPassword'])->name('password.update');

    // Google OAuth
    Route::get('/auth/google',          [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
    Route::get('/auth/google/register/{role}', [GoogleController::class, 'redirectWithRole'])->name('auth.google.register')->where('role', 'employee|employer');

    // GitHub OAuth
    Route::get('/auth/github',          [App\Http\Controllers\Auth\GithubController::class, 'redirect'])->name('auth.github');
    Route::get('/auth/github/callback', [App\Http\Controllers\Auth\GithubController::class, 'callback'])->name('auth.github.callback');
    Route::get('/auth/github/register/{role}', [App\Http\Controllers\Auth\GithubController::class, 'redirectWithRole'])->name('auth.github.register')->where('role', 'employee|employer');

    // Social OAuth — chọn role cho user mới
    Route::get('/auth/{provider}/choose-role', function (string $provider) {
        if (!session('social_pending') || session('social_pending.provider') !== $provider) {
            return redirect()->route('login')->withErrors(['email' => 'Phiên đăng ký đã hết hạn.']);
        }
        return view('auth.social-choose-role', ['provider' => $provider]);
    })->name('auth.social.choose-role')->where('provider', 'google|github');

    Route::get('/auth/{provider}/role/{role}', function (string $provider, string $role) {
        if ($provider === 'google') {
            return app(App\Http\Controllers\Auth\GoogleController::class)->completeRegistration($role);
        }
        return app(App\Http\Controllers\Auth\GithubController::class)->completeRegistration($role);
    })->name('auth.social.role')->where('provider', 'google|github')->where('role', 'employee|employer');
});

// ─── Logout (Auth only) ────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ─── Email Verification ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/email/verify',                  [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}',      [EmailVerificationController::class, 'verify'])->name('verification.verify')->middleware('signed');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send')->middleware('throttle:6,1');
});

// ─── Subscribe ─────────────────────────────────────────────────────────────
Route::get('/subscribe', fn() => view('subscription.index'));

// ─── Legal Pages ───────────────────────────────────────────────────────────
Route::get('/terms',   fn() => view('legal.terms'))->name('terms');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');

// ═══════════════════════════════════════════════════════════════════════════
// PROTECTED PAGES (auth + email verified)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard — tự rẽ nhánh theo user_type trong controller
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/user/profile', fn() => view('user.profile'))->name('user.profile');

    // Jobs
    Route::get('/job',             fn() => view('job.index', ['listings' => \App\Models\Listing::latest()->paginate(10), 'total' => \App\Models\Listing::count()]))->name('job.index');
    Route::get('/job/create',      fn() => view('job.create'))->name('job.create')->middleware('can:create,App\Models\Listing');
    Route::get('/job/manage',      fn() => view('job.manage', ['listings' => auth()->user()->listings()->latest()->get()]))->name('job.manage');
    Route::get('/job/{id}/edit',   fn($id) => view('job.edit',  ['listing' => \App\Models\Listing::findOrFail($id)]))->name('job.edit');
    Route::get('/job/show/{slug}', fn($slug) => view('job.show', ['listing' => \App\Models\Listing::where('slug', $slug)->firstOrFail()]))->name('job.show');
    Route::post('/job/store',      fn() => redirect()->route('job.manage'))->name('job.store');

    // Applicants
    Route::get('/applicants',       fn() => view('applicants.index',  ['listings' => auth()->user()->listings()->with('users')->get()]))->name('applicants.index');
    Route::get('/applicants/{slug}', fn($slug) => view('applicants.view', [
        'listing'    => \App\Models\Listing::where('slug', $slug)->firstOrFail(),
        'applicants' => \App\Models\Listing::where('slug', $slug)->firstOrFail()->users()->paginate(10),
    ]))->name('applicants.view');

    // Messages
    Route::get('/messages',      fn() => view('messages.index',  ['conversations' => collect([])]))->name('messages.index');
    Route::get('/messages/{id}', fn($id) => view('messages.show', ['conversations' => collect([]), 'conversation' => null, 'messages' => collect([])]))->name('messages.show');

    // CV Builder (employee only)
    Route::middleware('can:employee')->group(function () {
        Route::get('/user/cv',           [\App\Http\Controllers\UserController::class, 'cv'])->name('user.cv');
        Route::post('/user/cv',          [\App\Http\Controllers\UserController::class, 'updateCv'])->name('user.cv.upload')->middleware('throttle:5,1');
        Route::get('/user/cv/view',      [\App\Http\Controllers\UserController::class, 'viewCv'])->name('user.cv.view');
        Route::get('/user/cv/create',    [\App\Http\Controllers\UserController::class, 'createCv'])->name('user.cv.create');
        Route::post('/user/cv/preview',  [\App\Http\Controllers\UserController::class, 'saveCv'])->name('user.cv.save');
        Route::get('/user/cv/preview',   [\App\Http\Controllers\UserController::class, 'showPreview'])->name('user.cv.preview');
        Route::get('/user/cv/download',  [\App\Http\Controllers\UserController::class, 'downloadPdf'])->name('user.cv.download')->middleware('throttle:10,1');
        Route::delete('/user/cv/online', [\App\Http\Controllers\UserController::class, 'deleteOnlineCv'])->name('user.cv.delete');
    });

    // Admin Panel (admin only)
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                          [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users',                     [AdminController::class, 'users'])->name('users');
        Route::post('/users/{id}/role',          [AdminController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{id}/plan',          [AdminController::class, 'updatePlan'])->name('users.plan');
        Route::post('/users/{id}/ban',           [AdminController::class, 'toggleBan'])->name('users.ban');
        Route::get('/permissions',               [AdminController::class, 'permissions'])->name('permissions');
        Route::post('/permissions/transfer/{id}',[AdminController::class, 'transferOwnership'])->name('permissions.transfer');
        Route::get('/jobs',                      [AdminController::class, 'jobs'])->name('jobs');
        Route::delete('/jobs/{id}',              [AdminController::class, 'deleteJob'])->name('jobs.delete');
    });
});


