<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Listing;
use App\Models\ListingAuditLog;
use App\Models\Skill;
use App\Services\ModerationService;
use App\Services\QuotaService;
use App\Services\RateLimitService;
use App\Services\ListingStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function __construct(
        private ModerationService $moderationService,
        private QuotaService $quotaService,
        private RateLimitService $rateLimitService,
        private ListingStateMachine $stateMachine
    ) {}

    /**
     * Danh sách listings của NTD hiện tại (có pagination).
     */
    public function index(Request $request): JsonResponse
    {
        $listings = Listing::where('user_id', $request->user()->id)
            ->with(['category', 'skills'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($listings);
    }

    /**
     * Chi tiết 1 listing (chỉ chủ sở hữu hoặc admin).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $listing = Listing::with(['category', 'skills', 'user'])->findOrFail($id);

        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền truy cập.'], 403);
        }

        return response()->json($listing);
    }

    /**
     * Tạo listing mới.
     */
    public function store(StoreJobRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        // Handle file upload
        if ($request->hasFile('jd_file')) {
            $data['jd_file_path'] = $request->file('jd_file')
                ->store('jd_files', 'public');
        }

        // Determine initial status based on publish_mode
        $data['status'] = match ($data['publish_mode']) {
            'immediate' => 'pending_review',
            'scheduled' => 'scheduled',
            'draft' => 'draft',
            default => 'draft',
        };

        // Remove skills from data (will sync separately)
        $skills = $data['skills'] ?? [];
        unset($data['skills']);
        unset($data['jd_file']);

        $listing = Listing::create($data);

        // Sync skills
        if (!empty($skills)) {
            $listing->skills()->sync($skills);
            // Update usage_count for skills
            Skill::whereIn('id', $skills)->increment('usage_count');
        }

        // Log creation
        ListingAuditLog::create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => $listing->toArray(),
        ]);

        // Increment rate limit counter
        $this->rateLimitService->incrementAttempts($request->user());

        // Auto-moderation for immediate publish
        if ($data['publish_mode'] === 'immediate') {
            $this->moderationService->autoModerate($listing);
        }

        $listing->load(['category', 'skills']);

        return response()->json($listing, 201);
    }

    /**
     * Cập nhật listing.
     */
    public function update(UpdateJobRequest $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền chỉnh sửa.'], 403);
        }

        $data = $request->validated();
        $oldValues = $listing->toArray();

        // Handle file upload
        if ($request->hasFile('jd_file')) {
            // Delete old file
            if ($listing->jd_file_path) {
                Storage::disk('public')->delete($listing->jd_file_path);
            }
            $data['jd_file_path'] = $request->file('jd_file')
                ->store('jd_files', 'public');
        }
        unset($data['jd_file']);

        // Sync skills if provided
        if (isset($data['skills'])) {
            $listing->skills()->sync($data['skills']);
            unset($data['skills']);
        }

        $listing->update($data);

        // Log update
        ListingAuditLog::create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'action' => 'updated',
            'old_values' => array_intersect_key($oldValues, $data),
            'new_values' => $data,
        ]);

        // Re-moderation if title/description changed on active listing
        if ($listing->status === 'active' && $request->requiresReModeration()) {
            $this->stateMachine->transition($listing, 'pending_review');
            $this->moderationService->autoModerate($listing->fresh());
        }

        $listing->load(['category', 'skills']);

        return response()->json($listing);
    }

    /**
     * Soft delete listing (chuyển sang closed).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền xóa.'], 403);
        }

        $this->stateMachine->transition($listing, 'closed');

        return response()->json(['message' => 'Tin tuyển dụng đã được đóng.']);
    }

    /**
     * Tạm dừng listing (active → paused).
     */
    public function pause(Request $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền.'], 403);
        }

        $this->stateMachine->transition($listing, 'paused');

        return response()->json(['message' => 'Tin đã được tạm dừng.', 'listing' => $listing]);
    }

    /**
     * Tiếp tục listing (paused → active).
     */
    public function resume(Request $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền.'], 403);
        }

        $this->stateMachine->transition($listing, 'active');

        return response()->json(['message' => 'Tin đã hoạt động trở lại.', 'listing' => $listing]);
    }

    /**
     * Đóng listing.
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền.'], 403);
        }

        $this->stateMachine->transition($listing, 'closed');

        return response()->json(['message' => 'Tin đã được đóng.', 'listing' => $listing]);
    }

    /**
     * Gia hạn listing (expired/closed → pending_review).
     */
    public function renew(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'application_close_date' => 'required|date|after:today',
        ], [
            'application_close_date.required' => 'Ngày đóng nhận hồ sơ mới là bắt buộc.',
            'application_close_date.after' => 'Ngày đóng phải sau ngày hôm nay.',
        ]);

        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền.'], 403);
        }

        $listing->application_close_date = $request->application_close_date;
        $listing->save();

        $this->stateMachine->transition($listing, 'pending_review');
        $this->moderationService->autoModerate($listing->fresh());

        return response()->json(['message' => 'Tin đã được gửi duyệt lại.', 'listing' => $listing->fresh()]);
    }

    /**
     * Nhân bản listing (tạo bản sao với status draft).
     */
    public function clone(Request $request, int $id): JsonResponse
    {
        $original = Listing::with('skills')->findOrFail($id);

        if ($original->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền.'], 403);
        }

        $newListing = $original->replicate();
        $newListing->status = 'draft';
        $newListing->publish_mode = 'draft';
        $newListing->application_close_date = null;
        $newListing->scheduled_at = null;
        $newListing->rejection_reason = null;
        $newListing->rejected_at = null;
        $newListing->archived_reason = null;
        $newListing->save();

        // Copy skills
        $newListing->skills()->sync($original->skills->pluck('id'));

        // Log
        ListingAuditLog::create([
            'listing_id' => $newListing->id,
            'user_id' => $request->user()->id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => ['cloned_from' => $original->id],
        ]);

        // Increment rate limit
        $this->rateLimitService->incrementAttempts($request->user());

        $newListing->load(['category', 'skills']);

        return response()->json($newListing, 201);
    }
}
