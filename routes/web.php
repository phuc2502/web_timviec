<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC PAGES (Ai cũng có thể truy cập)
// ═══════════════════════════════════════════════════════════════════════════

// Trang chủ + danh sách job (thật từ DB)
Route::get('/', [JobController::class, 'index']);
Route::get('/job', [JobController::class, 'index']);
Route::get('/job/show/{slug}', [JobController::class, 'show']);

// Subscribe
Route::get('/subscribe', fn() => view('subscription.index'));

// Legal Pages
Route::get('/terms',   fn() => view('legal.terms'))->name('terms');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');


// ═══════════════════════════════════════════════════════════════════════════
// AUTH ROUTES (Chỉ dành cho Khách - Guest chưa đăng nhập)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Register — chọn role chung
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

    // Register employee (Ứng viên)
    Route::get('/register/employee',  [AuthController::class, 'showRegisterEmployee'])->name('register.employee');
    Route::post('/register/employee', [AuthController::class, 'registerEmployee'])->name('register.employee.submit');

    // Register employer (Nhà tuyển dụng)
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

    // Social OAuth — Chọn role cho tài khoản mạng xã hội mới tạo
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

// Logout (Yêu cầu đăng nhập)
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Email Verification Notice & Handler
Route::middleware('auth')->group(function () {
    Route::get('/email/verify',                     [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}',         [EmailVerificationController::class, 'verify'])->name('verification.verify')->middleware('signed');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send')->middleware('throttle:6,1');
});


// ═══════════════════════════════════════════════════════════════════════════
// CHỈ YÊU CẦU ĐĂNG NHẬP (Không cần bắt buộc xác minh email để test mượt mà)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {
    
    // Dashboard (Tự động rẽ nhánh giao diện theo user_type trong Controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AI Chat Panel
    Route::get('/ai-chat',            [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-chat/new',       [AiChatController::class, 'create'])->name('ai-chat.create');
    Route::get('/ai-chat/{id}',       [AiChatController::class, 'show'])->name('ai-chat.show');
    Route::post('/ai-chat/{id}/send', [AiChatController::class, 'send'])->name('ai-chat.send');
    Route::delete('/ai-chat/{id}',    [AiChatController::class, 'destroy'])->name('ai-chat.destroy');

    // 🌟 MESSAGES (Candidate - Recruiter Chat) — Đã hạ tầng xuống đây để tránh kẹt trang xác thực!
    Route::get('/messages',            [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/start',     [MessageController::class, 'findOrCreate'])->name('messages.start');
    Route::get('/messages/{id}',       [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{id}/send', [MessageController::class, 'store'])->name('messages.store')->middleware('throttle:30,1');
    Route::get('/messages/{id}/poll',  [MessageController::class, 'poll'])->name('messages.poll');
    Route::delete('/messages/{id}',    [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/messages/{id}/restore', [MessageController::class, 'restore'])->name('messages.restore');
    Route::post('/messages/interviews/{id}/respond', [MessageController::class, 'respondToInterview'])->name('messages.interview.respond');
    Route::get('/messages/quick-replies/list',       [MessageController::class, 'getQuickReplies'])->name('messages.quick_replies');

    // Notifications nhận chung
    Route::get('/notifications',           [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read',[\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
});


// ═══════════════════════════════════════════════════════════════════════════
// YÊU CẦU ĐĂNG NHẬP VÀ ĐÃ XÁC MINH EMAIL (Các phần bảo mật thông tin cốt lõi)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user/profile',           [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/user/profile/employee', [ProfileController::class, 'updateEmployee'])->name('profile.update.employee');
    Route::post('/user/profile/employer', [ProfileController::class, 'updateEmployer'])->name('profile.update.employer');
    Route::post('/user/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/user/mail',             [ProfileController::class, 'toggleMail'])->name('profile.toggle-mail');
    Route::post('/user/notification-settings', [ProfileController::class, 'updateNotificationSettings'])->name('profile.notification-settings');
});


// ═══════════════════════════════════════════════════════════════════════════
// JOB MANAGEMENT — Dành riêng cho Employer (Nhà tuyển dụng)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'employer'])->group(function () {
    Route::get('/job/create',        [JobController::class, 'create'])->name('job.create');
    Route::post('/job/store',        [JobController::class, 'store'])->name('job.store');
    Route::get('/job/manage',        [JobController::class, 'manage'])->name('job.manage');
    Route::get('/job/{id}/edit',     [JobController::class, 'edit'])->name('job.edit');
    Route::put('/job/{id}/update',   [JobController::class, 'update'])->name('job.update');
    Route::delete('/job/{id}/delete',[JobController::class, 'destroy'])->name('job.destroy');

    // Hồ sơ ứng tuyển gửi tới nhà tuyển dụng + Gói Premium thanh toán
    Route::get('/employer/jobs/{listingId}/applicants',       [ApplicationController::class, 'applicantList'])->name('employer.applicants');
    Route::get('/employer/applications/{id}',                 [ApplicationController::class, 'viewDetail'])->name('employer.application.detail');
    Route::patch('/employer/applications/{id}/status',        [ApplicationController::class, 'updateStatus'])->name('employer.application.status');
    Route::post('/shortlist/{listingId}/{applicantId}',       [ApplicationController::class, 'toggleShortlist'])->name('employer.shortlist.toggle');

    Route::get('/payment/subscription',          [PaymentController::class, 'subscriptionPage'])->name('payment.subscription');
    Route::post('/payment/subscription',         [PaymentController::class, 'initiateSubscription'])->name('payment.subscription.initiate');
    Route::get('/payment/subscription/callback', [PaymentController::class, 'subscriptionCallback'])->name('payment.subscription.callback');
    Route::get('/employer/subscription',         [PaymentController::class, 'subscriptionStatus'])->name('employer.subscription.status');
});


// ═══════════════════════════════════════════════════════════════════════════
// CV BUILDER & APPLY TRACKING — Dành riêng cho Candidate (Người tìm việc)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'candidate'])->group(function () {
    // Quản lý CV cá nhân
    Route::get('/user/cv',           [UserController::class, 'cv'])->name('user.cv');
    Route::post('/user/cv',          [UserController::class, 'updateCv'])->name('user.cv.upload')->middleware('throttle:5,1');
    Route::get('/user/cv/view',      [UserController::class, 'viewCv'])->name('user.cv.view');
    Route::get('/user/cv/create',    [UserController::class, 'createCv'])->name('user.cv.create');
    Route::post('/user/cv/preview',  [UserController::class, 'saveCv'])->name('user.cv.save');
    Route::get('/user/cv/preview',   [UserController::class, 'showPreview'])->name('user.cv.preview');
    Route::get('/user/cv/download',  [UserController::class, 'downloadPdf'])->name('user.cv.download')->middleware('throttle:10,1');
    Route::delete('/user/cv/online', [UserController::class, 'deleteOnlineCv'])->name('user.cv.delete');
    Route::post('/user/cv/ai-parse', [UserController::class, 'aiParseCv'])->name('user.cv.ai-parse')->middleware('throttle:5,60');

    // Nộp đơn ứng tuyển và Lịch sử ứng tuyển
    Route::get('/apply/{listingId}',            [ApplicationController::class, 'showForm'])->name('apply.form');
    Route::post('/apply',                       [ApplicationController::class, 'apply'])->name('apply.submit');
    Route::get('/candidate/history',            [ApplicationController::class, 'candidateHistory'])->name('candidate.history');
    Route::get('/candidate/applications/{id}',  [ApplicationController::class, 'candidateApplicationDetail'])->name('candidate.application.detail');

    // Mua Token ứng tuyển qua VNPay
    Route::get('/payment/token',          [PaymentController::class, 'tokenPurchasePage'])->name('payment.token');
    Route::post('/payment/token',         [PaymentController::class, 'initiateTokenPurchase'])->name('payment.token.initiate');
    Route::get('/payment/token/callback', [PaymentController::class, 'tokenCallback'])->name('payment.token.callback');
});


// ═══════════════════════════════════════════════════════════════════════════
// ADMIN PANEL (Yêu cầu đăng nhập & Phân quyền quản trị hệ thống)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                                              [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý thành viên (Users)
    Route::get('/users',                                         [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}/detail',                             [AdminController::class, 'userDetail'])->name('users.detail');
    Route::post('/users/{id}/role',                              [AdminController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{id}/plan',                              [AdminController::class, 'updatePlan'])->name('users.plan');
    Route::post('/users/{id}/ban',                               [AdminController::class, 'toggleBan'])->name('users.ban');
    Route::get('/users/{id}',                                    [AdminController::class, 'userShow'])->name('users.show');
    Route::delete('/users/{id}',                                 [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{id}/notification-settings',             [AdminController::class, 'updateNotificationSettings'])->name('users.notification-settings');

    // Quản lý tin tuyển dụng (Jobs)
    Route::get('/jobs',                                          [AdminController::class, 'jobs'])->name('jobs');
    Route::get('/jobs/pending',                                  [AdminController::class, 'pendingJobs'])->name('jobs.pending');
    Route::get('/jobs/{id}/detail',                              [AdminController::class, 'jobDetail'])->name('jobs.detail');
    Route::delete('/jobs/{id}',                                  [AdminController::class, 'deleteJob'])->name('jobs.delete');
    Route::post('/jobs/{id}/status',                             [AdminController::class, 'toggleJobStatus'])->name('jobs.status');
    Route::post('/jobs/{id}/approve',                            [AdminController::class, 'approveJob'])->name('jobs.approve');
    Route::post('/jobs/{id}/reject',                             [AdminController::class, 'rejectJob'])->name('jobs.reject');

    // Quản lý giao dịch nạp tiền (Transactions)
    Route::get('/transactions',                                  [AdminController::class, 'transactions'])->name('transactions');

    // Thông báo toàn hệ thống (Admin Notifications)
    Route::get('/notifications',                                 [App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/stats',                           [App\Http\Controllers\Admin\AdminNotificationController::class, 'stats'])->name('notifications.stats');
    Route::post('/notifications/broadcast',                      [App\Http\Controllers\Admin\AdminNotificationController::class, 'broadcast'])->name('notifications.broadcast');
    Route::delete('/notifications/{id}',                         [App\Http\Controllers\Admin\AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/cleanup',                        [App\Http\Controllers\Admin\AdminNotificationController::class, 'cleanup'])->name('notifications.cleanup');
    Route::get('/notifications/data',                            [App\Http\Controllers\Admin\AdminNotificationController::class, 'data'])->name('notifications.data');

    // Quản lý lịch sử Chat AI của user
    Route::get('/ai-chat',                                       [AdminController::class, 'aiConversations'])->name('ai-chat.index');
    Route::get('/ai-chat/{id}',                                  [AdminController::class, 'showAiConversation'])->name('ai-chat.show');
    Route::delete('/ai-chat/{id}',                               [AdminController::class, 'deleteAiConversation'])->name('ai-chat.destroy');
});


// ═══════════════════════════════════════════════════════════════════════════
// LEGACY PREVIEW — FakeAuth (Dùng để xem thử giao diện demo ứng viên)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware(\App\Http\Middleware\FakeAuth::class)->group(function () {
    Route::get('/applicants', fn() => view('applicants.index', ['listings' => collect([])]));
    Route::get('/applicants/{slug}', fn($slug) => view('applicants.view', [
        'listing'    => (object)['id'=>1,'title'=>'Demo','slug'=>$slug,'user'=>(object)['id'=>999,'name'=>'Demo'],'users'=>collect([])],
        'applicants' => new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 10),
    ]));
});

// ─── VNPay IPN (Webhooks cổng thanh toán server-to-server, không cần auth) ───
Route::post('/payment/token/ipn',        [PaymentController::class, 'tokenIpn'])->name('payment.token.ipn');
Route::post('/payment/subscription/ipn', [PaymentController::class, 'subscriptionIpn'])->name('payment.subscription.ipn');


// ═══════════════════════════════════════════════════════════════════════════
// FIX OAUTH REGISTER ROUTES (Đặt độc lập hoàn toàn ở cuối file)
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/auth/github/register/{role}', [App\Http\Controllers\Auth\GithubController::class, 'completeRegistration'])
    ->name('auth.github.register')
    ->where('role', 'employee|employer');
