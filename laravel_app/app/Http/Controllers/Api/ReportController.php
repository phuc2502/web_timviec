<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingReport;
use App\Services\ListingStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ListingStateMachine $stateMachine
    ) {}

    /**
     * Ứng viên tạo báo cáo vi phạm cho listing.
     */
    public function store(Request $request, int $listingId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|in:fake_job,scam,inappropriate,misleading',
            'description' => 'nullable|string|max:1000',
        ], [
            'reason.required' => 'Lý do báo cáo là bắt buộc.',
            'reason.in' => 'Lý do báo cáo không hợp lệ.',
        ]);

        $listing = Listing::where('status', 'active')->findOrFail($listingId);

        // Check duplicate report
        $exists = ListingReport::where('listing_id', $listing->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Bạn đã báo cáo tin này trước đó.',
            ], 422);
        }

        // Create report
        ListingReport::create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        // Auto-pause if >= 5 pending reports
        $pendingCount = ListingReport::where('listing_id', $listing->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount >= 5 && $listing->status === 'active') {
            $this->stateMachine->transition($listing, 'paused');
        }

        return response()->json(['message' => 'Báo cáo đã được gửi. Cảm ơn bạn!'], 201);
    }

    /**
     * Admin xem danh sách báo cáo (filter theo status).
     */
    public function index(Request $request): JsonResponse
    {
        $query = ListingReport::with(['listing', 'user', 'reviewer']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($reports);
    }

    /**
     * Admin xử lý báo cáo (reviewed/dismissed).
     */
    public function review(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:reviewed,dismissed',
        ]);

        $report = ListingReport::findOrFail($id);
        $report->update([
            'status' => $request->status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Báo cáo đã được xử lý.',
            'report' => $report->fresh(),
        ]);
    }
}
