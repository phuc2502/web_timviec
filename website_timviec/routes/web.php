<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC PAGES
// ═══════════════════════════════════════════════════════════════════════════

// Trang chủ + danh sách job (thật từ DB)
Route::get('/', [JobController::class, 'index']);
Route::get('/job', [JobController::class, 'index'])->name('job.index');
Route::get('/job/show/{slug}', [JobController::class, 'show'])->name('job.show');

// Subscribe page
Route::get('/subscribe', fn() => view('subscription.index'));

// Legal Pages
Route::get('/terms',   fn() => view('legal.terms'))->name('terms');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');

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

// ═══════════════════════════════════════════════════════════════════════════
// PROTECTED PAGES (auth + email verified)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard — tự rẽ nhánh theo user_type trong controller
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/user/profile', fn() => view('user.profile'))->name('user.profile');
    Route::post('/user/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::post('/user/profile/password', [UserController::class, 'updatePassword'])->name('user.profile.password');

    // Notifications
    Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read',[NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Messages
    Route::get('/messages',      fn() => view('messages.index',  ['conversations' => collect([])]))->name('messages.index');
    Route::get('/messages/{id}', fn($id) => view('messages.show', ['conversations' => collect([]), 'conversation' => null, 'messages' => collect([])]))->name('messages.show');

    // ─── JOB MANAGEMENT — Employer ─────────────────────────────────────────
    Route::middleware('employer')->group(function () {
        Route::get('/job/create',        [JobController::class, 'create'])->name('job.create');
        Route::post('/job/store',        [JobController::class, 'store'])->name('job.store');
        Route::get('/job/manage',        [JobController::class, 'manage'])->name('job.manage');
        Route::get('/job/{id}/edit',     [JobController::class, 'edit'])->name('job.edit');
        Route::put('/job/{id}/update',   [JobController::class, 'update'])->name('job.update');
        Route::delete('/job/{id}/delete',[JobController::class, 'destroy'])->name('job.destroy');

        // Employer Payment & Subscription
        Route::get('/payment/subscription',          [PaymentController::class, 'subscriptionPage'])->name('payment.subscription');
        Route::post('/payment/subscription',         [PaymentController::class, 'initiateSubscription'])->name('payment.subscription.initiate');
        Route::get('/payment/subscription/callback', [PaymentController::class, 'subscriptionCallback'])->name('payment.subscription.callback');
        Route::get('/employer/subscription',         [PaymentController::class, 'subscriptionStatus'])->name('employer.subscription.status');

        // Applicants Tracking for Employer
        Route::get('/employer/jobs/{listingId}/applicants', [ApplicationController::class, 'applicantList'])->name('employer.applicants');
        Route::get('/employer/applications/{id}',          [ApplicationController::class, 'viewDetail'])->name('employer.application.detail');
        Route::patch('/employer/applications/{id}/status', [ApplicationController::class, 'updateStatus'])->name('employer.application.status');
    });

    // ─── CV BUILDER & APPLY — Candidate ──────────────────────────────────────
    Route::middleware('candidate')->group(function () {
        // CV Builder
        Route::get('/user/cv',           [UserController::class, 'cv'])->name('user.cv');
        Route::post('/user/cv',          [UserController::class, 'updateCv'])->name('user.cv.upload')->middleware('throttle:5,1');
        Route::get('/user/cv/view',      [UserController::class, 'viewCv'])->name('user.cv.view');
        Route::get('/user/cv/create',    [UserController::class, 'createCv'])->name('user.cv.create');
        Route::post('/user/cv/preview',  [UserController::class, 'saveCv'])->name('user.cv.save');
        Route::get('/user/cv/preview',   [UserController::class, 'showPreview'])->name('user.cv.preview');
        Route::get('/user/cv/download',  [UserController::class, 'downloadPdf'])->name('user.cv.download')->middleware('throttle:10,1');
        Route::delete('/user/cv/online', [UserController::class, 'deleteOnlineCv'])->name('user.cv.delete');

        // Apply & Tracking
        Route::get('/apply/{listingId}',          [ApplicationController::class, 'showForm'])->name('apply.form');
        Route::post('/apply',                 [ApplicationController::class, 'apply'])->name('apply.submit');
        Route::get('/candidate/history',      [ApplicationController::class, 'candidateHistory'])->name('candidate.history');
        Route::get('/candidate/applications/{id}', [ApplicationController::class, 'candidateApplicationDetail'])->name('candidate.application.detail');

        // Candidate Tokens Payment
        Route::get('/payment/token',          [PaymentController::class, 'tokenPurchasePage'])->name('payment.token');
        Route::post('/payment/token',         [PaymentController::class, 'initiateTokenPurchase'])->name('payment.token.initiate');
        Route::get('/payment/token/callback', [PaymentController::class, 'tokenCallback'])->name('payment.token.callback');
    });

    // ─── Admin Panel (admin only) ────────────────────────────────────────────
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

// ─── VNPay IPN (server-to-server, không cần auth) ─────────────────────────
Route::post('/payment/token/ipn',        [PaymentController::class, 'tokenIpn'])->name('payment.token.ipn');
Route::post('/payment/subscription/ipn', [PaymentController::class, 'subscriptionIpn'])->name('payment.subscription.ipn');
