<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyJobRequest;
use App\Models\Application;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\Listing;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $service) {}

    // ════════════════════════════════════════════════════════════════════════
    // PHÍA ỨNG VIÊN
    // ════════════════════════════════════════════════════════════════════════

    /**
     * GET /apply/{listingId} — Form ứng tuyển, tự động gợi ý CV gần nhất.
     */
    public function showForm(int $listingId)
    {
        $listing    = Listing::findOrFail($listingId);
        $candidate  = auth()->user();
        $suggestedCv = $this->service->suggestLatestCv($candidate);

        return view('application.form', [
            'listing'     => $listing,
            'listingId'   => $listingId,
            'suggestedCv' => $suggestedCv,
        ]);
    }

    /**
     * POST /apply — Xử lý ứng tuyển công việc.
     */
    public function apply(ApplyJobRequest $request)
    {
        $candidate = auth()->user();

        try {
            $application = $this->service->apply($candidate, $request->validated());

            return redirect()
                ->route('candidate.history')
                ->with('success', 'Ứng tuyển thành công!');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Apply job failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
        }
    }

    /**
     * GET /candidate/history — Lịch sử ứng tuyển của ứng viên.
     */
    public function candidateHistory()
    {
        $candidate    = auth()->user();
        $applications = $this->service->candidateHistory($candidate);

        return view('application.history', compact('applications'));
    }

    /**
     * GET /candidate/applications/{id} — Ứng viên xem chi tiết đơn của chính mình.
     */
    public function candidateApplicationDetail(int $applicationId)
    {
        $candidate   = auth()->user();
        $application = Application::with(['listing.user', 'cv'])
            ->where('user_id', $candidate->id)
            ->findOrFail($applicationId);

        return view('application.candidate-detail', compact('application'));
    }

    // ════════════════════════════════════════════════════════════════════════
    // PHÍA NHÀ TUYỂN DỤNG
    // ════════════════════════════════════════════════════════════════════════

    /**
     * GET /employer/jobs/{listingId}/applicants — Danh sách ứng viên theo listing.
     */
    public function applicantList(Request $request, int $listingId)
    {
        $employer     = auth()->user();
        $applications = $this->service->listByJob($employer, $listingId);

        return view('application.applicant-list', compact('applications', 'listingId'));
    }

    /**
     * GET /employer/applications/{id} — Chi tiết CV ứng viên.
     * Tự động chuyển trạng thái submitted → viewed.
     */
    public function viewDetail(int $applicationId)
    {
        $employer = auth()->user();

        try {
            $application = $this->service->viewApplicationDetail($employer, $applicationId);
            return view('application.detail', compact('application'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            abort(404, 'Đơn ứng tuyển không tồn tại hoặc không thuộc quyền quản lý của bạn.');
        }
    }

    /**
     * PATCH /employer/applications/{id}/status — Cập nhật trạng thái ứng tuyển.
     */
    public function updateStatus(UpdateApplicationStatusRequest $request, int $applicationId)
    {
        $employer  = auth()->user();
        $validated = $request->validated();
        $newStatus = $validated['status'];
        $extra     = ['interview_scheduled_at' => $validated['interview_scheduled_at'] ?? null];

        try {
            $this->service->updateStatus($employer, $applicationId, $newStatus, $extra);

            return back()->with('success', 'Cập nhật trạng thái thành công!');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("UpdateStatus failed: " . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
        }
    }
}
