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
            });

        // ── 1. Keyword: tìm trên title, description, roles, predes ──────
        $keyword = $request->filled('keyword') ? $request->keyword
                 : ($request->filled('search')  ? $request->search : null);
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title',       'like', '%' . $keyword . '%')
                  ->orWhere('description','like', '%' . $keyword . '%')
                  ->orWhere('roles',      'like', '%' . $keyword . '%')
                  ->orWhere('predes',     'like', '%' . $keyword . '%');
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
                'Dưới 1 năm'   => $query->where('experience_years_min', '<=', 1),
                '1 - 3 năm'    => $query->where('experience_years_min', '<=', 3)->where(fn($q) => $q->whereNull('experience_years_max')->orWhere('experience_years_max', '>=', 1)),
                '3 - 5 năm'    => $query->where('experience_years_min', '<=', 5)->where(fn($q) => $q->whereNull('experience_years_max')->orWhere('experience_years_max', '>=', 3)),
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

        // Kiểm tra ứng viên đã nộp đơn chưa (dùng bảng applications mới)
        $existingApplication = null;
        if (auth()->check() && auth()->user()->user_type === 'employee') {
            $existingApplication = \App\Models\Application::where('user_id', auth()->id())
                ->where('listing_id', $listing->id)
                ->first();
        }

        return view('job.show', compact('listing', 'existingApplication'));
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
                'roles'                 => $request->roles,
                'predes'                => $request->predes,
                'salary'                => $request->salary ?? 0,
                'address'               => $request->address,
                'job_type'              => $request->job_type,
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
        $listings = Listing::with('users')
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
                'roles'                 => $request->roles,
                'predes'                => $request->predes,
                'salary'                => $request->salary ?? 0,
                'address'               => $request->address,
                'job_type'              => $request->job_type,
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