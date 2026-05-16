<?php

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
        $list[] = (object)[
            'id' => $i,
            'title' => $titles[$i - 1] ?? "Vị trí số $i",
            'slug' => 'job-' . $i,
            'address' => $cities[array_rand($cities)],
            'job_type' => $types[array_rand($types)],
            'salary' => rand(10, 50) * 1000000,
            'feature_image' => null,
            'application_close_date' => now()->addDays(rand(5, 30)),
            'created_at' => now()->subDays(rand(1, 10)),
            'user' => mockUser('employer'),
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
Route::get('/login',             fn() => view('user.login'));
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

// Fake auth middleware cho preview
Route::middleware(\App\Http\Middleware\FakeAuth::class)->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard', [
        'totalJobs' => 12, 'totalApplicants' => 47,
        'shortlisted' => 8, 'activeJobs' => 9,
        'recentJobs' => mockListings(4),
        'totalRevenue' => 5,
    ]));

    // Profile & CV
    Route::get('/user/profile',    fn() => view('user.profile'));
    Route::get('/user/cv',         fn() => view('user.cv'));
    Route::get('/user/cv/create',  fn() => view('user.create-cv'));

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

    // Admin
    Route::get('/admin', fn() => view('admin.index', [
        'totalUsers' => 128, 'totalJobs' => 47,
        'totalApplications' => 312, 'totalRevenue' => 23,
        'totalEmployees' => 95, 'totalEmployers' => 33,
        'recentUsers' => collect([mockUser(), mockUser('employer'), mockUser()]),
        'recentJobs'  => mockListings(4),
    ]));
    Route::get('/admin/users', fn() => view('admin.users', [
        'users' => new \Illuminate\Pagination\LengthAwarePaginator(
            collect([mockUser(), mockUser('employer'), mockUser(), mockUser('employer')]),
            4, 20
        ),
    ]));
    Route::get('/admin/jobs', fn() => view('admin.jobs', [
        'listings' => new \Illuminate\Pagination\LengthAwarePaginator(mockListings(4)->all(), 4, 20),
    ]));
});
