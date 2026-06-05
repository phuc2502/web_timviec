# Kế Hoạch Triển Khai: Job Posting & Moderation

## Tổng quan

Module Job Posting & Moderation nâng cấp hệ thống đăng tin tuyển dụng của ITWorks từ form đơn giản thành hệ thống quản lý toàn diện. Triển khai theo kiến trúc Laravel MVC với các layer:
- **Database Layer**: 9 migrations (listings, pivot tables, analytics, audit logs)
- **Business Logic Layer**: 5 services (Moderation, Quota, RateLimit, Analytics, Notification)
- **API Layer**: 22 routes với middleware validation
- **Frontend Layer**: 3 Vue components (JobPostingForm, AnalyticsDashboard, SkillsAutocomplete)
- **Infrastructure Layer**: 4 scheduled tasks (publish, expire, archive, reminders)

## Tasks

- [ ] 1. Thiết lập cấu trúc Database Layer
  - [ ] 1.1 Tạo migration cho bảng `listings`
    - Tạo file `database/migrations/2024_01_XX_create_listings_table.php`
    - Định nghĩa schema với đầy đủ 30+ columns theo design: `title`, `description`, `address`, `job_type`, `level`, salary fields, publishing mode, status, moderation fields
    - Thêm indexes: `user_id + status`, `status + application_close_date`, `scheduled_at`, FULLTEXT index cho `title + description`
    - Thêm softDeletes timestamp
    - _Requirements: 1.1, 1.2, 2.1, 2.3, 2.4, 4.1_

  - [ ] 1.2 Tạo migration cho bảng pivot `listing_skill`
    - Tạo file `database/migrations/2024_01_XX_create_listing_skill_table.php`
    - Định nghĩa foreign keys: `listing_id`, `skill_id` với cascade delete
    - Thêm unique constraint cho cặp `(listing_id, skill_id)`
    - Thêm index cho `skill_id` để tối ưu reverse lookup
    - _Requirements: 1.5_

  - [ ] 1.3 Tạo migration cập nhật bảng `skills`
    - Tạo file `database/migrations/2024_01_XX_update_skills_table.php`
    - Thêm column `slug` (unique) và `usage_count` (default 0)
    - Thêm index cho `name` để hỗ trợ autocomplete search
    - Sử dụng `Schema::hasColumn()` check trước khi thêm column
    - _Requirements: 1.6_

  - [ ] 1.4 Tạo migration cho bảng `listing_views` (Analytics)
    - Tạo file `database/migrations/2024_01_XX_create_listing_views_table.php`
    - Định nghĩa schema với: `listing_id`, `user_id` (nullable), `ip_address`, `traffic_source`, `action_type` enum('view', 'apply_click')
    - Thêm indexes: `listing_id + created_at`, `listing_id + action_type + created_at`, `user_id + created_at`
    - Không sử dụng `updated_at`, chỉ có `created_at`
    - _Requirements: 8.1, 8.2_

  - [ ] 1.5 Tạo migration cho bảng `listing_reports`
    - Tạo file `database/migrations/2024_01_XX_create_listing_reports_table.php`
    - Định nghĩa schema với: `listing_id`, `user_id`, `reason` enum, `description`, `status` enum, `reviewed_by`, `reviewed_at`
    - Thêm unique constraint: `(listing_id, user_id)` để ngăn báo cáo trùng
    - Thêm index: `listing_id + status`
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [ ] 1.6 Tạo migration cho bảng `listing_audit_logs`
    - Tạo file `database/migrations/2024_01_XX_create_listing_audit_logs_table.php`
    - Định nghĩa schema với: `listing_id`, `user_id`, `action`, `old_values` JSON, `new_values` JSON, `note`
    - Thêm indexes: `listing_id + created_at`, `user_id + created_at`
    - Không có `updated_at`, chỉ `created_at` vì audit logs immutable
    - _Requirements: 5.4, 5.5, 15.7_

  - [ ] 1.7 Tạo migration cho bảng `banned_keywords`
    - Tạo file `database/migrations/2024_01_XX_create_banned_keywords_table.php`
    - Định nghĩa schema với: `keyword`, `is_active`, `severity`
    - Thêm index: `keyword + is_active`
    - _Requirements: 3.1, 3.2, 3.7, 3.8_

  - [ ] 1.8 Tạo migration cập nhật bảng `users`
    - Tạo file `database/migrations/2024_01_XX_update_users_table_for_listings.php`
    - Thêm columns: `email_notify` (boolean, default true), `is_banned` (boolean, default false), `banned_at` (timestamp nullable)
    - Sử dụng `Schema::hasColumn()` check trước khi thêm
    - _Requirements: 6.7, 9.5_

  - [ ] 1.9 Tạo migration cập nhật bảng `categories`
    - Tạo file `database/migrations/2024_01_XX_update_categories_table.php`
    - Thêm columns: `slug` (unique) và `is_active` (default true)
    - Thêm index: `is_active + name`
    - _Requirements: 11.1, 11.3_

- [ ] 2. Triển khai Models và Relationships
  - [ ] 2.1 Tạo Listing Model với relationships
    - Tạo file `app/Models/Listing.php` extends Eloquent Model
    - Định nghĩa relationships: `belongsTo(User)`, `belongsTo(Category)`, `belongsToMany(Skill)`, `hasMany(ListingView)`, `hasMany(ListingReport)`, `hasMany(ListingAuditLog)`
    - Thêm `fillable` array với tất cả columns có thể mass-assign
    - Thêm `casts` cho: `salary_min`, `salary_max` (decimal), `scheduled_at`, `rejected_at`, `banned_at` (datetime), `is_negotiable`, `hide_salary` (boolean)
    - Kích hoạt `SoftDeletes` trait
    - _Requirements: 1.1, 1.5_

  - [ ] 2.2 Tạo ListingView Model
    - Tạo file `app/Models/ListingView.php`
    - Định nghĩa relationship: `belongsTo(Listing)`, `belongsTo(User)` với nullable
    - Vô hiệu hóa `updated_at` bằng cách set `const UPDATED_AT = null`
    - Thêm `fillable`: `listing_id`, `user_id`, `ip_address`, `traffic_source`, `action_type`
    - _Requirements: 8.1, 8.2_

  - [ ] 2.3 Tạo ListingReport Model
    - Tạo file `app/Models/ListingReport.php`
    - Định nghĩa relationships: `belongsTo(Listing)`, `belongsTo(User, 'user_id')`, `belongsTo(User, 'reviewed_by')`
    - Thêm `fillable` và `casts` cho `reviewed_at` (datetime)
    - _Requirements: 7.3, 7.6_

  - [ ] 2.4 Tạo ListingAuditLog Model
    - Tạo file `app/Models/ListingAuditLog.php`
    - Định nghĩa relationships: `belongsTo(Listing)`, `belongsTo(User)`
    - Vô hiệu hóa `updated_at` vì audit logs immutable
    - Thêm `casts` cho `old_values`, `new_values` (array/JSON)
    - _Requirements: 5.4, 13.5, 15.5_

  - [ ] 2.5 Tạo BannedKeyword Model
    - Tạo file `app/Models/BannedKeyword.php`
    - Thêm `fillable`: `keyword`, `is_active`, `severity`
    - Thêm scope `active()` để filter `is_active = true`
    - _Requirements: 3.1, 3.7_

  - [ ] 2.6 Cập nhật User Model với listing relationships
    - Mở file `app/Models/User.php`
    - Thêm relationship: `hasMany(Listing)`
    - Thêm accessor `isPaidOrTrial()` để check subscription status
    - _Requirements: 6.1, 6.2_

- [ ] 3. Triển khai State Machine và Events
  - [ ] 3.1 Tạo ListingStateMachine service
    - Tạo file `app/Services/ListingStateMachine.php`
    - Định nghĩa constant `TRANSITIONS` array với 9 states và allowed transitions theo design
    - Implement method `canTransition(Listing $listing, string $toStatus): bool`
    - Implement method `transition(Listing $listing, string $toStatus, ?string $reason = null): bool`
    - Method `transition` phải: update status, set rejection fields nếu cần, save, log audit, dispatch event
    - Throw `InvalidStateTransitionException` nếu transition không hợp lệ
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.9_

  - [ ] 3.2 Tạo ListingStatusChanged event
    - Tạo file `app/Events/ListingStatusChanged.php` implements `ShouldBroadcast`
    - Constructor nhận: `Listing $listing`, `string $oldStatus`, `string $newStatus`
    - Implement `broadcastOn()` trả về `PrivateChannel('user.' . $this->listing->user_id)`
    - _Requirements: 4.1, 9.1, 9.2_

  - [ ] 3.3 Tạo SendListingStatusNotification listener
    - Tạo file `app/Listeners/SendListingStatusNotification.php`
    - Method `handle(ListingStatusChanged $event)` check `user->email_notify`
    - Sử dụng match expression để queue email: `active` → ApprovedMail, `rejected` → RejectedMail, `expired` → ExpiredMail
    - _Requirements: 9.1, 9.2, 9.5_

  - [ ] 3.4 Đăng ký Event-Listener trong EventServiceProvider
    - Mở file `app/Providers/EventServiceProvider.php`
    - Thêm mapping: `ListingStatusChanged::class => [SendListingStatusNotification::class]`
    - _Requirements: 9.1_

- [ ] 4. Triển khai Business Logic Services
  - [ ] 4.1 Tạo ModerationService
    - Tạo file `app/Services/ModerationService.php`
    - Inject dependencies: `ListingStateMachine`, `NotificationService`
    - Implement `autoModerate(Listing $listing): void` - quét title + description với banned keywords, auto-reject nếu vi phạm
    - Implement `approve(Listing $listing): void` - transition sang active, gửi email (với try-catch log lỗi)
    - Implement `reject(Listing $listing, string $reason): void` - transition sang rejected, gửi email
    - Implement private `getBannedKeywords()` với cache 1 hour
    - _Requirements: 3.1, 3.2, 3.3, 3.5, 3.6_

  - [ ] 4.2 Tạo QuotaService
    - Tạo file `app/Services/QuotaService.php`
    - Định nghĩa constant `QUOTA_LIMITS`: monthly=5, yearly=15, trial=5
    - Implement `canCreateListing(User $user): bool` - check subscription + count active listings
    - Implement `getActiveListingCount(User $user): int` - đếm listings có status in ['active', 'pending_review', 'scheduled']
    - Implement `getQuotaLimit(User $user): int`
    - Implement private `hasActiveSubscription(User $user): bool` - check paid status hoặc trial period
    - Admin luôn return true
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [ ] 4.3 Tạo RateLimitService
    - Tạo file `app/Services/RateLimitService.php`
    - Định nghĩa constant `RATE_LIMITS`: monthly=5, yearly=5, trial=2 (listings per 24h)
    - Implement `canCreateListing(User $user): bool` - check cache key rate limit
    - Implement `incrementAttempts(User $user): void` - increment cache counter với TTL 24h
    - Implement `getRemainingAttempts(User $user): int`
    - Implement `getResetTime(User $user): string` - return ISO8601 timestamp
    - Private method `getCacheKey(User $user)`: return "rate_limit:listing:create:{$user->id}"
    - Admin luôn return true cho `canCreateListing`
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6, 14.7, 14.8_

  - [ ] 4.4 Tạo AnalyticsService
    - Tạo file `app/Services/AnalyticsService.php`
    - Implement `trackView(Listing $listing, ?User $user, Request $request): void` - tạo ListingView record với action_type='view'
    - Implement `trackApplyClick(Listing $listing, ?User $user, Request $request): void` - tạo ListingView record với action_type='apply_click'
    - Implement `getListingAnalytics(Listing $listing, int $days = 7): array` - aggregate views, apply clicks, conversion rate, views by day, top traffic sources
    - Implement `getSystemOverview(): array` - return new_listings_this_week, total_views, pending_review_count, avg_conversion_rate
    - Implement private `calculateAvgConversionRate(): float`
    - _Requirements: 8.1, 8.2, 8.3, 8.5, 8.7_

  - [ ] 4.5 Tạo NotificationService
    - Tạo file `app/Services/NotificationService.php`
    - Implement `sendApprovalEmail(Listing $listing): void` - queue ListingApprovedMail
    - Implement `sendRejectionEmail(Listing $listing, string $reason): void` - queue ListingRejectedMail với reason
    - Implement `sendExpiryReminderEmail(Listing $listing): void` - queue ListingExpiryReminderMail
    - Implement `sendNewApplicationEmail(Listing $listing, User $candidate): void` - queue NewApplicationMail
    - Tất cả methods check `user->email_notify` trước khi gửi
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [ ] 5. Checkpoint - Kiểm tra Database và Services
  - Chạy `php artisan migrate` để apply tất cả migrations
  - Kiểm tra các bảng đã được tạo đúng schema và indexes
  - Chạy tinker test các Model relationships và Service methods cơ bản
  - Đảm bảo không có lỗi syntax hay dependency injection
  - Hỏi người dùng nếu có vấn đề phát sinh

- [ ] 6. Triển khai Form Requests và Validation
  - [ ] 6.1 Tạo StoreJobRequest
    - Tạo file `app/Http/Requests/StoreJobRequest.php`
    - Định nghĩa validation rules cho tất cả fields: title (required, max 255), description (required), category_id (exists), job_type (enum), salary validation logic
    - Custom validation: `salary_max >= salary_min`, `application_close_date >= today`, `scheduled_at >= now + 5 minutes` nếu publish_mode=scheduled
    - Custom validation: nếu không có salary và không có `is_negotiable` → error
    - Implement `authorize()` return true (authorization qua middleware)
    - Thêm custom error messages bằng tiếng Việt
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.9, 2.4_

  - [ ] 6.2 Tạo UpdateJobRequest
    - Tạo file `app/Http/Requests/UpdateJobRequest.php`
    - Kế thừa validation rules từ StoreJobRequest nhưng tất cả fields đều optional (except id)
    - Thêm logic check: nếu update `title` hoặc `description` trên listing active → sẽ trigger re-moderation
    - _Requirements: 1.1, 5.1, 5.2_

  - [ ] 6.3 Tạo RejectListingRequest
    - Tạo file `app/Http/Requests/RejectListingRequest.php`
    - Validation rule: `rejection_reason` required, string, min 10 characters
    - _Requirements: 3.6_

- [ ] 7. Triển khai Middleware
  - [ ] 7.1 Tạo CheckQuota middleware
    - Tạo file `app/Http/Middleware/CheckQuota.php`
    - Inject QuotaService trong constructor
    - Method `handle()` check `$user->is_admin` → pass, else call `QuotaService->canCreateListing()`
    - Nếu vượt quota: return JSON response 403 với message, current_active, max_allowed
    - _Requirements: 6.5, 6.6_

  - [ ] 7.2 Tạo CheckRateLimit middleware
    - Tạo file `app/Http/Middleware/CheckRateLimit.php`
    - Inject RateLimitService trong constructor
    - Method `handle()` check `$user->is_admin` → pass, else call `RateLimitService->canCreateListing()`
    - Nếu vượt rate limit: return JSON response 429 với message, remaining, reset_at
    - _Requirements: 14.3, 14.6_

  - [ ] 7.3 Đăng ký middleware trong Kernel
    - Mở file `app/Http/Kernel.php`
    - Thêm alias: `'quota' => CheckQuota::class`, `'ratelimit' => CheckRateLimit::class`
    - _Requirements: 6.5, 14.3_

- [ ] 8. Triển khai API Controllers - Employer Endpoints
  - [ ] 8.1 Tạo ListingController với CRUD methods
    - Tạo file `app/Http/Controllers/Api/ListingController.php`
    - Inject dependencies: ModerationService, QuotaService, RateLimitService, ListingStateMachine
    - Implement `index()` - list listings của current user với pagination
    - Implement `show($id)` - chi tiết listing với authorization check
    - Implement `store(StoreJobRequest $request)` - tạo listing, handle file upload, sync skills, apply auto-moderation, increment rate limit
    - Implement `update($id, UpdateJobRequest $request)` - update listing, check nếu title/description thay đổi → trigger re-moderation
    - Implement `destroy($id)` - soft delete (chuyển sang closed)
    - _Requirements: 1.1, 1.5, 1.7, 2.1, 2.2, 2.3, 4.4, 5.1, 5.2_

  - [ ] 8.2 Thêm lifecycle action methods vào ListingController
    - Implement `pause($id)` - chuyển listing sang paused
    - Implement `resume($id)` - chuyển listing từ paused sang active
    - Implement `close($id)` - chuyển listing sang closed
    - Implement `renew($id, Request $request)` - validate new application_close_date, chuyển về pending_review, check quota
    - Implement `clone($id)` - nhân bản listing với status draft, check quota và rate limit
    - Tất cả methods phải check ownership (listing->user_id === auth user) hoặc is_admin
    - _Requirements: 4.2, 4.3, 4.4, 4.6, 4.7_

- [ ] 9. Triển khai API Controllers - Admin Endpoints
  - [ ] 9.1 Tạo ModerationController
    - Tạo file `app/Http/Controllers/Api/Admin/ModerationController.php`
    - Inject ModerationService
    - Implement `pending()` - list listings có status='pending_review' với pagination, sort by created_at desc
    - Implement `approve($id)` - call ModerationService->approve()
    - Implement `reject($id, RejectListingRequest $request)` - call ModerationService->reject() với reason
    - Implement `auditLogs($id)` - return ListingAuditLog records cho listing
    - Implement `hardDelete($id)` - check listing phải archived/rejected >= 90 days, xóa vĩnh viễn nhưng giữ audit logs
    - _Requirements: 3.3, 3.4, 3.5, 3.6, 5.5, 13.1, 13.2, 13.3, 13.4, 13.5, 13.8_

  - [ ] 9.2 Tạo ReportController
    - Tạo file `app/Http/Controllers/Api/ReportController.php`
    - Inject ModerationService
    - Implement `store(Request $request)` - Candidate tạo report mới, validate unique constraint, check >= 5 reports → auto-pause listing
    - Implement `index()` - Admin xem danh sách reports với filter theo status
    - Implement `review($id, Request $request)` - Admin đánh dấu report là reviewed/dismissed
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

  - [ ] 9.3 Tạo AnalyticsController
    - Tạo file `app/Http/Controllers/Api/AnalyticsController.php`
    - Inject AnalyticsService
    - Implement `show($listingId)` - return analytics cho 1 listing, check authorization (owner or admin)
    - Implement `overview()` - Admin only, return system-wide analytics
    - _Requirements: 8.3, 8.4, 8.6, 8.7_

- [ ] 10. Triển khai Public API Endpoints
  - [ ] 10.1 Tạo PublicListingController
    - Tạo file `app/Http/Controllers/Api/PublicListingController.php`
    - Inject AnalyticsService
    - Implement `index(Request $request)` - tìm kiếm listings active với filters: keyword (FULLTEXT), category_id, job_type, address, salary range, skills. Pagination 20/page, sort by created_at desc
    - Implement `show($id)` - chi tiết listing active, track view bằng AnalyticsService->trackView()
    - Implement `applyClick($id)` - track apply click bằng AnalyticsService->trackApplyClick(), return success
    - _Requirements: 8.1, 12.1, 12.2, 12.3, 12.4, 12.5_

- [ ] 11. Định nghĩa API Routes
  - [ ] 11.1 Tạo employer routes
    - Mở file `routes/api.php`
    - Định nghĩa route group `/api/employer/listings` với middleware: auth, employer
    - Thêm 10 routes theo design: GET /, GET /{id}, POST /, PUT /{id}, DELETE /{id}, POST /{id}/pause, POST /{id}/resume, POST /{id}/close, POST /{id}/renew, POST /{id}/clone
    - Routes store và clone có thêm middleware: quota, ratelimit
    - Routes update, destroy, pause, resume, close, renew, clone có thêm check ownership trong controller
    - _Requirements: 1.1, 4.2, 4.3, 4.4, 4.6, 4.7_

  - [ ] 11.2 Tạo admin routes
    - Trong `routes/api.php`, định nghĩa route group `/api/admin/listings` với middleware: auth, admin
    - Thêm 7 routes: GET /pending, POST /{id}/approve, POST /{id}/reject, GET /{id}/audit-logs, DELETE /{id}/hard-delete, GET /reports, POST /reports/{id}/review
    - _Requirements: 3.4, 3.5, 3.6, 5.5, 7.6, 13.1_

  - [ ] 11.3 Tạo public và analytics routes
    - Trong `routes/api.php`, định nghĩa public routes: GET /api/listings, GET /api/listings/{id}, POST /api/listings/{id}/report
    - Định nghĩa analytics routes với middleware auth: GET /api/listings/{id}/analytics (owner-or-admin), GET /api/analytics/overview (admin)
    - _Requirements: 8.3, 8.7, 12.1_

- [ ] 12. Checkpoint - Kiểm tra API Endpoints
  - Test tất cả API endpoints bằng Postman hoặc Thunder Client
  - Kiểm tra validation, authorization, quota, rate limiting đều hoạt động đúng
  - Kiểm tra state transitions và audit logging
  - Đảm bảo API responses follow chuẩn JSON structure
  - Hỏi người dùng nếu có vấn đề phát sinh

- [ ] 13. Triển khai Scheduled Tasks
  - [ ] 13.1 Tạo PublishScheduledListings command
    - Tạo file `app/Console/Commands/PublishScheduledListings.php`
    - Signature: `listings:publish-scheduled`
    - Method `handle()`: query listings có status='scheduled' và scheduled_at <= now(), chuyển sang pending_review
    - Log kết quả xử lý
    - _Requirements: 2.5, 2.7, 10.1_

  - [ ] 13.2 Tạo ExpireListings command
    - Tạo file `app/Console/Commands/ExpireListings.php`
    - Signature: `listings:expire`
    - Method `handle()`: query listings có status='active' và application_close_date < today, chuyển sang expired
    - Gửi email thông báo hết hạn cho NTD
    - _Requirements: 4.5, 10.2_

  - [ ] 13.3 Tạo ArchiveRejectedListings command
    - Tạo file `app/Console/Commands/ArchiveRejectedListings.php`
    - Signature: `listings:archive-rejected`
    - Method `handle()`: query listings có status='rejected' và rejected_at < 30 days ago, chuyển sang archived
    - _Requirements: 4.8, 10.3_

  - [ ] 13.4 Tạo SendExpiryReminders command
    - Tạo file `app/Console/Commands/SendExpiryReminders.php`
    - Signature: `listings:send-expiry-reminders`
    - Method `handle()`: query listings active có application_close_date = 3 days from now, gửi email reminder
    - _Requirements: 9.3_

  - [ ] 13.5 Đăng ký scheduled tasks trong Kernel
    - Mở file `app/Console/Kernel.php`
    - Trong method `schedule()`, thêm:
      - `$schedule->command('listings:publish-scheduled')->everyMinute()`
      - `$schedule->command('listings:expire')->dailyAt('00:00')`
      - `$schedule->command('listings:archive-rejected')->dailyAt('01:00')`
      - `$schedule->command('listings:send-expiry-reminders')->dailyAt('09:00')`
    - _Requirements: 10.1, 10.2, 10.3, 10.5_

- [ ] 14. Triển khai Email Notifications
  - [ ] 14.1 Tạo ListingApprovedMail
    - Tạo file `app/Mail/ListingApprovedMail.php` extends Mailable
    - Constructor nhận Listing $listing
    - Method `build()`: return view 'emails.listing-approved' với data listing
    - Subject: "Tin tuyển dụng của bạn đã được duyệt"
    - _Requirements: 9.1_

  - [ ] 14.2 Tạo ListingRejectedMail
    - Tạo file `app/Mail/ListingRejectedMail.php`
    - Constructor nhận Listing $listing và string $reason
    - Method `build()`: return view 'emails.listing-rejected' với data listing, reason
    - Subject: "Tin tuyển dụng của bạn cần chỉnh sửa"
    - _Requirements: 9.2_

  - [ ] 14.3 Tạo ListingExpiryReminderMail
    - Tạo file `app/Mail/ListingExpiryReminderMail.php`
    - Constructor nhận Listing $listing
    - Method `build()`: return view 'emails.listing-expiry-reminder'
    - Subject: "Tin tuyển dụng sắp hết hạn nhận hồ sơ"
    - _Requirements: 9.3_

  - [ ] 14.4 Tạo NewApplicationMail
    - Tạo file `app/Mail/NewApplicationMail.php`
    - Constructor nhận Listing $listing và User $candidate
    - Method `build()`: return view 'emails.new-application' với data listing, candidate
    - Subject: "Có ứng viên mới nộp đơn vào tin tuyển dụng của bạn"
    - _Requirements: 9.4_

  - [ ] 14.5 Tạo email blade views
    - Tạo 4 blade templates: `resources/views/emails/listing-approved.blade.php`, `listing-rejected.blade.php`, `listing-expiry-reminder.blade.php`, `new-application.blade.php`
    - Mỗi template hiển thị thông tin listing và link action button
    - _Requirements: 9.1, 9.2, 9.3, 9.4_

- [ ] 15. Triển khai User Event Observers
  - [ ] 15.1 Tạo UserObserver để xử lý soft/hard delete
    - Tạo file `app/Observers/UserObserver.php`
    - Implement `deleted(User $user)` - khi soft delete: chuyển listings active/pending_review/scheduled sang paused, log audit với action='auto_paused_user_deleted'
    - Implement `forceDeleted(User $user)` - khi hard delete: batch archive tất cả listings với archived_reason='user_deleted', xử lý 50 listings/batch để tránh timeout
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.7, 15.8_

  - [ ] 15.2 Đăng ký UserObserver trong AppServiceProvider
    - Mở file `app/Providers/AppServiceProvider.php`
    - Trong method `boot()`, thêm: `User::observe(UserObserver::class)`
    - _Requirements: 15.1_

- [ ] 16. Triển khai Frontend - JobPostingForm Component
  - [ ] 16.1 Tạo JobPostingForm.vue component
    - Tạo file `resources/js/components/JobPostingForm.vue`
    - Template: form với tất cả fields theo design (title, description WYSIWYG editor, category dropdown, job_type radio, salary inputs, skills autocomplete, file upload, publish_mode radio)
    - Script setup: reactive form data, validation, submit handler gọi API POST /api/employer/listings
    - Xử lý response: success → redirect, error → hiển thị validation errors
    - Hiển thị quota và rate limit warnings nếu gần đạt giới hạn
    - _Requirements: 1.1, 1.2, 1.5, 1.7, 2.1, 2.3_

  - [ ] 16.2 Thêm Skills Autocomplete vào JobPostingForm
    - Tạo component con `resources/js/components/SkillsAutocomplete.vue`
    - Implement debounced search gọi API `/api/skills/search?q={query}` (cần tạo endpoint này)
    - Hiển thị dropdown với kết quả, cho phép multi-select
    - Integrate với JobPostingForm qua v-model
    - _Requirements: 1.5, 1.6, 11.4_

- [ ] 17. Triển khai Frontend - AnalyticsDashboard Component
  - [ ] 17.1 Tạo AnalyticsDashboard.vue component
    - Tạo file `resources/js/components/AnalyticsDashboard.vue`
    - Template: hiển thị metrics cards (total views, apply clicks, conversion rate), line chart (Chart.js) cho views by day, top traffic sources table
    - Script setup: fetch data từ API GET /api/listings/{id}/analytics
    - Integrate Chart.js library để vẽ biểu đồ
    - Responsive design cho mobile/desktop
    - _Requirements: 8.3, 8.5_

  - [ ] 17.2 Tạo SystemAnalyticsOverview.vue (Admin only)
    - Tạo file `resources/js/components/SystemAnalyticsOverview.vue`
    - Template: hiển thị system-wide metrics, pending review count, avg conversion rate
    - Script setup: fetch data từ API GET /api/analytics/overview
    - Chỉ hiển thị cho admin users
    - _Requirements: 8.7_

- [ ] 18. Triển khai Category Management (Admin)
  - [ ] 18.1 Tạo CategoryController
    - Tạo file `app/Http/Controllers/Api/Admin/CategoryController.php`
    - Implement CRUD: index, store, update, destroy
    - Method `destroy()` check nếu category đang được sử dụng bởi listings → reject với error message
    - _Requirements: 11.1, 11.2_

  - [ ] 18.2 Tạo CategoryManagement.vue component
    - Tạo file `resources/js/components/admin/CategoryManagement.vue`
    - Template: table list categories, form thêm/sửa, delete confirmation modal
    - Script setup: gọi API Category CRUD endpoints
    - _Requirements: 11.1_

- [ ] 19. Triển khai BannedKeyword Management (Admin)
  - [ ] 19.1 Tạo BannedKeywordController
    - Tạo file `app/Http/Controllers/Api/Admin/BannedKeywordController.php`
    - Implement CRUD: index, store, update, destroy
    - Khi update/delete → clear cache key 'banned_keywords'
    - _Requirements: 3.7, 3.8_

  - [ ] 19.2 Tạo BannedKeywordManagement.vue component
    - Tạo file `resources/js/components/admin/BannedKeywordManagement.vue`
    - Template: table list banned keywords với filter active/inactive, severity badges, form thêm/sửa
    - Script setup: gọi API BannedKeyword CRUD endpoints
    - _Requirements: 3.7, 3.8_

- [ ] 20. Tạo Seeders cho dữ liệu mẫu
  - [ ] 20.1 Tạo BannedKeywordSeeder
    - Tạo file `database/seeders/BannedKeywordSeeder.php`
    - Seed 10-15 banned keywords phổ biến (lừa đảo, spam, v.v.)
    - _Requirements: 3.1_

  - [ ] 20.2 Tạo CategorySeeder
    - Tạo file `database/seeders/CategorySeeder.php`
    - Seed 10-15 categories ngành nghề IT phổ biến
    - _Requirements: 11.1_

  - [ ] 20.3 Tạo SkillSeeder
    - Tạo file `database/seeders/SkillSeeder.php`
    - Seed 30-50 skills IT phổ biến (PHP, Laravel, Vue.js, MySQL, v.v.)
    - _Requirements: 1.5_

  - [ ] 20.4 Cập nhật DatabaseSeeder để gọi các seeders
    - Mở file `database/seeders/DatabaseSeeder.php`
    - Trong method `run()`, gọi: BannedKeywordSeeder, CategorySeeder, SkillSeeder
    - _Requirements: 3.1, 11.1, 1.5_

- [ ] 21. Checkpoint - Kiểm tra Frontend và Schedulers
  - Chạy `php artisan schedule:work` trong terminal để test scheduled tasks
  - Mở trình duyệt test JobPostingForm component với tất cả validation cases
  - Test AnalyticsDashboard với data thực
  - Test admin interfaces (Category, BannedKeyword management)
  - Kiểm tra emails đang được queue và gửi đúng
  - Đảm bảo không có lỗi console hay network errors
  - Hỏi người dùng nếu có vấn đề phát sinh

- [ ] 22. Viết Unit Tests
  - [ ]* 22.1 Viết tests cho QuotaService
    - Tạo file `tests/Unit/Services/QuotaServiceTest.php`
    - Test cases: canCreateListing với các plan khác nhau, getActiveListingCount, getQuotaLimit, hasActiveSubscription, admin bypass
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [ ]* 22.2 Viết tests cho RateLimitService
    - Tạo file `tests/Unit/Services/RateLimitServiceTest.php`
    - Test cases: canCreateListing, incrementAttempts, getRemainingAttempts, cache expiration, admin bypass
    - _Requirements: 14.1, 14.2, 14.3, 14.6, 14.7, 14.8_

  - [ ]* 22.3 Viết tests cho ListingStateMachine
    - Tạo file `tests/Unit/Services/ListingStateMachineTest.php`
    - Test cases: canTransition với tất cả valid/invalid transitions, transition thành công, audit logging, event dispatch
    - _Requirements: 4.1, 4.9_

  - [ ]* 22.4 Viết tests cho ModerationService
    - Tạo file `tests/Unit/Services/ModerationServiceTest.php`
    - Test cases: autoModerate với/không có banned keywords, approve thành công, reject với reason, email notification failures
    - _Requirements: 3.1, 3.2, 3.5, 3.6_

- [ ] 23. Viết Feature Tests
  - [ ]* 23.1 Viết tests cho Listing API endpoints
    - Tạo file `tests/Feature/ListingApiTest.php`
    - Test cases: tạo listing với validation errors, quota exceeded, rate limit exceeded, update listing trigger re-moderation, lifecycle actions (pause/resume/close/renew/clone)
    - _Requirements: 1.1, 4.2, 4.3, 4.6, 4.7, 5.1, 6.5, 14.3_

  - [ ]* 23.2 Viết tests cho Moderation API endpoints
    - Tạo file `tests/Feature/ModerationApiTest.php`
    - Test cases: admin approve/reject listing, hard delete với constraints, audit logs retrieval
    - _Requirements: 3.4, 3.5, 3.6, 13.2, 13.3, 13.8_

  - [ ]* 23.3 Viết tests cho Report API
    - Tạo file `tests/Feature/ReportApiTest.php`
    - Test cases: candidate tạo report, duplicate report prevention, auto-pause khi >= 5 reports, admin review reports
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

  - [ ]* 23.4 Viết tests cho Analytics API
    - Tạo file `tests/Feature/AnalyticsApiTest.php`
    - Test cases: track view/apply click, get listing analytics với authorization, system overview (admin only)
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.6, 8.7_

  - [ ]* 23.5 Viết tests cho Scheduled Commands
    - Tạo file `tests/Feature/ScheduledCommandsTest.php`
    - Test cases: PublishScheduledListings, ExpireListings, ArchiveRejectedListings, SendExpiryReminders với error handling
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  - [ ]* 23.6 Viết tests cho UserObserver
    - Tạo file `tests/Feature/UserObserverTest.php`
    - Test cases: soft delete user → listings paused, hard delete user → listings archived (batch processing), audit logs created
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.7, 15.8_

- [ ] 24. Tạo Documentation
  - [ ] 24.1 Tạo API documentation
    - Tạo file `docs/api/job-posting-moderation.md`
    - Document tất cả 22 API endpoints với request/response examples, authentication requirements, error codes
    - _Requirements: All API-related requirements_

  - [ ] 24.2 Tạo deployment guide
    - Tạo file `docs/deployment/job-posting-moderation.md`
    - Hướng dẫn: run migrations, seed data, configure scheduler (crontab hoặc schedule:work), queue workers
    - Cache configuration (Redis recommended)
    - _Requirements: 10.5_

  - [ ] 24.3 Tạo user guide cho NTD
    - Tạo file `docs/user-guide/employer-job-posting.md`
    - Hướng dẫn: tạo tin mới, chọn publish mode, quản lý lifecycle, xem analytics
    - _Requirements: 1.1, 2.1, 4.2, 8.3_

- [ ] 25. Final Checkpoint - End-to-End Testing và Review
  - Chạy toàn bộ test suite: `php artisan test`
  - Kiểm tra code coverage, đảm bảo >= 80% cho Services và Controllers
  - Test end-to-end workflow: NTD tạo listing → Admin duyệt → Candidate xem → Analytics track → Scheduler expire
  - Review security: SQL injection prevention, XSS protection trong form inputs, authorization checks
  - Review performance: database indexes, N+1 query prevention, cache usage
  - Đảm bảo tất cả 15 requirements đều được cover bởi ít nhất 1 task
  - Hỏi người dùng xác nhận trước khi đánh dấu hoàn thành

## Notes

- Tasks đánh dấu `*` là optional (testing tasks) có thể bỏ qua nếu cần MVP nhanh
- Mỗi task đều tham chiếu đến requirements cụ thể để đảm bảo traceability
- Checkpoints được đặt ở các điểm then chốt để kiểm tra tiến độ
- Database migrations phải chạy theo thứ tự đúng (listing table trước, pivot tables sau)
- Services phải được implement trước Controllers vì dependency injection
- Middleware phải đăng ký trong Kernel trước khi sử dụng trong routes
- Scheduled tasks cần setup crontab hoặc chạy `php artisan schedule:work` trên local
- Frontend components sử dụng Vue 3 Composition API và integrate với Laravel API
- Email notifications sử dụng Laravel Queue để tránh blocking requests

## Task Dependency Graph

```json
{
  "waves": [
    {
      "id": 0,
      "tasks": ["1.1", "1.2", "1.3", "1.4", "1.5", "1.6", "1.7", "1.8", "1.9"]
    },
    {
      "id": 1,
      "tasks": ["2.1", "2.2", "2.3", "2.4", "2.5", "2.6"]
    },
    {
      "id": 2,
      "tasks": ["3.1", "3.2", "3.3", "3.4"]
    },
    {
      "id": 3,
      "tasks": ["4.1", "4.2", "4.3", "4.4", "4.5"]
    },
    {
      "id": 4,
      "tasks": ["6.1", "6.2", "6.3", "7.1", "7.2", "7.3"]
    },
    {
      "id": 5,
      "tasks": ["8.1", "8.2", "9.1", "9.2", "9.3", "10.1"]
    },
    {
      "id": 6,
      "tasks": ["11.1", "11.2", "11.3"]
    },
    {
      "id": 7,
      "tasks": ["13.1", "13.2", "13.3", "13.4", "13.5"]
    },
    {
      "id": 8,
      "tasks": ["14.1", "14.2", "14.3", "14.4", "14.5"]
    },
    {
      "id": 9,
      "tasks": ["15.1", "15.2"]
    },
    {
      "id": 10,
      "tasks": ["16.1", "16.2", "17.1", "17.2"]
    },
    {
      "id": 11,
      "tasks": ["18.1", "18.2", "19.1", "19.2"]
    },
    {
      "id": 12,
      "tasks": ["20.1", "20.2", "20.3", "20.4"]
    },
    {
      "id": 13,
      "tasks": ["22.1", "22.2", "22.3", "22.4"]
    },
    {
      "id": 14,
      "tasks": ["23.1", "23.2", "23.3", "23.4", "23.5", "23.6"]
    },
    {
      "id": 15,
      "tasks": ["24.1", "24.2", "24.3"]
    }
  ]
}
```
