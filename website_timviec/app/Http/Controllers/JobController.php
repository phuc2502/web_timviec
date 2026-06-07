<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class JobController extends Controller
{
    /**
     * GET /job — Danh sách tất cả công việc
     */
    public function index(Request $request)
    {
        $query = Listing::with('user')
            ->where(function ($q) {
                $q->whereNull('application_close_date')
                  ->orWhere('application_close_date', '>=', now());
            })
            // Ẩn bài đăng của Free account đã nhận đủ 3 ứng viên:
            // Điều kiện: employer là Premium  →  hiển thị luôn
            //            employer là Free     →  chỉ hiển thị khi số đơn < 3
            ->where(function ($q) {
                $q->whereHas('user', function ($u) {
                      // Premium: billing_ends > now() AND status = active
                      $u->whereHas('subscriptions', function ($s) {
                          $s->where('status', 'active')
                            ->where('billing_ends', '>', now());
                      });
                  })
                  ->orWhere(function ($q2) {
                      // Free: chưa đủ 3 ứng viên
                      $q2->whereDoesntHave('user', function ($u) {
                              $u->whereHas('subscriptions', function ($s) {
                                  $s->where('status', 'active')
                                    ->where('billing_ends', '>', now());
                              });
                          })
                          ->whereHas('applications', function ($a) {}, '<', 3);
                  });
            })
            ->latest();

        // ── 1. Keyword: tìm kiếm TOÀN DIỆN trên tất cả trường liên quan ──
        $keyword = $request->filled('keyword') ? trim($request->keyword)
                 : ($request->filled('search')  ? trim($request->search) : null);

        if ($keyword) {
            // Chuẩn hóa keyword để tìm kiếm (lowercase để so sánh)
            $kw = $keyword;

            // Map từ khóa tiếng Việt phổ biến sang giá trị DB
            $jobTypeMaps = [
                'toàn thời gian' => 'Full-time',
                'toan thoi gian' => 'Full-time',
                'full time'      => 'Full-time',
                'fulltime'       => 'Full-time',
                'bán thời gian'  => 'Part-time',
                'ban thoi gian'  => 'Part-time',
                'part time'      => 'Part-time',
                'parttime'       => 'Part-time',
                'thực tập'       => 'Internship',
                'thuc tap'       => 'Internship',
                'intern'         => 'Internship',
                'thực tập sinh'  => 'Internship',
                'freelance'      => 'Freelance',
                'tự do'          => 'Freelance',
                'tu do'          => 'Freelance',
                'remote'         => 'Remote',
                'từ xa'          => 'Remote',
                'tu xa'          => 'Remote',
            ];

            $workModeMaps = [
                'văn phòng'  => 'onsite',
                'van phong'  => 'onsite',
                'onsite'     => 'onsite',
                'tại chỗ'    => 'onsite',
                'tai cho'    => 'onsite',
                'hybrid'     => 'hybrid',
                'kết hợp'    => 'hybrid',
                'ket hop'    => 'hybrid',
                'remote'     => 'remote',
                'làm từ xa'  => 'remote',
                'lam tu xa'  => 'remote',
                'làm ở nhà'  => 'remote',
                'lam o nha'  => 'remote',
            ];

            $levelMaps = [
                'intern'        => 'intern',
                'thực tập'      => 'intern',
                'thuc tap'      => 'intern',
                'fresher'       => 'fresher',
                'mới ra trường' => 'fresher',
                'moi ra truong' => 'fresher',
                'junior'        => 'junior',
                'trẻ'           => 'junior',
                'tre'           => 'junior',
                'middle'        => 'middle',
                'mid'           => 'middle',
                'senior'        => 'senior',
                'cao cấp'       => 'senior',
                'cao cap'       => 'senior',
                'lead'          => 'lead',
                'trưởng nhóm'   => 'lead',
                'truong nhom'   => 'lead',
                'manager'       => 'lead',
                'quản lý'       => 'lead',
                'quan ly'       => 'lead',
            ];

            // Tìm giá trị DB tương ứng từ keyword
            $kwLower          = mb_strtolower($kw);
            $mappedJobType    = $jobTypeMaps[$kwLower]  ?? null;
            $mappedWorkMode   = $workModeMaps[$kwLower] ?? null;
            $mappedLevel      = $levelMaps[$kwLower]    ?? null;

            $query->where(function ($q) use ($kw, $mappedJobType, $mappedWorkMode, $mappedLevel) {
                // Trường text trực tiếp trong listings
                $q->where('title',       'like', '%' . $kw . '%')
                  ->orWhere('description','like', '%' . $kw . '%')
                  ->orWhere('requirements','like', '%' . $kw . '%')
                  ->orWhere('benefits',    'like', '%' . $kw . '%')
                  ->orWhere('address',    'like', '%' . $kw . '%')
                  ->orWhere('job_type',   'like', '%' . $kw . '%')
                  ->orWhere('work_mode',  'like', '%' . $kw . '%')
                  ->orWhere('job_level',  'like', '%' . $kw . '%');

                // Tìm theo tên công ty / tên nhà tuyển dụng (join users)
                $q->orWhereHas('user', function ($uq) use ($kw) {
                    $uq->where('company_name', 'like', '%' . $kw . '%')
                       ->orWhere('name',        'like', '%' . $kw . '%');
                });

                // Map tiếng Việt → giá trị enum trong DB
                if ($mappedJobType) {
                    $q->orWhere('job_type', $mappedJobType);
                }
                if ($mappedWorkMode) {
                    $q->orWhere('work_mode', $mappedWorkMode);
                }
                if ($mappedLevel) {
                    $q->orWhere('job_level', $mappedLevel);
                }
            });
        }

        // ── 2. Địa điểm ─────────────────────────────────────────────────
        if ($request->filled('address')) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }

        // ── 3. Loại hình công việc ───────────────────────────────────────
        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        // ── 4. Work mode (onsite / remote / hybrid) ──────────────────────
        if ($request->filled('work_mode')) {
            $query->where('work_mode', $request->work_mode);
        }

        // ── 5. Khoảng lương ─────────────────────────────────────────────
        if ($request->filled('salary_range')) {
            match ($request->salary_range) {
                'Thỏa Thuận'    => $query->where('salary', 0),
                'Dưới 5 triệu'  => $query->where('salary', '>', 0)->where('salary', '<', 5000000),
                '5 - 10 triệu'  => $query->whereBetween('salary', [5000000, 10000000]),
                '10 - 15 triệu' => $query->whereBetween('salary', [10000000, 15000000]),
                'Trên 15 triệu' => $query->where('salary', '>', 15000000),
                default         => null,
            };
        }

        // ── 6. Kinh nghiệm (năm) ────────────────────────────────────────
        if ($request->filled('exp_range')) {
            match ($request->exp_range) {
                'Chưa có KN'   => $query->where(fn($q) => $q->whereNull('experience_years_min')->orWhere('experience_years_min', 0)),
                'Dưới 1 năm'   => $query->where(fn($q) => $q->whereNull('experience_years_min')->orWhere('experience_years_min', '<=', 1)),
                '1 - 3 năm'    => $query->where(fn($q) => $q->whereNull('experience_years_min')->orWhere('experience_years_min', '<=', 3))
                                        ->where(fn($q) => $q->whereNull('experience_years_max')->orWhere('experience_years_max', '>=', 1)),
                '3 - 5 năm'    => $query->where(fn($q) => $q->whereNull('experience_years_min')->orWhere('experience_years_min', '<=', 5))
                                        ->where(fn($q) => $q->whereNull('experience_years_max')->orWhere('experience_years_max', '>=', 3)),
                'Trên 5 năm'   => $query->where(fn($q) => $q->whereNull('experience_years_min')->orWhere('experience_years_min', '>=', 5)),
                default        => null,
            };
        }

        // ── 7. Cấp độ công việc ─────────────────────────────────────────
        if ($request->filled('job_level')) {
            $query->where('job_level', $request->job_level);
        }

        // ── 8. Sắp xếp ──────────────────────────────────────────────────
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'salary_desc'  => $query->orderByRaw('CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary DESC'),
            'salary_asc'   => $query->orderByRaw('CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary ASC'),
            'closing_soon' => $query->orderBy('application_close_date', 'asc'),
            default        => $query->latest(),
        };

        $listings  = $query->paginate(12)->withQueryString();
        $totalJobs = $listings->total();

        return view('job.index', compact('listings', 'totalJobs'));
    }

    /**
     * GET /job/show/{slug} — Chi tiết công việc
     */
    public function show(string $slug)
    {
        $listing = Listing::with(['user', 'users'])->where('slug', $slug)->firstOrFail();

        // Kiểm tra ứng viên đã nộp đơn chưa
        $existingApplication = null;
        $applyCount          = 0;
        if (auth()->check() && auth()->user()->user_type === 'employee') {
            $allApps = \App\Models\Application::where('user_id', auth()->id())
                ->where('listing_id', $listing->id)
                ->orderBy('id')
                ->get();

            $existingApplication = $allApps->last(); // bản ghi mới nhất (NULL nếu chưa ứng tuyển)
            // apply_round = tổng số lần đã bấm nộp (kể cả các lần update)
            $applyCount          = $existingApplication ? ($existingApplication->apply_round ?? 1) : 0;
            // Hồ sơ bị khoá: NTD đã xử lý (status ≠ submitted) → không cho ứng tuyển lại
            $isStatusLocked      = $existingApplication && $existingApplication->status !== 'submitted';
        }

        // Kiểm tra job của Free account đã nhận đủ 3 ứng viên chưa
        $applicantLimitReached = $listing->applicantLimitReached();

        // Ứng viên đã ứng tuyển đủ 3 lần → disable nút
        // Disable nút nếu: đủ 3 lần HOẶC hồ sơ đã bị NTD xử lý
        $reapplyDisabled = ($applyCount >= \App\Models\Application::MAX_APPLY_ROUNDS)
                        || $isStatusLocked;

        return view('job.show', compact(
            'listing', 'existingApplication', 'applicantLimitReached',
            'applyCount', 'reapplyDisabled', 'isStatusLocked'
        ));
    }

    /**
     * GET /job/create — Form đăng tin (employer)
     */
    public function create()
    {
        return view('job.create');
    }

    /**
     * POST /job/store — Lưu tin tuyển dụng mới
     */
    public function store(Request $request)
    {
        $employer = auth()->user();

        // ── KIỂM TRA GIỚI HẠN FREE ACCOUNT ──────────────────────────────
        if (!$employer->isPremium()) {
            $postCount = $employer->monthlyPostCount();
            if ($postCount >= 3) {
                return redirect()->route('payment.subscription')
                    ->with('warning', "Bạn đã dùng hết {$postCount}/3 lượt đăng tin miễn phí tháng này. Nâng cấp Premium để đăng không giới hạn!");
            }
        }
        // ─────────────────────────────────────────────────────────────────
        $request->validate([
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['required', 'string'],
            'address'               => ['required', 'string', 'max:255'],
            'job_type'              => ['required', 'string'],
            'salary'                => ['nullable', 'integer', 'min:0'],
            'application_close_date'=> ['nullable', 'date', 'after:today'],
        ], [
            'title.required'       => 'Vui lòng nhập tiêu đề công việc.',
            'description.required' => 'Vui lòng nhập mô tả công việc.',
            'address.required'     => 'Vui lòng nhập địa điểm.',
            'job_type.required'    => 'Vui lòng chọn loại hình công việc.',
        ]);

        try {
            $listing = Listing::create([
                'user_id'               => auth()->id(),
                'title'                 => $request->title,
                'slug'                  => Str::slug($request->title) . '-' . Str::random(6),
                'description'           => $request->description,
                'requirements'          => $request->requirements,
                'benefits'              => $request->benefits,
                'salary'                => $request->salary ?? 0,
                'address'               => $request->address,
                'job_type'              => $request->job_type,
                'work_mode'             => $request->work_mode ?? 'onsite',
                'job_level'             => $request->job_level,
                'experience_years_min'  => $request->experience_years_min,
                'experience_years_max'  => $request->experience_years_max,
                'application_close_date'=> $request->application_close_date,
            ]);

            return redirect()->route('job.manage')
                ->with('success', 'Đăng tin tuyển dụng thành công!');

        } catch (\Throwable $e) {
            Log::error('Job store failed: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * GET /job/manage — Quản lý tin đăng của employer
     */
    public function manage()
    {
        $listings = Listing::withCount('applications')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('job.manage', compact('listings'));
    }

    /**
     * GET /job/{id}/edit — Form chỉnh sửa tin
     */
    public function edit(int $id)
    {
        $listing = Listing::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('job.edit', compact('listing'));
    }

    /**
     * PUT /job/{id}/update — Cập nhật tin tuyển dụng
     */
    public function update(Request $request, int $id)
    {
        $listing = Listing::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['required', 'string'],
            'address'               => ['required', 'string', 'max:255'],
            'job_type'              => ['required', 'string'],
            'salary'                => ['nullable', 'integer', 'min:0'],
            'application_close_date'=> ['nullable', 'date'],
        ]);

        try {
            $listing->update([
                'title'                 => $request->title,
                'description'           => $request->description,
                'requirements'          => $request->requirements,
                'benefits'              => $request->benefits,
                'salary'                => $request->salary ?? 0,
                'address'               => $request->address,
                'job_type'              => $request->job_type,
                'work_mode'             => $request->work_mode ?? $listing->work_mode,
                'job_level'             => $request->job_level ?? $listing->job_level,
                'experience_years_min'  => $request->experience_years_min ?? $listing->experience_years_min,
                'experience_years_max'  => $request->experience_years_max ?? $listing->experience_years_max,
                'application_close_date'=> $request->application_close_date,
            ]);

            return redirect()->route('job.manage')
                ->with('success', 'Cập nhật tin tuyển dụng thành công!');

        } catch (\Throwable $e) {
            Log::error('Job update failed: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * DELETE /job/{id}/delete — Xóa tin tuyển dụng
     */
    public function destroy(int $id)
    {
        $listing = Listing::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $listing->delete();

        return redirect()->route('job.manage')
            ->with('success', 'Đã xóa tin tuyển dụng.');
    }
}