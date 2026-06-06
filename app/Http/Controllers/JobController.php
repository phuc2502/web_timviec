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

        // Tìm kiếm
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        // Lọc địa điểm
        if ($request->filled('address')) {
            $query->where('address', 'like', '%'.$request->address.'%');
        }

        // Lọc loại hình
        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        $listings = $query->paginate(12)->withQueryString();
        $total    = $listings->total();

        return view('job.index', compact('listings', 'total'));
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