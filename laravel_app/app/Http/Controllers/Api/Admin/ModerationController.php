<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectListingRequest;
use App\Models\Listing;
use App\Models\ListingAuditLog;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        private ModerationService $moderationService
    ) {}

    /**
     * Danh sách listings chờ duyệt.
     */
    public function pending(Request $request): JsonResponse
    {
        $listings = Listing::where('status', 'pending_review')
            ->with(['user', 'category'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($listings);
    }

    /**
     * Duyệt listing (pending_review → active).
     */
    public function approve(int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        if ($listing->status !== 'pending_review') {
            return response()->json([
                'message' => 'Chỉ có thể duyệt tin đang ở trạng thái chờ duyệt.',
            ], 422);
        }

        $this->moderationService->approve($listing);

        return response()->json([
            'message' => 'Tin tuyển dụng đã được duyệt.',
            'listing' => $listing->fresh(),
        ]);
    }

    /**
     * Từ chối listing (pending_review → rejected).
     */
    public function reject(RejectListingRequest $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        if ($listing->status !== 'pending_review') {
            return response()->json([
                'message' => 'Chỉ có thể từ chối tin đang ở trạng thái chờ duyệt.',
            ], 422);
        }

        $this->moderationService->reject($listing, $request->rejection_reason);

        return response()->json([
            'message' => 'Tin tuyển dụng đã bị từ chối.',
            'listing' => $listing->fresh(),
        ]);
    }

    /**
     * Xem lịch sử audit logs của listing.
     */
    public function auditLogs(int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        $logs = ListingAuditLog::where('listing_id', $listing->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * Xóa vĩnh viễn listing (archived/rejected >= 90 ngày).
     */
    public function hardDelete(Request $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        // Check status
        if (!in_array($listing->status, ['archived', 'rejected'])) {
            return response()->json([
                'message' => 'Chỉ có thể xóa vĩnh viễn tin ở trạng thái archived hoặc rejected.',
            ], 422);
        }

        // Check 90 days condition
        $statusDate = $listing->status === 'rejected' ? $listing->rejected_at : $listing->updated_at;
        if ($statusDate && $statusDate->diffInDays(now()) < 90) {
            return response()->json([
                'message' => 'Tin phải ở trạng thái hiện tại ít nhất 90 ngày trước khi có thể xóa vĩnh viễn.',
            ], 422);
        }

        // Log hard delete action BEFORE deleting
        ListingAuditLog::create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'action' => 'hard_deleted',
            'old_values' => $listing->toArray(),
            'new_values' => null,
        ]);

        // Delete related data (but NOT audit logs)
        $listing->skills()->detach();
        $listing->views()->delete();
        $listing->reports()->delete();

        // Force delete the listing
        $listing->forceDelete();

        return response()->json(['message' => 'Tin tuyển dụng đã được xóa vĩnh viễn.']);
    }
}
