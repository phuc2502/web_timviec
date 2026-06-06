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
if (!function_exists('mockUser')) {
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
}

// ─── Mock Listing ──────────────────────────────────────────────────────────
if (!function_exists('mockListing')) {
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
}

// ─── Mock Job List ─────────────────────────────────────────────────────────
if (!function_exists('mockListings')) {
    function mockListings($n = 30) {
        $jobPool = [
            ['title'=>'Senior PHP / Laravel Developer',    'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>35000000, 'exp_min'=>3, 'exp_max'=>7,  'job_level'=>'senior',  'roles'=>'Tối thiểu 3 năm kinh nghiệm PHP/Laravel, thành thạo MySQL, Redis, Docker, Git, CI/CD'],
            ['title'=>'Technical Lead - Java Spring Boot', 'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>50000000, 'exp_min'=>5, 'exp_max'=>10, 'job_level'=>'lead',    'roles'=>'Tối thiểu 5 năm kinh nghiệm Java Spring Boot, kinh nghiệm kiến trúc hệ thống lớn'],
            ['title'=>'DevOps Engineer',                   'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>40000000, 'exp_min'=>3, 'exp_max'=>6,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm DevOps, thành thạo Docker Kubernetes AWS Linux'],
            ['title'=>'Junior ReactJS Developer',          'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>10000000, 'exp_min'=>0, 'exp_max'=>1,  'job_level'=>'junior',  'roles'=>'0-1 năm kinh nghiệm ReactJS, biết HTML CSS JavaScript TypeScript'],
            ['title'=>'QA Automation Engineer',            'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>18000000, 'exp_min'=>1, 'exp_max'=>3,  'job_level'=>'junior',  'roles'=>'1+ năm kinh nghiệm QA automation, thành thạo Selenium hoặc Playwright'],
            ['title'=>'Product Manager - Fintech',         'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>45000000, 'exp_min'=>3, 'exp_max'=>7,  'job_level'=>'senior',  'roles'=>'3+ năm kinh nghiệm Product Manager trong lĩnh vực fintech hoặc banking'],
            ['title'=>'Golang Backend Developer',          'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>30000000, 'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm Golang, kinh nghiệm xây dựng microservices, gRPC'],
            ['title'=>'Intern Frontend (React/Vue)',       'address'=>'Hà Nội',      'job_type'=>'Internship', 'work_mode'=>'onsite',  'salary'=>3000000,  'exp_min'=>0, 'exp_max'=>0,  'job_level'=>'intern',  'roles'=>'Sinh viên năm 3-4 ngành CNTT, biết cơ bản HTML CSS JavaScript ReactJS hoặc VueJS'],
            ['title'=>'Data Analyst (SQL + Python)',       'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>22000000, 'exp_min'=>1, 'exp_max'=>3,  'job_level'=>'junior',  'roles'=>'1+ năm kinh nghiệm phân tích dữ liệu, thành thạo SQL Python Pandas Tableau'],
            ['title'=>'Network Security Engineer',         'address'=>'Hà Nội',      'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>28000000, 'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm bảo mật mạng, kinh nghiệm với firewall IDS/IPS penetration testing'],
            ['title'=>'Frontend Developer (ReactJS)',      'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>28000000, 'exp_min'=>2, 'exp_max'=>4,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm ReactJS, thành thạo Redux TypeScript REST API'],
            ['title'=>'Backend Developer Python/Django',   'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'remote',  'salary'=>20000000, 'exp_min'=>1, 'exp_max'=>3,  'job_level'=>'junior',  'roles'=>'1+ năm kinh nghiệm Python Django, biết PostgreSQL Redis Docker'],
            ['title'=>'iOS Developer (Swift)',             'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>32000000, 'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm iOS Swift, kinh nghiệm publish App Store, biết SwiftUI'],
            ['title'=>'AI/ML Engineer',                   'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>42000000, 'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'senior',  'roles'=>'2+ năm kinh nghiệm Machine Learning, thành thạo Python TensorFlow PyTorch scikit-learn'],
            ['title'=>'UI/UX Product Designer',           'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>20000000, 'exp_min'=>1, 'exp_max'=>4,  'job_level'=>'middle',  'roles'=>'1+ năm kinh nghiệm thiết kế UI/UX, thành thạo Figma Adobe XD, có portfolio'],
            ['title'=>'Blockchain Smart Contract Dev',     'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'remote',  'salary'=>0,        'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'senior',  'roles'=>'2+ năm kinh nghiệm Solidity Ethereum Web3.js, kinh nghiệm audit smart contract'],
            ['title'=>'Part-time NodeJS Developer',       'address'=>'Hồ Chí Minh', 'job_type'=>'Part-time',  'work_mode'=>'remote',  'salary'=>12000000, 'exp_min'=>1, 'exp_max'=>3,  'job_level'=>'junior',  'roles'=>'1+ năm kinh nghiệm NodeJS Express, biết MongoDB PostgreSQL REST API'],
            ['title'=>'Database Administrator (MySQL)',    'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>25000000, 'exp_min'=>3, 'exp_max'=>6,  'job_level'=>'senior',  'roles'=>'3+ năm kinh nghiệm MySQL DBA, thành thạo query optimization replication backup'],
            ['title'=>'Scrum Master / Agile Coach',       'address'=>'Hồ Chí Minh', 'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>38000000, 'exp_min'=>3, 'exp_max'=>7,  'job_level'=>'senior',  'roles'=>'3+ năm kinh nghiệm Scrum Master, chứng chỉ CSM hoặc PSM, kinh nghiệm coaching team Agile'],
            ['title'=>'Intern Data Science',              'address'=>'Hồ Chí Minh', 'job_type'=>'Internship', 'work_mode'=>'onsite',  'salary'=>4000000,  'exp_min'=>0, 'exp_max'=>0,  'job_level'=>'intern',  'roles'=>'Sinh viên năm 3-4 ngành Toán Tin hoặc CNTT, biết Python cơ bản và thống kê'],
            ['title'=>'Mobile Developer (Flutter)',        'address'=>'Đà Nẵng',     'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>25000000, 'exp_min'=>1, 'exp_max'=>3,  'job_level'=>'junior',  'roles'=>'1+ năm kinh nghiệm Flutter Dart, biết tích hợp REST API Firebase'],
            ['title'=>'Unity Game Developer',             'address'=>'Đà Nẵng',     'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>20000000, 'exp_min'=>1, 'exp_max'=>4,  'job_level'=>'middle',  'roles'=>'1+ năm kinh nghiệm Unity C#, có game đã publish là lợi thế'],
            ['title'=>'Full-stack PHP Developer',         'address'=>'Đà Nẵng',     'job_type'=>'Full-time',  'work_mode'=>'hybrid',  'salary'=>18000000, 'exp_min'=>1, 'exp_max'=>3,  'job_level'=>'junior',  'roles'=>'1+ năm kinh nghiệm PHP Laravel, biết HTML CSS JavaScript MySQL'],
            ['title'=>'Embedded Systems Engineer',        'address'=>'Đà Nẵng',     'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>22000000, 'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm lập trình nhúng C/C++, kinh nghiệm với STM32 Arduino Linux Embedded'],
            ['title'=>'Fresher Backend Laravel',          'address'=>'Đà Nẵng',     'job_type'=>'Full-time',  'work_mode'=>'onsite',  'salary'=>8000000,  'exp_min'=>0, 'exp_max'=>1,  'job_level'=>'fresher', 'roles'=>'Fresher hoặc dưới 1 năm kinh nghiệm PHP Laravel, tốt nghiệp đại học CNTT'],
            ['title'=>'Remote Senior NodeJS Developer',   'address'=>'Remote',       'job_type'=>'Remote',     'work_mode'=>'remote',  'salary'=>0,        'exp_min'=>4, 'exp_max'=>8,  'job_level'=>'senior',  'roles'=>'4+ năm kinh nghiệm NodeJS, thành thạo microservices Docker AWS, lương thỏa thuận hấp dẫn'],
            ['title'=>'Remote TypeScript Developer',      'address'=>'Remote',       'job_type'=>'Remote',     'work_mode'=>'remote',  'salary'=>30000000, 'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm TypeScript ReactJS hoặc NodeJS, có thể làm việc độc lập'],
            ['title'=>'Remote Cloud Architect (AWS)',     'address'=>'Remote',       'job_type'=>'Remote',     'work_mode'=>'remote',  'salary'=>60000000, 'exp_min'=>5, 'exp_max'=>10, 'job_level'=>'lead',    'roles'=>'5+ năm kinh nghiệm AWS cloud architecture, chứng chỉ AWS Solutions Architect'],
            ['title'=>'Remote Kotlin Android Developer',  'address'=>'Remote',       'job_type'=>'Remote',     'work_mode'=>'remote',  'salary'=>28000000, 'exp_min'=>2, 'exp_max'=>5,  'job_level'=>'middle',  'roles'=>'2+ năm kinh nghiệm Android Kotlin, kinh nghiệm publish Google Play Store'],
            ['title'=>'Remote Rust Systems Developer',    'address'=>'Remote',       'job_type'=>'Remote',     'work_mode'=>'remote',  'salary'=>0,        'exp_min'=>3, 'exp_max'=>7,  'job_level'=>'senior',  'roles'=>'3+ năm kinh nghiệm Rust, kinh nghiệm hệ thống low-level hoặc WebAssembly, lương thỏa thuận'],
        ];

        $companies = [
            'FPT Software', 'VNG Corporation', 'TopDev Vietnam', 'NashTech Global',
            'TechStartup JSC', 'Viettel Cyber Security', 'Got It Inc.', 'CodeLab Studio',
        ];

        $list  = [];
        $total = min($n, count($jobPool));
        for ($i = 0; $i < $total; $i++) {
            $job  = $jobPool[$i];
            $user = mockUser('employer');
            $user->company_name = $companies[$i % count($companies)];
            $list[] = (object)[
                'id'                     => $i + 1,
                'user_id'                => $user->id,
                'title'                  => $job['title'],
                'slug'                   => 'job-' . ($i + 1),
                'description'            => 'Mô tả chi tiết về vị trí ' . $job['title'],
                'roles'                  => $job['roles'],
                'predes'                 => 'Lương cạnh tranh, bảo hiểm đầy đủ, môi trường làm việc chuyên nghiệp',
                'address'                => $job['address'],
                'job_type'               => $job['job_type'],
                'work_mode'              => $job['work_mode'],
                'salary'                 => $job['salary'],
                'experience_years_min'   => $job['exp_min'],
                'experience_years_max'   => $job['exp_max'],
                'job_level'              => $job['job_level'],
                'feature_image'          => null,
                'application_close_date' => now()->addDays(rand(5, 45)),
                'created_at'             => now()->subDays(rand(0, 14)),
                'user'                   => $user,
                'users'                  => collect(array_fill(0, rand(1, 20), null)),
            ];
        }
        return collect($list);
    }
}

if (!function_exists('getSortedMockListings')) {
    function getSortedMockListings() {
        $sort        = request('sort', 'newest');
        $keyword     = trim(request('keyword', ''));
        $address     = request('address');
        $jobType     = request('job_type');
        $workMode    = request('work_mode');
        $salaryRange = request('salary_range');
        $expRange    = request('exp_range');
        $jobLevel    = request('job_level');

        $listings = mockListings(30);

        if ($keyword !== '') {
            $listings = $listings->filter(function ($listing) use ($keyword) {
                $kw = mb_strtolower($keyword);
                return str_contains(mb_strtolower($listing->title),       $kw)
                    || str_contains(mb_strtolower($listing->roles       ?? ''), $kw)
                    || str_contains(mb_strtolower($listing->predes      ?? ''), $kw)
                    || str_contains(mb_strtolower($listing->description ?? ''), $kw);
            })->values();
        }

        if ($address) {
            $listings = $listings->filter(function ($listing) use ($address) {
                return mb_stripos($listing->address, $address) !== false;
            })->values();
        }

        if ($jobType) {
            $listings = $listings->filter(function ($listing) use ($jobType) {
                return mb_strtolower($listing->job_type) === mb_strtolower($jobType);
            })->values();
        }

        if ($workMode) {
            $listings = $listings->filter(function ($listing) use ($workMode) {
                return mb_strtolower($listing->work_mode ?? '') === mb_strtolower($workMode);
            })->values();
        }

        if ($salaryRange) {
            $listings = $listings->filter(function ($listing) use ($salaryRange) {
                $s = $listing->salary;
                return match ($salaryRange) {
                    'Thỏa Thuận'    => $s == 0,
                    'Dưới 5 triệu'  => $s > 0 && $s < 5000000,
                    '5 - 10 triệu'  => $s >= 5000000  && $s <= 10000000,
                    '10 - 15 triệu' => $s >= 10000000 && $s <= 15000000,
                    'Trên 15 triệu' => $s > 15000000,
                    default         => true,
                };
            })->values();
        }

        if ($expRange) {
            $listings = $listings->filter(function ($listing) use ($expRange) {
                $min = $listing->experience_years_min ?? 0;
                $max = $listing->experience_years_max ?? 99;
                return match ($expRange) {
                    'Chưa có KN'  => $min == 0,
                    'Dưới 1 năm'  => $min <= 1,
                    '1 - 3 năm'   => $min <= 3 && $max >= 1,
                    '3 - 5 năm'   => $min <= 5 && $max >= 3,
                    'Trên 5 năm'  => $min >= 5,
                    default       => true,
                };
            })->values();
        }

        if ($jobLevel) {
            $listings = $listings->filter(function ($listing) use ($jobLevel) {
                return mb_strtolower($listing->job_level ?? '') === mb_strtolower($jobLevel);
            })->values();
        }

        return match ($sort) {
            'salary_desc'  => $listings->sortByDesc('salary')->values(),
            'salary_asc'   => $listings->sort(function ($a, $b) {
                if ($a->salary == 0 && $b->salary != 0) return 1;
                if ($a->salary != 0 && $b->salary == 0) return -1;
                return $a->salary <=> $b->salary;
            })->values(),
            'closing_soon' => $listings->sortBy('application_close_date')->values(),
            default        => $listings->sortByDesc('created_at')->values(),
        };
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC PAGES
// ═══════════════════════════════════════════════════════════════════════════

Route::get('/', [JobController::class, 'index']);
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
// AUTH REQUIRED
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/user/profile', fn() => view('user.profile'));

    // ── Messages ──────────────────────────────────────────────────────────
    Route::get('/messages',            [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/start',     [\App\Http\Controllers\MessageController::class, 'findOrCreate'])->name('messages.start');
    Route::get('/messages/{id}',       [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{id}/send', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store')->middleware('throttle:30,1');
    Route::get('/messages/{id}/poll',  [\App\Http\Controllers\MessageController::class, 'poll'])->name('messages.poll');
    Route::delete('/messages/{id}',    [\App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');
});

// ═══════════════════════════════════════════════════════════════════════════
// JOB ROUTES
// ═══════════════════════════════════════════════════════════════════════════

Route::get('/job',               [JobController::class, 'index'])->name('job.index');
Route::get('/job/create',        [JobController::class, 'create'])->name('job.create');
Route::get('/job/manage',        [JobController::class, 'manage'])->name('job.manage');
Route::get('/job/show/{slug}',   [JobController::class, 'show'])->name('job.show');
Route::post('/job/store',        [JobController::class, 'store'])->name('job.store');
Route::get('/job/{id}/edit',     [JobController::class, 'edit'])->name('job.edit');
Route::put('/job/{id}/update',   [JobController::class, 'update'])->name('job.update');
Route::delete('/job/{id}/delete',[JobController::class, 'destroy'])->name('job.destroy');

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
    Route::get('/apply/{listingId}',           [ApplicationController::class, 'showForm'])->name('apply.form');
    Route::post('/apply',                      [ApplicationController::class, 'apply'])->name('apply.submit');
    Route::get('/candidate/history',           [ApplicationController::class, 'candidateHistory'])->name('candidate.history');
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
    Route::get('/employer/applications/{id}',              [ApplicationController::class, 'viewDetail'])->name('employer.application.detail');
    Route::patch('/employer/applications/{id}/status',     [ApplicationController::class, 'updateStatus'])->name('employer.application.status');

    Route::get('/payment/subscription',          [PaymentController::class, 'subscriptionPage'])->name('payment.subscription');
    Route::post('/payment/subscription',         [PaymentController::class, 'initiateSubscription'])->name('payment.subscription.initiate');
    Route::get('/payment/subscription/callback', [PaymentController::class, 'subscriptionCallback'])->name('payment.subscription.callback');
    Route::get('/employer/subscription',         [PaymentController::class, 'subscriptionStatus'])->name('employer.subscription.status');
});

// ═══════════════════════════════════════════════════════════════════════════
// ADMIN
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(\App\Http\Middleware\FakeAuth::class)->group(function () {
    Route::get('/applicants', fn() => view('applicants.index', ['listings' => collect([])]));
    Route::get('/applicants/{slug}', function($slug) {
    $listing = \App\Models\Listing::with(['user', 'users'])->where('slug', $slug)->firstOrFail();
    $applicants = $listing->users()->paginate(10);
    return view('applicants.view', compact('listing', 'applicants'));
});
   
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

// ─── VNPay IPN ────────────────────────────────────────────────────────────
Route::post('/payment/token/ipn',        [PaymentController::class, 'tokenIpn'])->name('payment.token.ipn');
Route::post('/payment/subscription/ipn', [PaymentController::class, 'subscriptionIpn'])->name('payment.subscription.ipn');
