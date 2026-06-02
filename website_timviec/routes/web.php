<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC PAGES
// ═══════════════════════════════════════════════════════════════════════════

// Trang chủ + danh sách job (thật từ DB)
Route::get('/', [JobController::class, 'index']);
Route::get('/job', [JobController::class, 'index']);
Route::get('/job/show/{slug}', [JobController::class, 'show']);

// Subscribe
Route::get('/subscribe', fn() => view('subscription.index'));

// ═══════════════════════════════════════════════════════════════════════════
// AUTH ROUTES
// ═══════════════════════════════════════════════════════════════════════════

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/register', fn() => view('user.register'))->name('register');
Route::get('/register/employee',  [AuthController::class, 'showRegisterCandidate'])->name('register.candidate');
Route::post('/register/employee', [AuthController::class, 'registerCandidate'])->name('register.candidate.submit');
Route::get('/register/employer',  [AuthController::class, 'showRegisterEmployer'])->name('register.employer');
Route::post('/register/employer', [AuthController::class, 'registerEmployer'])->name('register.employer.submit');

// ═══════════════════════════════════════════════════════════════════════════
// DASHBOARD — Auth thật (tự rẽ nhánh theo user_type)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/user/profile', fn() => view('user.profile'));
});

// ═══════════════════════════════════════════════════════════════════════════
// JOB MANAGEMENT — Employer
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'employer'])->group(function () {
    Route::get('/job/create',        [JobController::class, 'create'])->name('job.create');
    Route::post('/job/store',        [JobController::class, 'store'])->name('job.store');
    Route::get('/job/manage',        [JobController::class, 'manage'])->name('job.manage');
    Route::get('/job/{id}/edit',     [JobController::class, 'edit'])->name('job.edit');
    Route::put('/job/{id}/update',   [JobController::class, 'update'])->name('job.update');
    Route::delete('/job/{id}/delete',[JobController::class, 'destroy'])->name('job.destroy');
});

// ═══════════════════════════════════════════════════════════════════════════
// CV BUILDER — Candidate
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'candidate'])->group(function () {
    Route::get('/user/cv',           [UserController::class, 'cv'])->name('user.cv');
    Route::post('/user/cv',          [UserController::class, 'updateCv'])->name('user.cv.upload')->middleware('throttle:5,1');
    Route::get('/user/cv/view',      [UserController::class, 'viewCv'])->name('user.cv.view');
    Route::get('/user/cv/create',    [UserController::class, 'createCv'])->name('user.cv.create');
    Route::post('/user/cv/preview',  [UserController::class, 'saveCv'])->name('user.cv.save');
    Route::get('/user/cv/preview',   [UserController::class, 'showPreview'])->name('user.cv.preview');
    Route::get('/user/cv/download',  [UserController::class, 'downloadPdf'])->name('user.cv.download')->middleware('throttle:10,1');
    Route::delete('/user/cv/online', [UserController::class, 'deleteOnlineCv'])->name('user.cv.delete');
});

// ═══════════════════════════════════════════════════════════════════════════
// APPLY & TRACKING — Candidate
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'candidate'])->group(function () {
    Route::get('/apply/{listingId}',      [ApplicationController::class, 'showForm'])->name('apply.form');
    Route::post('/apply',             [ApplicationController::class, 'apply'])->name('apply.submit');
    Route::get('/candidate/history',  [ApplicationController::class, 'candidateHistory'])->name('candidate.history');
    Route::get('/candidate/applications/{id}', [ApplicationController::class, 'candidateApplicationDetail'])->name('candidate.application.detail');

    Route::get('/payment/token',          [PaymentController::class, 'tokenPurchasePage'])->name('payment.token');
    Route::post('/payment/token',         [PaymentController::class, 'initiateTokenPurchase'])->name('payment.token.initiate');
    Route::get('/payment/token/callback', [PaymentController::class, 'tokenCallback'])->name('payment.token.callback');
});

// ═══════════════════════════════════════════════════════════════════════════
// EMPLOYER PANEL
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'employer'])->group(function () {
    Route::get('/employer/jobs/{listingId}/applicants',    [ApplicationController::class, 'applicantList'])->name('employer.applicants');
    Route::get('/employer/applications/{id}',          [ApplicationController::class, 'viewDetail'])->name('employer.application.detail');
    Route::patch('/employer/applications/{id}/status', [ApplicationController::class, 'updateStatus'])->name('employer.application.status');

    Route::get('/payment/subscription',          [PaymentController::class, 'subscriptionPage'])->name('payment.subscription');
    Route::post('/payment/subscription',         [PaymentController::class, 'initiateSubscription'])->name('payment.subscription.initiate');
    Route::get('/payment/subscription/callback', [PaymentController::class, 'subscriptionCallback'])->name('payment.subscription.callback');
    Route::get('/employer/subscription',         [PaymentController::class, 'subscriptionStatus'])->name('employer.subscription.status');
});

// ═══════════════════════════════════════════════════════════════════════════
// ADMIN — FakeAuth chỉ còn dùng cho preview admin panel
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(\App\Http\Middleware\FakeAuth::class)->group(function () {
    Route::get('/applicants', fn() => view('applicants.index', ['listings' => collect([])]));
    Route::get('/applicants/{slug}', fn($slug) => view('applicants.view', [
        'listing'    => (object)['id'=>1,'title'=>'Demo','slug'=>$slug,'user'=>(object)['id'=>999,'name'=>'Demo'],'users'=>collect([])],
        'applicants' => new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 10),
    ]));
    Route::get('/messages',      fn() => view('messages.index',  ['conversations' => collect([])]));
    Route::get('/messages/{id}', fn($id) => view('messages.show', [
        'conversations' => collect([]),
        'conversation'  => (object)['id'=>$id,'listing'=>null,'employee'=>null,'employer'=>null,'messages'=>collect([])],
        'messages'      => collect([]),
    ]));
    Route::get('/admin',                             [DashboardController::class, 'index']);
    Route::get('/admin/users',                       [AdminController::class, 'users']);
    Route::post('/admin/users/{id}/role',            [AdminController::class, 'updateRole']);
    Route::post('/admin/users/{id}/plan',            [AdminController::class, 'updatePlan']);
    Route::post('/admin/users/{id}/ban',             [AdminController::class, 'toggleBan']);
    Route::get('/admin/permissions',                 [AdminController::class, 'permissions']);
    Route::post('/admin/permissions/transfer/{id}',  [AdminController::class, 'transferOwnership']);
    Route::get('/admin/jobs',                        [AdminController::class, 'jobs']);
    Route::delete('/admin/jobs/{id}',                [AdminController::class, 'deleteJob']);
});

// ─── VNPay IPN (server-to-server, không cần auth) ─────────────────────────
Route::post('/payment/token/ipn',        [PaymentController::class, 'tokenIpn'])->name('payment.token.ipn');
Route::post('/payment/subscription/ipn', [PaymentController::class, 'subscriptionIpn'])->name('payment.subscription.ipn');