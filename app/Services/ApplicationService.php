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
        $listing = Listing::with('user')->findOrFail($data['listing_id']);

        // Kiểm tra hết hạn
        if ($listing->application_close_date && now()->gt($listing->application_close_date)) {
            throw new \RuntimeException('Công việc đã hết hạn hoặc đã đóng tuyển dụng.');
        }

        // ── 1b. Lấy đơn ứng tuyển gần nhất của ứng viên cho job này ─────
        $latestApplication = Application::where('user_id', $candidate->id)
                                         ->where('listing_id', $listing->id)
                                         ->orderByDesc('id')
                                         ->first();

        $isReapply    = $latestApplication !== null;
        // apply_round = tổng số lần đã bấm nộp (được tăng mỗi lần submit)
        $totalApplied = $isReapply ? ($latestApplication->apply_round ?? 1) : 0;

        // ── Quy tắc nghiệp vụ ────────────────────────────────────────────
        // 1. Nếu status ≠ submitted (đã xem/duyệt/phỏng vấn/từ chối)
        //    → NTD đã xử lý hồ sơ, không cho ứng tuyển lại
        if ($isReapply && $latestApplication->status !== 'submitted') {
            throw new \RuntimeException(
                '__STATUS_LOCKED__: Hồ sơ của bạn đang ở trạng thái "' .
                (\App\Models\Application::STATUS_LABELS[$latestApplication->status] ?? $latestApplication->status) .
                '" — không thể ứng tuyển lại.'
            );
        }

        // 2. Nếu đã nộp đủ 3 lần → disable
        if ($totalApplied >= Application::MAX_APPLY_ROUNDS) {
            throw new \RuntimeException(
                '__MAX_REAPPLY__: Bạn đã ứng tuyển tối đa ' . Application::MAX_APPLY_ROUNDS . ' lần cho vị trí này.'
            );
        }

        // ── 1c. Kiểm tra giới hạn ứng viên cho tài khoản Free ─────────────
        if (!$isReapply && !$listing->user->isPremium()) {
            $uniqueApplicantCount = Application::where('listing_id', $listing->id)
                                               ->whereNull('parent_application_id')
                                               ->count();
            if ($uniqueApplicantCount >= 3) {
                throw new \RuntimeException('__FREE_LIMIT__:' . $listing->id);
            }
        }

        // ── 2. Kiểm tra lượt ứng tuyển ────────────────────────────────────
        $tokenRecord = UserToken::where('user_id', $candidate->id)->lockForUpdate()->first();

        if (!$tokenRecord || !$tokenRecord->hasBalance()) {
            throw new \RuntimeException('Bạn đã hết lượt ứng tuyển. Vui lòng mua thêm.');
        }

        return DB::transaction(function () use (
            $candidate, $data, $listing, $tokenRecord,
            $latestApplication, $totalApplied
        ) {
            // ── 3. Xử lý CV ───────────────────────────────────────────────
            $cvId = $this->resolveCv($candidate, $data);

            // Snapshot thông tin liên hệ tại thời điểm nộp đơn
            $contactSnapshot = [
                'applicant_name'  => $data['fullname']  ?? $candidate->name,
                'applicant_phone' => $data['phone']      ?? $candidate->phone,
                'applicant_email' => $data['email']      ?? $candidate->email,
            ];

            // ── 4. Lưu / cập nhật đơn ứng tuyển ──────────────────────────
            // Lúc này chỉ có 2 TH:
            //   a) Chưa ứng tuyển lần nào       → INSERT (apply_round = 1)
            //   b) Đã ứng tuyển, status=submitted → UPDATE (apply_round tăng +1)
            //   (TH status ≠ submitted đã bị chặn ở bước 1b)
            $newRound = $totalApplied + 1;

            if ($latestApplication) {
                // ─── UPDATE bản ghi hiện tại (status đang là submitted) ──
                $latestApplication->update(array_merge([
                    'cv_id'        => $cvId,
                    'cover_letter' => $data['cover_letter'] ?? null,
                    'status'       => 'submitted',
                    'apply_round'  => $newRound,
                    'applied_at'   => now('Asia/Ho_Chi_Minh'),
                ], $contactSnapshot));
                $application = $latestApplication->fresh();

                Log::info("Application UPDATED: user={$candidate->id}, listing={$listing->id}, round={$newRound}");
            } else {
                // ─── INSERT bản ghi mới (lần đầu ứng tuyển) ─────────────
                $application = Application::create(array_merge([
                    'user_id'      => $candidate->id,
                    'listing_id'   => $listing->id,
                    'cv_id'        => $cvId,
                    'cover_letter' => $data['cover_letter'] ?? null,
                    'status'       => 'submitted',
                    'apply_round'  => $newRound, // = 1
                    'applied_at'   => now('Asia/Ho_Chi_Minh'),
                ], $contactSnapshot));

                Log::info("Application CREATED: user={$candidate->id}, listing={$listing->id}, round=1");
            }

            // ── 5. Trừ 1 lượt ─────────────────────────────────────────────
            $tokenRecord->decrement('balance');

            return $application;
        });
    }
    /**
     * Lấy CV từ lần ứng tuyển gần nhất trong toàn hệ thống (Global Last-Used CV).
     * Đây là Single Source of Truth — cùng bản ghi mà NTD nhìn thấy.
     */
    public function suggestLatestCv(User $candidate): ?Cv
    {
        // Lấy CV từ lần ứng tuyển gần nhất (bất kể job nào, bất kể round nào)
        $latestApplication = Application::with('cv')
            ->where('user_id', $candidate->id)
            ->whereNotNull('cv_id')
            ->orderByDesc('applied_at')
            ->first();

        if ($latestApplication && $latestApplication->cv) {
            return $latestApplication->cv;
        }

        // Fallback: CV upload gần nhất (chưa ứng tuyển lần nào)
        return Cv::where('user_id', $candidate->id)->latest()->first();
    }

    /**
     * Đếm số lần ứng viên đã ứng tuyển 1 job cụ thể.
     * Dùng để kiểm tra giới hạn 3 lần ở phía view.
     */
    public function applyCountForJob(User $candidate, int $listingId): int
    {
        return Application::where('user_id', $candidate->id)
                          ->where('listing_id', $listingId)
                          ->count();
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

        // Hiển thị TẤT CẢ các bản ghi (bao gồm cả lần ứng tuyển lại tạo bản ghi mới).
        // Mỗi bản ghi là 1 dòng riêng trong danh sách NTD.
        // apply_round giúp NTD biết đây là lần ứng tuyển thứ mấy.
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
            $oldStatus = $application->status;
            $application->update([
                'status'            => 'viewed',
                'status_updated_at' => now('Asia/Ho_Chi_Minh'),
            ]);
            $fresh = $application->fresh(['user', 'cv', 'listing']);
            // Gửi in-app notification cho ứng viên khi NTD xem hồ sơ
            $this->createStatusNotification($fresh, $oldStatus);
            return $fresh;
        }

        return $application->fresh(['user', 'cv', 'listing']);
    }

    /**
     * Cập nhật trạng thái ứng tuyển (theo quy tắc state machine).
     *
     * @param  array  $extra  Dữ liệu thêm: interview_scheduled_at (khi status=interviewing)
     * @throws \RuntimeException  Khi vi phạm quy tắc chuyển trạng thái
     */
    public function updateStatus(User $employer, int $applicationId, string $newStatus, array $extra = []): Application
    {
        $application = Application::with(['user', 'listing.user'])
            ->whereHas('listing', fn($q) => $q->where('user_id', $employer->id))
            ->findOrFail($applicationId);

        if ($application->status === $newStatus) {
            throw new \RuntimeException('Trạng thái không thay đổi.');
        }

        if ($application->isClosed()) {
            throw new \RuntimeException('Hồ sơ đã ở trạng thái đóng, không thể cập nhật.');
        }

        if (!$application->canTransitionTo($newStatus)) {
            throw new \RuntimeException(
                "Không thể chuyển từ \"{$application->status}\" sang \"{$newStatus}\"."
            );
        }

        $oldStatus = $application->status;

        $updateData = [
            'status'            => $newStatus,
            'status_updated_at' => now('Asia/Ho_Chi_Minh'),
        ];

        // Lưu ngày giờ phỏng vấn nếu status = interviewing
        if ($newStatus === 'interviewing' && !empty($extra['interview_scheduled_at'])) {
            $updateData['interview_scheduled_at'] = $extra['interview_scheduled_at'];
        }

        $application->update($updateData);

        $fresh = $application->fresh(['user', 'listing.user']);

        // 1. Gửi in-app notification cho ứng viên
        $this->createStatusNotification($fresh, $oldStatus);

        // 2. Dispatch event (gửi email nếu status = interviewing)
        ApplicationStatusUpdated::dispatch($fresh, $oldStatus);

        Log::info("Application #{$applicationId} status: {$oldStatus} → {$newStatus} by employer {$employer->id}");

        return $fresh;
    }

    /**
     * Tạo in-app notification cho ứng viên khi trạng thái thay đổi.
     */
    private function createStatusNotification(Application $application, string $oldStatus): void
    {
        $statusLabel = \App\Models\Application::STATUS_LABELS[$application->status] ?? $application->status;
        $jobTitle    = $application->listing->title;
        $company     = $application->listing->user->company_name ?? $application->listing->user->name;

        $titles = [
            'viewed'       => "Hồ sơ của bạn đã được xem",
            'approved'     => "Hồ sơ của bạn được duyệt! 🎉",
            'interviewing' => "Bạn được mời phỏng vấn! 🎯",
            'rejected'     => "Thông báo kết quả hồ sơ",
        ];

        $bodies = [
            'viewed'       => "{$company} đã xem hồ sơ ứng tuyển vị trí **{$jobTitle}** của bạn.",
            'approved'     => "Chúc mừng! {$company} đã duyệt hồ sơ ứng tuyển vị trí **{$jobTitle}** của bạn.",
            'interviewing' => "Chúc mừng! {$company} mời bạn tham gia phỏng vấn cho vị trí **{$jobTitle}**." .
                              ($application->interview_scheduled_at ? " Thời gian dự kiến: " . $application->interview_scheduled_at->format('d/m/Y H:i') : ""),
            'rejected'     => "{$company} thông báo hồ sơ ứng tuyển vị trí **{$jobTitle}** của bạn chưa phù hợp lần này.",
        ];

        \App\Models\AppNotification::create([
            'user_id' => $application->user_id,
            'type'    => 'application_status',
            'title'   => $titles[$application->status] ?? "Cập nhật trạng thái hồ sơ",
            'body'    => $bodies[$application->status] ?? "Trạng thái hồ sơ của bạn đã được cập nhật thành {$statusLabel}.",
            'data'    => [
                'application_id' => $application->id,
                'listing_id'     => $application->listing_id,
                'status'         => $application->status,
                'old_status'     => $oldStatus,
            ],
        ]);
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
