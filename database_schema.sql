-- ============================================================
--  DATABASE SCHEMA — Website Tìm Việc IT
--  Laravel 11 + MySQL
--  Bao gồm: tất cả bảng từ 10 module
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- 1. USERS
--    Module: Auth, User Profile, Payment, Admin
-- ============================================================
CREATE TABLE `users` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Auth
    `name`               VARCHAR(255)    NOT NULL,
    `email`              VARCHAR(255)    NOT NULL UNIQUE,
    `email_verified_at`  TIMESTAMP       NULL DEFAULT NULL,
    `password`           VARCHAR(255)    NOT NULL,
    `remember_token`     VARCHAR(100)    NULL DEFAULT NULL,

    -- Profile chung
    `user_type`          ENUM('employee','employer') NOT NULL DEFAULT 'employee',
    `about`              TEXT            NULL,
    `profile_pic`        VARCHAR(255)    NULL,
    `mail`               TINYINT(1)      NOT NULL DEFAULT 1  COMMENT 'Nhận email thông báo',

    -- CV (employee)
    `resume`             VARCHAR(255)    NULL  COMMENT 'Đường dẫn file CV upload',

    -- Profile mở rộng — Employee
    `experience_years`   TINYINT UNSIGNED NULL,
    `desired_salary`     BIGINT UNSIGNED  NULL,
    `location`           VARCHAR(255)    NULL,

    -- Profile mở rộng — Employer
    `company_name`       VARCHAR(255)    NULL,
    `company_logo`       VARCHAR(255)    NULL,
    `company_website`    VARCHAR(255)    NULL,
    `company_size`       VARCHAR(100)    NULL  COMMENT 'VD: 10-50, 50-200, 200+',

    -- Payment & Subscription (Module 8)
    `user_trial`         DATE            NULL  COMMENT 'Hết hạn dùng thử (1 tuần sau đăng ký)',
    `status`             ENUM('paid')    NULL  DEFAULT NULL,
    `plan`               ENUM('monthly','yearly') NULL DEFAULT NULL,
    `billing_ends`       DATE            NULL,

    -- Admin (Module 9)
    `is_admin`           TINYINT(1)      NOT NULL DEFAULT 0,
    `is_banned`          TINYINT(1)      NOT NULL DEFAULT 0,
    `banned_at`          TIMESTAMP       NULL DEFAULT NULL,

    `created_at`         TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`         TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_users_user_type` (`user_type`),
    INDEX `idx_users_status`    (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. SOCIAL ACCOUNTS (OAuth — Socialite)
--    Module: Auth (Google / GitHub)
-- ============================================================
CREATE TABLE `social_accounts` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NOT NULL,
    `provider`    VARCHAR(50)     NOT NULL  COMMENT 'google | github',
    `provider_id` VARCHAR(255)    NOT NULL,
    `token`       TEXT            NULL,
    `created_at`  TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_social_accounts` (`provider`, `provider_id`),
    CONSTRAINT `fk_social_accounts_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. SKILLS
--    Module: Search & Filter, User Profile, Job Posting
-- ============================================================
CREATE TABLE `skills` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)    NOT NULL UNIQUE,
    `slug`       VARCHAR(100)    NOT NULL UNIQUE,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. USER_SKILL (Pivot)
--    Module: User Profile (employee skills)
-- ============================================================
CREATE TABLE `user_skill` (
    `user_id`  BIGINT UNSIGNED NOT NULL,
    `skill_id` BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (`user_id`, `skill_id`),
    CONSTRAINT `fk_user_skill_user`
        FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`)  ON DELETE CASCADE,
    CONSTRAINT `fk_user_skill_skill`
        FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. LISTINGS (Tin tuyển dụng)
--    Module: Job Posting, Search & Filter
-- ============================================================
CREATE TABLE `listings` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`               BIGINT UNSIGNED NOT NULL  COMMENT 'Employer đăng bài',

    `title`                 VARCHAR(255)    NOT NULL,
    `slug`                  VARCHAR(300)    NOT NULL UNIQUE,
    `predes`                VARCHAR(500)    NULL  COMMENT 'Mô tả ngắn',
    `description`           LONGTEXT        NOT NULL,
    `roles`                 TEXT            NOT NULL  COMMENT 'Yêu cầu / quyền lợi',

    `job_type`              VARCHAR(100)    NOT NULL  COMMENT 'Full-time, Part-time, Remote...',
    `address`               VARCHAR(255)    NOT NULL,
    `salary`                BIGINT UNSIGNED NOT NULL DEFAULT 0  COMMENT '0 = Thỏa thuận',

    `feature_image`         VARCHAR(255)    NULL,
    `application_close_date` DATE           NOT NULL,

    -- Mở rộng (Module 3)
    `status`                ENUM('open','hidden','closed') NOT NULL DEFAULT 'open',

    `created_at`            TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`            TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_listings_user_id`  (`user_id`),
    INDEX `idx_listings_status`   (`status`),
    INDEX `idx_listings_address`  (`address`),
    INDEX `idx_listings_job_type` (`job_type`),
    INDEX `idx_listings_salary`   (`salary`),
    FULLTEXT INDEX `ft_listings_title_desc` (`title`, `predes`),
    CONSTRAINT `fk_listings_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. LISTING_SKILL (Pivot)
--    Module: Job Posting, Search & Filter
-- ============================================================
CREATE TABLE `listing_skill` (
    `listing_id` BIGINT UNSIGNED NOT NULL,
    `skill_id`   BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (`listing_id`, `skill_id`),
    CONSTRAINT `fk_listing_skill_listing`
        FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_listing_skill_skill`
        FOREIGN KEY (`skill_id`)   REFERENCES `skills`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. APPLICATIONS
--    Module: Apply & Tracking
--    Bảng riêng thay thế pivot listing_user
-- ============================================================
CREATE TABLE `applications` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `listing_id`   BIGINT UNSIGNED NOT NULL,
    `user_id`      BIGINT UNSIGNED NOT NULL  COMMENT 'Ứng viên',

    `status`       ENUM('pending','reviewing','interviewed','accepted','rejected')
                   NOT NULL DEFAULT 'pending',
    `shortlisted`  TINYINT(1)      NOT NULL DEFAULT 0,

    `cover_letter` TEXT            NULL,
    `note`         TEXT            NULL  COMMENT 'Ghi chú nội bộ của employer',

    `created_at`   TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`   TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_application` (`listing_id`, `user_id`),
    INDEX `idx_app_user_id`   (`user_id`),
    INDEX `idx_app_status`    (`status`),
    CONSTRAINT `fk_app_listing`
        FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_user`
        FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7b. APPLICATION_STATUS_LOGS (Timeline lịch sử trạng thái)
--     Module: Apply & Tracking
-- ============================================================
CREATE TABLE `application_status_logs` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `application_id` BIGINT UNSIGNED NOT NULL,
    `changed_by`     BIGINT UNSIGNED NOT NULL  COMMENT 'user_id người thay đổi (employer/admin)',

    `old_status`     ENUM('pending','reviewing','interviewed','accepted','rejected') NULL,
    `new_status`     ENUM('pending','reviewing','interviewed','accepted','rejected') NOT NULL,
    `note`           VARCHAR(500)    NULL  COMMENT 'Lý do hoặc ghi chú khi đổi trạng thái',

    `created_at`     TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_asl_application` (`application_id`),
    CONSTRAINT `fk_asl_application`
        FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_asl_changed_by`
        FOREIGN KEY (`changed_by`)     REFERENCES `users`(`id`)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. CV_DATA (CV Builder — lưu nội dung form)
--    Module: CV Builder
-- ============================================================
CREATE TABLE `cv_data` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`         BIGINT UNSIGNED NOT NULL,

    -- Thông tin cá nhân
    `full_name`       VARCHAR(255)    NULL,
    `phone`           VARCHAR(20)     NULL,
    `email`           VARCHAR(255)    NULL,
    `address`         VARCHAR(255)    NULL,
    `photo_path`      VARCHAR(255)    NULL,

    -- Nội dung CV (lưu JSON hoặc text)
    `objective`       TEXT            NULL  COMMENT 'Mục tiêu nghề nghiệp',
    `education`       JSON            NULL  COMMENT '[{school, degree, year_start, year_end}]',
    `experience`      JSON            NULL  COMMENT '[{company, role, year_start, year_end, desc}]',
    `projects`        JSON            NULL  COMMENT '[{name, tech, url, desc}]',
    `certifications`  JSON            NULL  COMMENT '[{name, issuer, year}]',
    `skills_text`     TEXT            NULL  COMMENT 'Danh sách kỹ năng dạng text',
    `languages`       JSON            NULL  COMMENT '[{lang, level}]',

    -- Template được chọn
    `template`        VARCHAR(50)     NOT NULL DEFAULT 'default',

    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cv_data_user` (`user_id`),
    CONSTRAINT `fk_cv_data_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. TRANSACTIONS (Lịch sử thanh toán VNPay)
--    Module: Payment & Subscription, Admin Dashboard
-- ============================================================
CREATE TABLE `transactions` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`         BIGINT UNSIGNED NOT NULL,

    `vnp_txn_ref`     VARCHAR(100)    NOT NULL UNIQUE  COMMENT 'Mã giao dịch VNPay',
    `vnp_order_info`  VARCHAR(255)    NULL,
    `amount`          BIGINT UNSIGNED NOT NULL  COMMENT 'Số tiền (VNĐ)',
    `plan`            ENUM('monthly','yearly') NOT NULL,

    `status`          ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `vnp_response_code` VARCHAR(10)   NULL  COMMENT 'Mã phản hồi từ VNPay',
    `vnp_transaction_no` VARCHAR(100) NULL  COMMENT 'Số giao dịch phía VNPay',
    `paid_at`         TIMESTAMP       NULL DEFAULT NULL,

    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_transactions_user_id` (`user_id`),
    INDEX `idx_transactions_status`  (`status`),
    CONSTRAINT `fk_transactions_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. CONVERSATIONS (Chat & Messaging)
--     Module: Chat & Messaging
-- ============================================================
CREATE TABLE `conversations` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employer_id` BIGINT UNSIGNED NOT NULL,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `listing_id`  BIGINT UNSIGNED NOT NULL,

    `created_at`  TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_conversation` (`employer_id`, `employee_id`, `listing_id`),
    INDEX `idx_conv_employee` (`employee_id`),
    INDEX `idx_conv_listing`  (`listing_id`),
    CONSTRAINT `fk_conv_employer`
        FOREIGN KEY (`employer_id`) REFERENCES `users`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_conv_employee`
        FOREIGN KEY (`employee_id`) REFERENCES `users`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_conv_listing`
        FOREIGN KEY (`listing_id`)  REFERENCES `listings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. MESSAGES
--     Module: Chat & Messaging
-- ============================================================
CREATE TABLE `messages` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id` BIGINT UNSIGNED NOT NULL,
    `sender_id`       BIGINT UNSIGNED NOT NULL,

    `body`            TEXT            NOT NULL,
    `read_at`         TIMESTAMP       NULL DEFAULT NULL,

    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_messages_conversation` (`conversation_id`),
    INDEX `idx_messages_sender`       (`sender_id`),
    CONSTRAINT `fk_messages_conversation`
        FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_sender`
        FOREIGN KEY (`sender_id`)       REFERENCES `users`(`id`)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 12. NOTIFICATIONS (DB notifications — Laravel)
--     Module: Notification
-- ============================================================
CREATE TABLE `notifications` (
    `id`             CHAR(36)        NOT NULL  COMMENT 'UUID',
    `type`           VARCHAR(255)    NOT NULL  COMMENT 'Class notification',
    `notifiable_type` VARCHAR(255)   NOT NULL,
    `notifiable_id`  BIGINT UNSIGNED NOT NULL,
    `data`           JSON            NOT NULL,
    `read_at`        TIMESTAMP       NULL DEFAULT NULL,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_notifications_notifiable` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. BẢNG HỆ THỐNG LARAVEL (giữ nguyên)
-- ============================================================

CREATE TABLE `password_reset_tokens` (
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `personal_access_tokens` (
    `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `tokenable_type` VARCHAR(255)     NOT NULL,
    `tokenable_id`   BIGINT UNSIGNED  NOT NULL,
    `name`           VARCHAR(255)     NOT NULL,
    `token`          VARCHAR(64)      NOT NULL UNIQUE,
    `abilities`      TEXT             NULL,
    `last_used_at`   TIMESTAMP        NULL DEFAULT NULL,
    `expires_at`     TIMESTAMP        NULL DEFAULT NULL,
    `created_at`     TIMESTAMP        NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP        NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_pat_tokenable` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `failed_jobs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`       VARCHAR(255)    NOT NULL UNIQUE,
    `connection` TEXT            NOT NULL,
    `queue`      TEXT            NOT NULL,
    `payload`    LONGTEXT        NOT NULL,
    `exception`  LONGTEXT        NOT NULL,
    `failed_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `jobs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255)    NOT NULL,
    `payload`      LONGTEXT        NOT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED    NULL DEFAULT NULL,
    `available_at` INT UNSIGNED    NOT NULL,
    `created_at`   INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_jobs_queue` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TÓM TẮT CÁC BẢNG
-- ============================================================
-- Core:
--   users                 → Auth, Profile, Payment, Admin
--   social_accounts       → Auth (Socialite OAuth)
-- Skills:
--   skills                → danh mục kỹ năng
--   user_skill            → skill của ứng viên
--   listing_skill         → skill yêu cầu của tin
-- Jobs:
--   listings              → tin tuyển dụng
--   applications          → đơn ứng tuyển + trạng thái
--   application_status_logs → timeline lịch sử đổi trạng thái
-- CV:
--   cv_data               → nội dung CV Builder
-- Payment:
--   transactions          → lịch sử thanh toán VNPay
-- Chat:
--   conversations         → cuộc hội thoại employer↔employee
--   messages              → tin nhắn
-- Notification:
--   notifications         → thông báo in-app (Laravel DB)
-- System:
--   password_reset_tokens, personal_access_tokens,
--   failed_jobs, jobs
-- ============================================================
