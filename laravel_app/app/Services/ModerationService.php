<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\BannedKeyword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ModerationService
{
    public function __construct(
        private ListingStateMachine $stateMachine,
        private NotificationService $notificationService
    ) {}

    /**
     * Tự động kiểm duyệt listing bằng cách quét banned keywords.
     * Nếu phát hiện vi phạm → auto-reject.
     * Nếu không → giữ pending_review cho Admin duyệt thủ công.
     */
    public function autoModerate(Listing $listing): void
    {
        $bannedKeywords = $this->getBannedKeywords();

        // Scan title and description
        $content = mb_strtolower($listing->title . ' ' . strip_tags($listing->description));
        $violations = [];

        foreach ($bannedKeywords as $keyword) {
            if (str_contains($content, mb_strtolower($keyword->keyword))) {
                $violations[] = $keyword->keyword;
            }
        }

        // Auto-reject if violations found
        if (!empty($violations)) {
            $reason = 'Phát hiện từ khóa vi phạm: ' . implode(', ', $violations);
            $this->stateMachine->transition($listing, 'rejected', $reason);
            return;
        }

        // Keep in pending_review for manual review
        if ($listing->status !== 'pending_review') {
            $listing->status = 'pending_review';
            $listing->save();
        }
    }

    /**
     * Admin duyệt listing → chuyển sang active, gửi email thông báo.
     */
    public function approve(Listing $listing): void
    {
        $this->stateMachine->transition($listing, 'active');

        try {
            $this->notificationService->sendApprovalEmail($listing);
        } catch (\Exception $e) {
            // Email failure should not rollback status change
            Log::error('Failed to send approval email', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Admin từ chối listing → chuyển sang rejected, gửi email kèm lý do.
     */
    public function reject(Listing $listing, string $reason): void
    {
        $this->stateMachine->transition($listing, 'rejected', $reason);

        try {
            $this->notificationService->sendRejectionEmail($listing, $reason);
        } catch (\Exception $e) {
            Log::error('Failed to send rejection email', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Lấy danh sách banned keywords từ DB, cache 1 giờ.
     */
    private function getBannedKeywords()
    {
        return Cache::remember('banned_keywords', 3600, function () {
            return BannedKeyword::where('is_active', true)->get();
        });
    }
}
