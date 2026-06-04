<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;

// ─── Helper: Mock User ─────────────────────────────────────────────────────
function mockUser($type = 'employee') {
    return (object)[
        'id' => 1,
        'name' => $type === 'employer' ? 'Công ty ABC Tech' : 'Nguyễn Văn A',
        'email' => $type === 'employer' ? 'hr@abctech.vn' : 'nguyenvana@gmail.com',
        'user_type' => $type,
        'profile_pic' => null,
        'resume' => 'cv_nguyen_van_a.pdf',
        'about' => 'Lập trình viên Backend 3 năm kinh nghiệm Laravel, MySQL, Redis.',
        'company_name' => $type === 'employer' ? 'ABC Tech Vietnam' : null,
        'company_logo' => null,
        'plan' => 'monthly',
        'billing_ends' => now()->addDays(20),
        'user_trial' => now()->addDays(10),
        'is_banned' => false,
        'email_verified_at' => now(),
        'created_at' => now()->subDays(30),
        'listings' => collect([]),
    ];
}

// ─── Mock Listing ──────────────────────────────────────────────────────────
function mockListing($id = 1) {
    $user = mockUser('employer');
    return (object)[
        'id' => $id,
        'user_id' => $user->id,
        'title' => 'Senior PHP / Laravel Developer',
        'slug' => 'senior-php-laravel-developer',
        'description' => "Chúng tôi đang tìm kiếm Senior PHP Developer với kinh nghiệm tối thiểu 3 năm làm việc với Laravel Framework để tham gia nhóm phát triển sản phẩm SaaS của công ty.\n\n- Thiết kế và phát triển API RESTful với Laravel\n- Tối ưu hóa hiệu suất hệ thống và cơ sở dữ liệu\n- Code review và hướng dẫn các thành viên junior",
        'roles' => "- Tối thiểu 3 năm kinh nghiệm PHP/Laravel\n- Thành thạo MySQL, Redis, Docker\n- Hiểu biết về Git, CI/CD\n- Có kinh nghiệm với microservices là lợi thế",
        'predes' => "- Lương: 25-40 triệu/tháng\n- Thưởng dự án, thưởng tết 2-3 tháng lương\n- Bảo hiểm sức khỏe cao cấp\n- MacBook Pro / setup tuỳ chọn\n- Remote 2 ngày/tuần",
        'salary' => 35000000,
        'address' => 'Hà Nội',
        'job_type' => 'Full-time',
        'feature_image' => null,
        'application_close_date' => now()->addDays(15),
        'created_at' => now()->subDays(3),
        'user' => $user,
        'users' => collect([mockUser(), mockUser(), mockUser()]),
    ];
}

// ─── Mock Job List ─────────────────────────────────────────────────────────
function mockListings($n = 10) {
    $titles = [
        'Senior PHP / Laravel Developer',
        'Frontend Developer (ReactJS)',
        'DevOps Engineer',
        'Mobile Developer (Flutter)',
        'Data Engineer (Python)',
        'Backend NodeJS Developer',
        'Senior QA / Automation Tester',
        'UI/UX Product Designer',
        'Blockchain Smart Contract Dev',
        'Unity Game Developer',
    ];
    $cities = ['Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng', 'Remote'];
    $types  = ['Full-time', 'Part-time', 'Remote', 'Internship'];
    $list   = [];
    for ($i = 1; $i <= $n; $i++) {
        $user = mockUser('employer');
        $list[] = (object)[
            'id' => $i,
            'user_id' => $user->id,
            'title' => $titles[$i - 1] ?? "Vị trí số $i",
            'slug' => 'job-' . $i,
            'address' => $cities[array_rand($cities)],
            'job_type' => $types[array_rand($types)],
            'salary' => rand(0, 5) === 0 ? 0 : rand(8, 45) * 1000000, // Include some 0 (Thỏa thuận) salaries
            'feature_image' => null,
            'application_close_date' => now()->addDays(rand(5, 30)),
            'created_at' => now()->subDays(rand(1, 10)),
            'user' => $user,
            'users' => collect(array_fill(0, rand(1, 15), null)),
        ];
    }
    return collect($list);
}

function getSortedMockListings() {
    $sort = request('sort', 'newest');
    $keyword = trim(request('keyword', ''));
    $address = request('address');
    $jobType = request('job_type');
    $salaryRange = request('salary_range');
    
    // Generate 10 listings so we have a good variety of mock data
    $listings = mockListings(10);
    
    // 1. Keyword search (case-insensitive) on title/description
    if ($keyword !== '') {
        $listings = $listings->filter(function ($listing) use ($keyword) {
            $title = strtolower($listing->title);
            $kw = strtolower($keyword);
            
            // Map common tags to mock title matches
            if ($kw === 'data / ai') {
                return str_contains($title, 'data') || str_contains($title, 'python');
            }
            if ($kw === 'qa/tester') {
                return str_contains($title, 'qa') || str_contains($title, 'tester');
            }
            if ($kw === 'mobile') {
                return str_contains($title, 'mobile') || str_contains($title, 'flutter');
            }
            if ($kw === 'game') {
                return str_contains($title, 'game') || str_contains($title, 'unity');
            }
            
            return str_contains($title, $kw) || str_contains(strtolower($listing->description ?? ''), $kw);
        })->values();
    }
    
    // 2. Address/location filter (e.g. Hà Nội, Hồ Chí Minh, Đà Nẵng, Remote)
    if ($address) {
        $listings = $listings->filter(function ($listing) use ($address) {
            if (strtolower($address) === 'remote') {
                return strtolower($listing->address) === 'remote' || strtolower($listing->job_type) === 'remote' || strtolower($listing->job_type) === 'freelance';
            }
            return mb_stripos($listing->address, $address) !== false;
        })->values();
    }
    
    // 3. Job type filter (e.g. Full-time, Part-time, Freelance, Internship)
    if ($jobType) {
        $listings = $listings->filter(function ($listing) use ($jobType) {
            return strtolower($listing->job_type) === strtolower($jobType);
        })->values();
    }
    
    // 4. Salary range filter
    if ($salaryRange) {
        $listings = $listings->filter(function ($listing) use ($salaryRange) {
            $salary = $listing->salary;
            if ($salaryRange === 'Thỏa Thuận') {
                return $salary == 0;
            } elseif ($salaryRange === 'Dưới 5 triệu') {
                return $salary > 0 && $salary < 5000000;
            } elseif ($salaryRange === '5 - 10 triệu') {
                return $salary >= 5000000 && $salary <= 10000000;
            } elseif ($salaryRange === '10 - 15 triệu') {
                return $salary >= 10000000 && $salary <= 15000000;
            } elseif ($salaryRange === 'Trên 15 triệu') {
                return $salary > 15000000;
            }
            return true;
        })->values();
    }
    
    // 5. Sorting
    if ($sort === 'salary_desc') {
        return $listings->sortByDesc('salary')->values();
    } elseif ($sort === 'salary_asc') {
        return $listings->sort(function ($a, $b) {
            if ($a->salary == 0 && $b->salary != 0) return 1;
            if ($a->salary != 0 && $b->salary == 0) return -1;
            return $a->salary <=> $b->salary;
        })->values();
    } elseif ($sort === 'closing_soon') {
        return $listings->sortBy('application_close_date')->values();
    } else {
        return $listings->sortByDesc('created_at')->values();
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC PAGES
// ═══════════════════════════════════════════════════════════════════════════

Route::get('/', function () {
    return view('job.index', [
        'listings' => getSortedMockListings(),
        'total' => 6,
    ]);
});

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

    // Jobs
    Route::get('/job',         fn() => view('job.index', ['listings' => getSortedMockListings(), 'total' => 6]));
    Route::get('/job/create',  fn() => view('job.create'));
    Route::get('/job/manage',  fn() => view('job.manage', ['listings' => mockListings(4)]));
    Route::get('/job/{id}/edit', fn($id) => view('job.edit', ['listing' => mockListing($id)]));
    Route::get('/job/show/{slug}', fn($slug) => view('job.show', ['listing' => mockListing()]));
    Route::post('/job/store',  fn() => redirect('/job/manage'));

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