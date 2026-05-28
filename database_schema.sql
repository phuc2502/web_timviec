-- ============================================================
--  DATABASE SCHEMA — Website Tìm Việc IT
--  Laravel 11 + MySQL 8.0+
--  Phiên bản: đã sửa lỗi và tối ưu
-- ============================================================
--
--  THAY ĐỔI SO VỚI BẢN GỐC:
--  [FIX-1] users.status       → ENUM mở rộng + NOT NULL DEFAULT 'unpaid'
--  [FIX-2] asl.changed_by     → ON DELETE SET NULL (tránh mất log)
--  [FIX-3] transactions FK    → ON DELETE RESTRICT (bảo toàn lịch sử tài chính)
--  [FIX-4] listings.roles     → đổi TEXT → JSON + đổi tên rõ hơn
--  [FIX-5] listings.job_type  → chuẩn hóa ENUM
--  [FIX-6] conversations.listing_id → cho phép NULL
--  [FIX-7] users.mail         → đổi tên thành email_notify
--  [FIX-8] Thêm index billing_ends, is_banned
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- 1. USERS
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
    -- [FIX-7] đổi tên 'mail' → 'email_notify' cho rõ nghĩa
    `email_notify`       TINYINT(1)      NOT NULL DEFAULT 1  COMMENT 'Nhận email thông báo',

    -- CV (employee)
    `resume`             VARCHAR(255)    NULL  COMMENT 'Đường dẫn file CV upload',

    -- Profile mở rộng — Employee
    `experience_years`   TINYINT UNSIGNED NULL,
    `desired_salary`     INT UNSIGNED    NULL  COMMENT 'Mức lương mong muốn (VNĐ)',
    `location`           VARCHAR(255)    NULL,

    -- Profile mở rộng — Employer
    `company_name`       VARCHAR(255)    NULL,
    `company_logo`       VARCHAR(255)    NULL,
    `company_website`    VARCHAR(255)    NULL,
    `company_size`       ENUM('1-9','10-49','50-199','200-499','500+')
                         NULL  COMMENT 'Quy mô công ty',

    -- Payment & Subscription
    `user_trial`         TIMESTAMP       NULL  COMMENT 'Thời điểm hết hạn dùng thử',
    -- [FIX-1] ENUM mở rộng, mặc định 'unpaid'
    `status`             ENUM('unpaid','paid','expired') NOT NULL DEFAULT 'unpaid',
    `plan`               ENUM('monthly','yearly') NULL DEFAULT NULL,
    `billing_ends`       DATE            NULL,

    -- Admin
    `is_admin`           TINYINT(1)      NOT NULL DEFAULT 0,
    `is_banned`          TINYINT(1)      NOT NULL DEFAULT 0,
    `banned_at`          TIMESTAMP       NULL DEFAULT NULL,

    `created_at`         TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`         TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_users_user_type`    (`user_type`),
    INDEX `idx_users_status`       (`status`),
    -- [FIX-8] thêm index cho các cột hay dùng filter
    INDEX `idx_users_billing_ends` (`billing_ends`),
    INDEX `idx_users_is_banned`    (`is_banned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. SOCIAL ACCOUNTS (OAuth — Socialite)
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
-- ============================================================
CREATE TABLE `listings` (
    `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`                 BIGINT UNSIGNED NOT NULL  COMMENT 'Employer đăng bài',

    `title`                   VARCHAR(255)    NOT NULL,
    `slug`                    VARCHAR(300)    NOT NULL UNIQUE,
    `predes`                  VARCHAR(500)    NULL  COMMENT 'Mô tả ngắn',
    `description`             LONGTEXT        NOT NULL,

    -- [FIX-4] tách roles TEXT → 2 cột JSON rõ ràng hơn
    `requirements`            JSON            NULL  COMMENT '[{"item": "..."}]  Yêu cầu ứng viên',
    `benefits`                JSON            NULL  COMMENT '[{"item": "..."}]  Quyền lợi',

    -- [FIX-5] chuẩn hóa job_type thành ENUM
    `job_type`                ENUM('full-time','part-time','remote','hybrid','freelance','internship')
                              NOT NULL DEFAULT 'full-time',

    `address`                 VARCHAR(255)    NOT NULL,
    `salary`                  INT UNSIGNED    NOT NULL DEFAULT 0  COMMENT '0 = Thỏa thuận',

    `feature_image`           VARCHAR(255)    NULL,
    `application_close_date`  DATE            NOT NULL,

    `status`                  ENUM('open','hidden','closed') NOT NULL DEFAULT 'open',

    `created_at`              TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`              TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_listings_user_id`  (`user_id`),
    INDEX `idx_listings_status`   (`status`),
    INDEX `idx_listings_address`  (`address`),
    INDEX `idx_listings_job_type` (`job_type`),
    INDEX `idx_listings_salary`   (`salary`),
    INDEX `idx_listings_close_date` (`application_close_date`),
    FULLTEXT INDEX `ft_listings_title_desc` (`title`, `predes`),
    CONSTRAINT `fk_listings_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. LISTING_SKILL (Pivot)
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
    UNIQUE KEY `uq_application`   (`listing_id`, `user_id`),
    INDEX `idx_app_user_id`       (`user_id`),
    INDEX `idx_app_status`        (`status`),
    CONSTRAINT `fk_app_listing`
        FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_user`
        FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7b. APPLICATION_STATUS_LOGS
-- ============================================================
CREATE TABLE `application_status_logs` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `application_id` BIGINT UNSIGNED NOT NULL,
    -- [FIX-2] NULL + SET NULL để tránh mất log khi xóa user
    `changed_by`     BIGINT UNSIGNED NULL  COMMENT 'user_id người thay đổi (employer/admin)',

    `old_status`     ENUM('pending','reviewing','interviewed','accepted','rejected') NULL,
    `new_status`     ENUM('pending','reviewing','interviewed','accepted','rejected') NOT NULL,
    `note`           VARCHAR(500)    NULL,

    `created_at`     TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_asl_application` (`application_id`),
    CONSTRAINT `fk_asl_application`
        FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_asl_changed_by`
        FOREIGN KEY (`changed_by`)     REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. CV_DATA
-- ============================================================
CREATE TABLE `cv_data` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`         BIGINT UNSIGNED NOT NULL,

    `full_name`       VARCHAR(255)    NULL,
    `phone`           VARCHAR(20)     NULL,
    `email`           VARCHAR(255)    NULL,
    `address`         VARCHAR(255)    NULL,
    `photo_path`      VARCHAR(255)    NULL,

    `objective`       TEXT            NULL,
    `education`       JSON            NULL  COMMENT '[{school, degree, year_start, year_end}]',
    `experience`      JSON            NULL  COMMENT '[{company, role, year_start, year_end, desc}]',
    `projects`        JSON            NULL  COMMENT '[{name, tech, url, desc}]',
    `certifications`  JSON            NULL  COMMENT '[{name, issuer, year}]',
    `skills_text`     TEXT            NULL,
    `languages`       JSON            NULL  COMMENT '[{lang, level}]',

    `template`        VARCHAR(50)     NOT NULL DEFAULT 'default',

    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    -- Giữ UNIQUE: 1 user 1 CV. Nếu sau cần multi-CV thì bỏ constraint này.
    UNIQUE KEY `uq_cv_data_user` (`user_id`),
    CONSTRAINT `fk_cv_data_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. TRANSACTIONS
-- ============================================================
CREATE TABLE `transactions` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`             BIGINT UNSIGNED NOT NULL,

    `vnp_txn_ref`         VARCHAR(100)    NOT NULL UNIQUE,
    `vnp_order_info`      VARCHAR(255)    NULL,
    `amount`              BIGINT UNSIGNED NOT NULL  COMMENT 'Số tiền (VNĐ)',
    `plan`                ENUM('monthly','yearly') NOT NULL,

    `status`              ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `vnp_response_code`   VARCHAR(10)     NULL,
    `vnp_transaction_no`  VARCHAR(100)    NULL,
    `paid_at`             TIMESTAMP       NULL DEFAULT NULL,

    `created_at`          TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_transactions_user_id` (`user_id`),
    INDEX `idx_transactions_status`  (`status`),
    INDEX `idx_transactions_paid_at` (`paid_at`),
    -- [FIX-3] RESTRICT: không cho xóa user khi còn lịch sử giao dịch
    CONSTRAINT `fk_transactions_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. CONVERSATIONS
-- ============================================================
CREATE TABLE `conversations` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employer_id` BIGINT UNSIGNED NOT NULL,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    -- [FIX-6] listing_id nullable: cho phép nhắn tin không gắn tin tuyển dụng
    `listing_id`  BIGINT UNSIGNED NULL,

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
        FOREIGN KEY (`listing_id`)  REFERENCES `listings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. MESSAGES
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
-- 12. NOTIFICATIONS
-- ============================================================
CREATE TABLE `notifications` (
    `id`              CHAR(36)        NOT NULL  COMMENT 'UUID',
    `type`            VARCHAR(255)    NOT NULL,
    `notifiable_type` VARCHAR(255)    NOT NULL,
    `notifiable_id`   BIGINT UNSIGNED NOT NULL,
    `data`            JSON            NOT NULL,
    `read_at`         TIMESTAMP       NULL DEFAULT NULL,
    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_notifications_notifiable` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. BẢNG HỆ THỐNG LARAVEL
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
-- LỆNH CHẠY:
--   mysql -u root -p ten_database < schema_fixed.sql
-- ============================================================