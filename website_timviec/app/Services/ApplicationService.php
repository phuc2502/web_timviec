<?php

namespace App\Services;

use App\Events\ApplicationStatusUpdated;
use App\Models\Application;
use App\Models\Cv;
use App\Models\Listing;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApplicationService
{
    // ════════════════════════════════════════════════════════════════════════
    // PHÍA ỨNG VIÊN
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Ứng tuyển công việc (insert hoặc update).
     *
     * @param  User   $candidate
     * @param  array  $data  Validated từ ApplyJobRequest
     * @return Application
     *
     * @throws \RuntimeException  Khi job hết hạn hoặc hết lượt
     * @throws \Throwable         Lỗi DB Transaction
     */
    public function apply(User $candidate, array $data): Application
    {
        // ── 1. Kiểm tra công việc ─────────────────────────────────────────
        $listing = Listing::findOrFail($data['listing_id']);

        // Kiểm tra hết hạn
        if ($listing->application_close_date && now()->gt($listing->application_close_date)) {
            throw new \RuntimeException('Công việc đã hết hạn hoặc đã đóng tuyển dụng.');
        }

        // ── 2. Kiểm tra lượt ứng tuyển ────────────────────────────────────
        $tokenRecord = UserToken::where('user_id', $candidate->id)->lockForUpdate()->first();

        if (!$tokenRecord || !$tokenRecord->hasBalance()) {
            throw new \RuntimeException('Bạn đã hết lượt ứng tuyển. Vui lòng mua thêm.');
        }

        return DB::transaction(function () use ($candidate, $data, $listing, $tokenRecord) {
            // ── 3. Xử lý CV ───────────────────────────────────────────────
            $cvId = $this->resolveCv($candidate, $data);

            // ── 4. Kiểm tra đã ứng tuyển chưa ────────────────────────────
            $existing = Application::where('user_id', $candidate->id)
                                    ->where('listing_id', $listing->id)
                                    ->first();

            if ($existing) {
                // Ứng tuyển lại: cập nhật bản ghi cũ, KHÔNG trừ thêm token
                $existing->update([
                    'cv_id'        => $cvId,
                    'cover_letter' => $data['cover_letter'] ?? null,
                    'status'       => 'submitted',
                    'applied_at'   => now(),
                ]);
                return $existing->fresh();
            }

            // ── 5. Insert đơn mới ──────────────────────────────────────────
            $application = Application::create([
                'user_id'      => $candidate->id,
                'listing_id'   => $listing->id,
                'cv_id'        => $cvId,
                'cover_letter' => $data['cover_letter'] ?? null,
                'status'       => 'submitted',
                'applied_at'   => now(),
            ]);

            // ── 6. Trừ 1 lượt ứng tuyển ───────────────────────────────────
            $tokenRecord->decrement('balance');

            Log::info("Application created: user={$candidate->id}, listing={$listing->id}, balance_after={$tokenRecord->balance}");

            return $application;
        });
    }

    /**
     * Tự động gợi ý CV gần nhất của ứng viên.
     */
    public function suggestLatestCv(User $candidate): ?Cv
    {
        return Cv::where('user_id', $candidate->id)
                  ->latest()
                  ->first();
    }

    /**
     * Lịch sử ứng tuyển của ứng viên (phân trang).
     */
    public function candidateHistory(User $candidate, int $perPage = 10): LengthAwarePaginator
    {
        return Application::with(['listing.user', 'cv'])
            ->where('user_id', $candidate->id)
            ->orderByDesc('applied_at')
            ->paginate($perPage);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PHÍA NHÀ TUYỂN DỤNG
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Danh sách ứng viên theo listing_id (employer phải sở hữu listing đó).
     */
    public function listByJob(User $employer, int $listingId, int $perPage = 10): LengthAwarePaginator
    {
        $listing = Listing::where('id', $listingId)
                          ->where('user_id', $employer->id)
                          ->firstOrFail();

        return Application::with(['user', 'cv'])
            ->where('listing_id', $listing->id)
            ->orderByDesc('applied_at')
            ->paginate($perPage);
    }

    /**
     * Xem chi tiết CV → tự động cập nhật status từ "submitted" sang "viewed".
     */
    public function viewApplicationDetail(User $employer, int $applicationId): Application
    {
        $application = Application::with(['user', 'cv', 'listing'])
            ->whereHas('listing', fn($q) => $q->where('user_id', $employer->id))
            ->findOrFail($applicationId);

        if ($application->status === 'submitted') {
            $application->update([
                'status'            => 'viewed',
                'status_updated_at' => now(),
            ]);
        }

        return $application->fresh(['user', 'cv', 'listing']);
    }

    /**
     * Cập nhật trạng thái ứng tuyển (theo quy tắc một chiều).
     *
     * @throws \RuntimeException  Khi vi phạm quy tắc chuyển trạng thái
     */
    public function updateStatus(User $employer, int $applicationId, string $newStatus): Application
    {
        $application = Application::with(['user', 'listing'])
            ->whereHas('listing', fn($q) => $q->where('user_id', $employer->id))
            ->findOrFail($applicationId);

        if ($application->status === $newStatus) {
            throw new \RuntimeException('Trạng thái không thay đổi.');
        }

        if (!$application->canTransitionTo($newStatus)) {
            throw new \RuntimeException(
                "Không thể chuyển từ \"{$application->status}\" sang \"{$newStatus}\"."
            );
        }

        $oldStatus = $application->status;

        $application->update([
            'status'            => $newStatus,
            'status_updated_at' => now(),
        ]);

        // Dispatch event gửi email thông báo
        ApplicationStatusUpdated::dispatch($application->fresh(), $oldStatus);

        Log::info("Application #{$applicationId} status: {$oldStatus} → {$newStatus} by employer {$employer->id}");

        return $application->fresh();
    }

    // ════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Xác định CV ID: dùng cv_id có sẵn hoặc upload file CV mới.
     */
    private function resolveCv(User $candidate, array $data): int
    {
        if (!empty($data['cv_id'])) {
            $cv = Cv::where('id', $data['cv_id'])
                    ->where('user_id', $candidate->id)
                    ->firstOrFail();
            return $cv->id;
        }

        $file     = $data['cv_file'];
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs("cvs/{$candidate->id}", $filename, 'public');

        $cv = Cv::create([
            'user_id'       => $candidate->id,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return $cv->id;
    }
}
