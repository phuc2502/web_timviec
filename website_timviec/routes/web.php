<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
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
function mockListings($n = 6) {
    $titles = [
        'Senior PHP / Laravel Developer',
        'Frontend Developer (ReactJS)',
        'DevOps Engineer',
        'Mobile Developer (Flutter)',
        'Data Engineer (Python)',
        'Backend NodeJS Developer',
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
            'salary' => rand(10, 50) * 1000000,
            'feature_image' => null,
            'application_close_date' => now()->addDays(rand(5, 30)),
            'created_at' => now()->subDays(rand(1, 10)),
            'user' => $user,
            'users' => collect(array_fill(0, rand(1, 15), null)),
        ];
    }
    return collect($list);
}

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC PAGES
// ═══════════════════════════════════════════════════════════════════════════

Route::get('/', function () {
    return view('job.index', [
        'listings' => mockListings(6),
        'total' => 6,
    ]);
});

// ─── Auth ──────────────────────────────────────────────────────────────────
Route::get('/login',             fn() => view('user.login'))->name('login');
Route::get('/register',          fn() => view('user.register'));
Route::get('/register/employee', fn() => view('user.tim-register'));
Route::get('/register/employer', fn() => view('user.employer-register'));
Route::post('/login',            fn() => redirect('/dashboard'));
Route::post('/register',         fn() => redirect('/dashboard'));

// ─── Subscribe ─────────────────────────────────────────────────────────────
Route::get('/subscribe', fn() => view('subscription.index'));

// ═══════════════════════════════════════════════════════════════════════════
// PROTECTED-LIKE PAGES (dùng dữ liệu giả để xem UI)
// ═══════════════════════════════════════════════════════════════════════════

// Fake auth middleware cho preview (các trang không phải CV)
Route::middleware(\App\Http\Middleware\FakeAuth::class)->group(function () {

    // Dashboard (Tự rẽ nhánh cho Employee, Employer, Admin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/user/profile',    fn() => view('user.profile'));

    // Jobs
    Route::get('/job',         fn() => view('job.index', ['listings' => mockListings(6), 'total' => 6]));
    Route::get('/job/create',  fn() => view('job.create'));
    Route::get('/job/manage',  fn() => view('job.manage', ['listings' => mockListings(4)]));
    Route::get('/job/{id}/edit', fn($id) => view('job.edit', ['listing' => mockListing($id)]));
    Route::get('/job/show/{slug}', fn($slug) => view('job.show', ['listing' => mockListing()]));
    Route::post('/job/store',  fn() => redirect('/job/manage'));

    // Applicants
    Route::get('/applicants', fn() => view('applicants.index', ['listings' => mockListings(4)]));
    Route::get('/applicants/{slug}', fn($slug) => view('applicants.view', [
        'listing'    => mockListing(),
        'applicants' => new \Illuminate\Pagination\LengthAwarePaginator(
            collect([mockUser(), mockUser(), mockUser()]),
            3, 10
        ),
    ]));

    // Messages
    Route::get('/messages',     fn() => view('messages.index',  ['conversations' => collect([])]));
    Route::get('/messages/{id}', fn($id) => view('messages.show', [
        'conversations' => collect([]),
        'conversation'  => (object)[
            'id' => $id,
            'listing' => mockListing(),
            'employee' => mockUser('employee'),
            'employer' => mockUser('employer'),
            'messages' => collect([]),
        ],
        'messages' => collect([]),
    ]));

    // Admin Panel (Được điều khiển động từ AdminController)
    Route::get('/admin', [DashboardController::class, 'index']);
    
    // Quản lý phân quyền chức năng
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::post('/admin/users/{id}/role', [AdminController::class, 'updateRole']);
    Route::post('/admin/users/{id}/plan', [AdminController::class, 'updatePlan']);
    Route::post('/admin/users/{id}/ban', [AdminController::class, 'toggleBan']);
    
    // Quản lý phân quyền dữ liệu
    Route::get('/admin/permissions', [AdminController::class, 'permissions']);
    Route::post('/admin/permissions/transfer/{id}', [AdminController::class, 'transferOwnership']);
    
    // Quản lý tin tuyển dụng toàn hệ thống
    Route::get('/admin/jobs', [AdminController::class, 'jobs']);
    Route::delete('/admin/jobs/{id}', [AdminController::class, 'deleteJob']);
});

// ═══════════════════════════════════════════════════════════════════════════
// CV BUILDER — Protected routes (middleware thật)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'verified', 'employee'])->group(function () {
    // Upload CV
    Route::get('/user/cv',          [UserController::class, 'cv'])->name('user.cv');
    Route::post('/user/cv',         [UserController::class, 'updateCv'])->name('user.cv.upload')->middleware('throttle:5,1');
    Route::get('/user/cv/view',     [UserController::class, 'viewCv'])->name('user.cv.view');

    // Online CV
    Route::get('/user/cv/create',   [UserController::class, 'createCv'])->name('user.cv.create');
    Route::post('/user/cv/preview', [UserController::class, 'saveCv'])->name('user.cv.save');
    Route::get('/user/cv/preview',  [UserController::class, 'showPreview'])->name('user.cv.preview');
    Route::get('/user/cv/download', [UserController::class, 'downloadPdf'])->name('user.cv.download')->middleware('throttle:10,1');
    Route::delete('/user/cv/online',[UserController::class, 'deleteOnlineCv'])->name('user.cv.delete');
});

