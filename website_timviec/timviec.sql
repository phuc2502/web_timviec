-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 09:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `timviec`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `cv_id` bigint(20) UNSIGNED NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `applicant_name` varchar(100) DEFAULT NULL,
  `applicant_phone` varchar(20) DEFAULT NULL,
  `applicant_email` varchar(255) DEFAULT NULL,
  `status` enum('submitted','viewed','approved','interviewing','rejected') NOT NULL DEFAULT 'submitted',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `interview_scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `listing_id`, `cv_id`, `cover_letter`, `applicant_name`, `applicant_phone`, `applicant_email`, `status`, `applied_at`, `status_updated_at`, `interview_scheduled_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, NULL, NULL, NULL, 'interviewing', '2026-06-03 01:26:02', '2026-06-03 01:44:20', '2026-06-11 01:44:00', '2026-06-03 01:26:02', '2026-06-03 01:44:20'),
(2, 3, 1, 2, NULL, NULL, NULL, NULL, 'interviewing', '2026-06-03 01:54:36', '2026-06-03 01:56:10', '2026-06-23 01:56:00', '2026-06-03 01:54:36', '2026-06-03 01:56:10'),
(3, 3, 2, 2, NULL, NULL, NULL, NULL, 'rejected', '2026-06-03 03:21:55', '2026-06-03 03:23:26', NULL, '2026-06-03 03:20:48', '2026-06-03 03:23:26'),
(4, 3, 5, 2, 'hhh', NULL, NULL, NULL, 'rejected', '2026-06-03 03:33:58', '2026-06-03 03:36:11', NULL, '2026-06-03 03:33:23', '2026-06-03 03:36:11'),
(5, 1, 2, 1, NULL, NULL, NULL, NULL, 'submitted', '2026-06-03 08:37:25', NULL, NULL, '2026-06-03 08:37:25', '2026-06-03 08:37:25'),
(6, 1, 6, 4, NULL, 'Lâm Trúc', '0354337920', 'lamtrucnguyen08032k5@gmail.com', 'interviewing', '2026-06-04 02:48:37', '2026-06-04 02:57:18', '2026-06-23 02:57:00', '2026-06-03 12:09:43', '2026-06-04 02:57:19'),
(7, 3, 6, 2, 'hhh', 'Lâm Trúc', '0354337920', 'lamtruk0803@gmail.com', 'submitted', '2026-06-03 12:10:46', NULL, NULL, '2026-06-03 12:10:46', '2026-06-03 12:10:46'),
(8, 6, 6, 3, NULL, 'He', '0354337920', 'test08@gmail.com', 'submitted', '2026-06-03 12:12:10', NULL, NULL, '2026-06-03 12:12:10', '2026-06-03 12:12:10'),
(9, 1, 4, 6, NULL, 'Lâm Trúc', '0354337920', 'lamtrucnguyen08032k5@gmail.com', 'submitted', '2026-06-04 03:05:53', NULL, NULL, '2026-06-04 03:03:17', '2026-06-04 03:05:53'),
(10, 1, 5, 7, 'hh', 'Lâm Trúc', '0354337920', 'lamtrucnguyen08032k5@gmail.com', 'viewed', '2026-06-04 03:20:09', '2026-06-04 03:20:34', NULL, '2026-06-04 03:19:04', '2026-06-04 03:20:34'),
(11, 7, 5, 8, NULL, 'Mèo', '0354337920', 'meomeo03@gmail.com', 'submitted', '2026-06-04 03:22:41', NULL, NULL, '2026-06-04 03:22:41', '2026-06-04 03:22:41');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cvs`
--

CREATE TABLE `cvs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cvs`
--

INSERT INTO `cvs` (`id`, `user_id`, `file_path`, `original_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'cvs/1/5d892a9f-2819-458a-971e-74901feac58b.docx', 'Trúc_PHP.docx', '2026-06-03 01:26:02', '2026-06-03 01:26:02'),
(2, 3, 'cvs/3/de43c7fb-fd9c-42b8-86fb-ea31d2312ce1.docx', '5d892a9f-2819-458a-971e-74901feac58b.docx', '2026-06-03 01:54:36', '2026-06-03 01:54:36'),
(3, 6, 'cvs/6/da8d26ba-dfab-4bac-9b4b-675897f6bd3c.docx', 'Chuong3_HeThongQuanLyQuangCao (1).docx', '2026-06-03 12:12:10', '2026-06-03 12:12:10'),
(4, 1, 'cvs/1/5024f455-6000-43ee-b449-0f3742df0350.docx', 'Chuong3_HeThongQuanLyQuangCao.docx', '2026-06-04 02:48:37', '2026-06-04 02:48:37'),
(5, 1, 'cvs/1/4e1e3685-1900-417b-97c5-88ba2ffd871e.pdf', 'Nhom3_QuanLyCaPhe_9,2.pdf', '2026-06-04 03:03:49', '2026-06-04 03:03:49'),
(6, 1, 'cvs/1/b723ec10-a5ab-43db-8244-32dfa185bc9d.pdf', 'AN_AUTOMATED_RESUME_SCREENING_SYSTEM_USING_NATURAL.pdf', '2026-06-04 03:05:53', '2026-06-04 03:05:53'),
(7, 1, 'cvs/1/9acb3070-ed1a-48cd-8630-a1aef45c6404.docx', 'Chuong3_HeThongQuanLyQuangCao (1).docx', '2026-06-04 03:20:09', '2026-06-04 03:20:09'),
(8, 7, 'cvs/7/7e70fe78-cd61-4f57-bc68-1e70a321fa3d.docx', 'Chuong3_HeThongQuanLyQuangCao (1).docx', '2026-06-04 03:22:41', '2026-06-04 03:22:41');

-- --------------------------------------------------------

--
-- Table structure for table `cv_data`
--

CREATE TABLE `cv_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `objective` text DEFAULT NULL,
  `education` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`education`)),
  `experience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`experience`)),
  `projects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`projects`)),
  `certifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`certifications`)),
  `skills_text` text DEFAULT NULL,
  `languages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`languages`)),
  `template` varchar(50) NOT NULL DEFAULT 'default',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `roles` text DEFAULT NULL,
  `predes` text DEFAULT NULL,
  `salary` bigint(20) UNSIGNED DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `job_type` varchar(255) DEFAULT NULL,
  `feature_image` varchar(255) DEFAULT NULL,
  `application_close_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `user_id`, `title`, `slug`, `description`, `roles`, `predes`, `salary`, `address`, `job_type`, `feature_image`, `application_close_date`, `created_at`, `updated_at`) VALUES
(1, 2, 'Fresh Tester', 'fresh-tester-Togz4B', 'Tham gia kiểm thử phần mềm (website/app) theo hướng dẫn\r\nViết test case, test scenario từ tài liệu yêu cầu\r\nThực hiện kiểm thử chức năng (functional testing)\r\nBáo lỗi và theo dõi quá trình xử lý lỗi\r\nHỗ trợ kiểm thử lại sau khi fix bug\r\nLàm việc với team Dev, BA để đảm bảo chất lượng sản phẩm', 'Sinh viên mới tốt nghiệp hoặc dưới 1 năm kinh nghiệm\r\nCó kiến thức cơ bản về kiểm thử phần mềm\r\nHiểu cơ bản về quy trình phát triển phần mềm (SDLC)\r\nBiết viết test case là một lợi thế\r\nCó kiến thức cơ bản về API, HTTP là điểm cộng\r\nCẩn thận, tỉ mỉ, có trách nhiệm\r\nCó tinh thần học hỏi, chủ động', 'Lương thỏa thuận theo năng lực\r\nĐược đào tạo từ đầu (on-job training)\r\nCơ hội phát triển lên QA/Tester chính thức\r\nMôi trường làm việc trẻ trung, hỗ trợ\r\nTham gia dự án thực tế', 200000, 'Hồ Chí Minh', 'Full-time', NULL, NULL, '2026-06-03 01:25:13', '2026-06-03 01:25:13'),
(2, 2, 'Intern Tester', 'intern-tester-2juAIj', 'Tham gia kiểm thử các chức năng của hệ thống website/app\r\nViết test case, test scenario dựa trên tài liệu yêu cầu\r\nThực hiện test manual (UI, API cơ bản)\r\nBáo lỗi (bug) và theo dõi quá trình fix bug với team dev\r\nHỗ trợ kiểm thử hồi quy (regression testing) sau khi fix lỗi\r\nPhối hợp với các bộ phận khác để đảm bảo chất lượng sản phẩm', 'Sinh viên năm 3, 4 hoặc mới tốt nghiệp ngành CNTT hoặc liên quan\r\nCó kiến thức cơ bản về kiểm thử phần mềm (Tester)\r\nHiểu quy trình phát triển phần mềm (SDLC)\r\nBiết viết test case là lợi thế\r\nCó kiến thức cơ bản về API, HTTP, REST là điểm cộng\r\nCẩn thận, tỉ mỉ, có trách nhiệm trong công việc\r\nCó laptop cá nhân', 'Hỗ trợ thực tập + phụ cấp (nếu có)\r\nĐược đào tạo bài bản về kiểm thử phần mềm\r\nCơ hội trở thành nhân viên chính thức\r\nMôi trường làm việc trẻ trung, năng động\r\nTham gia các hoạt động nội bộ của công ty', 500000, 'Hà Nội', 'Part-time', NULL, NULL, '2026-06-03 03:19:23', '2026-06-03 03:19:23'),
(3, 4, 'hh', 'hh-5tXECD', 'hh', 'hh', 'hh', 572941, 'Hồ Chí Minh', 'Full-time', NULL, '2026-07-01 17:00:00', '2026-06-03 03:31:20', '2026-06-03 03:31:20'),
(4, 4, 'dhet', 'dhet-3KLCQY', 'stqư', 'áqtưt', 'etqtqư', 24249, 'Cần Thơ', 'Full-time', NULL, '2026-06-14 17:00:00', '2026-06-03 03:31:44', '2026-06-03 03:31:44'),
(5, 4, 'ưqt', 'uqt-eDBE0Z', 'qưtqư', 'qưt', 'ưtqt', 632463, 'Hồ Chí Minh', 'Part-time', NULL, '2026-06-17 17:00:00', '2026-06-03 03:32:03', '2026-06-03 03:32:03'),
(6, 2, 'gg', 'gg-KU0YJL', 'gg', 'gg', 'gg', 632512, 'Remote', 'Full-time', NULL, '2026-07-02 17:00:00', '2026-06-03 10:17:14', '2026-06-03 10:17:14');

-- --------------------------------------------------------

--
-- Table structure for table `listing_user`
--

CREATE TABLE `listing_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `shortlisted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_28_000000_create_cv_data_table', 1),
(5, '2026_05_28_000001_add_profile_fields_to_users_table', 1),
(6, '2026_05_28_000002_create_listings_and_pivot_tables', 1),
(7, '2026_06_01_000002_create_cvs_table', 1),
(8, '2026_06_01_000003_create_applications_table', 1),
(9, '2026_06_01_000004_create_user_tokens_table', 1),
(10, '2026_06_01_000005_create_subscriptions_table', 1),
(11, '2026_06_01_000006_create_payments_table', 1),
(12, '2026_06_04_000001_update_applications_add_interview_fields', 1),
(13, '2026_06_04_000002_create_notifications_table', 1),
(14, '2026_06_04_000001_add_contact_snapshot_to_applications', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'application_status', 'Hồ sơ của bạn được duyệt! 🎉', 'Chúc mừng! FPT Software đã duyệt hồ sơ ứng tuyển vị trí **Fresh Tester** của bạn.', '{\"application_id\":1,\"listing_id\":1,\"status\":\"approved\",\"old_status\":\"viewed\"}', '2026-06-03 09:44:05', '2026-06-03 01:42:10', '2026-06-03 09:44:05'),
(2, 1, 'application_status', 'Bạn được mời phỏng vấn! 🎯', 'Chúc mừng! FPT Software mời bạn tham gia phỏng vấn cho vị trí **Fresh Tester**. Thời gian dự kiến: 11/06/2026 08:44', '{\"application_id\":1,\"listing_id\":1,\"status\":\"interviewing\",\"old_status\":\"approved\"}', '2026-06-03 01:45:49', '2026-06-03 01:44:20', '2026-06-03 01:45:49'),
(3, 3, 'application_status', 'Bạn được mời phỏng vấn! 🎯', 'Chúc mừng! FPT Software mời bạn tham gia phỏng vấn cho vị trí **Fresh Tester**. Thời gian dự kiến: 23/06/2026 08:56', '{\"application_id\":2,\"listing_id\":1,\"status\":\"interviewing\",\"old_status\":\"viewed\"}', NULL, '2026-06-03 01:56:10', '2026-06-03 01:56:10'),
(4, 3, 'application_status', 'Thông báo kết quả hồ sơ', 'FPT Software thông báo hồ sơ ứng tuyển vị trí **Intern Tester** của bạn chưa phù hợp lần này.', '{\"application_id\":3,\"listing_id\":2,\"status\":\"rejected\",\"old_status\":\"viewed\"}', NULL, '2026-06-03 03:23:26', '2026-06-03 03:23:26'),
(5, 3, 'application_status', 'Thông báo kết quả hồ sơ', 'Trung Nguyên Legend thông báo hồ sơ ứng tuyển vị trí **ưqt** của bạn chưa phù hợp lần này.', '{\"application_id\":4,\"listing_id\":5,\"status\":\"rejected\",\"old_status\":\"viewed\"}', NULL, '2026-06-03 03:36:11', '2026-06-03 03:36:11'),
(6, 1, 'application_status', 'Hồ sơ của bạn được duyệt! 🎉', 'Chúc mừng! FPT Software đã duyệt hồ sơ ứng tuyển vị trí **gg** của bạn.', '{\"application_id\":6,\"listing_id\":6,\"status\":\"approved\",\"old_status\":\"viewed\"}', NULL, '2026-06-04 02:56:32', '2026-06-04 02:56:32'),
(7, 1, 'application_status', 'Bạn được mời phỏng vấn! 🎯', 'Chúc mừng! FPT Software mời bạn tham gia phỏng vấn cho vị trí **gg**. Thời gian dự kiến: 23/06/2026 09:57', '{\"application_id\":6,\"listing_id\":6,\"status\":\"interviewing\",\"old_status\":\"approved\"}', NULL, '2026-06-04 02:57:19', '2026-06-04 02:57:19'),
(8, 1, 'application_status', 'Hồ sơ của bạn đã được xem', 'Trung Nguyên Legend đã xem hồ sơ ứng tuyển vị trí **ưqt** của bạn.', '{\"application_id\":10,\"listing_id\":5,\"status\":\"viewed\",\"old_status\":\"submitted\"}', NULL, '2026-06-04 03:20:34', '2026-06-04 03:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('token','subscription') NOT NULL,
  `amount` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `vnpay_txn_ref` varchar(255) DEFAULT NULL,
  `vnpay_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vnpay_response`)),
  `token_amount` int(10) UNSIGNED DEFAULT NULL,
  `plan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `type`, `amount`, `status`, `vnpay_txn_ref`, `vnpay_response`, `token_amount`, `plan`, `created_at`, `updated_at`) VALUES
(1, 1, 'token', 50000, 'success', '20260603082632_V1ZE9K', '{\"vnp_Amount\":\"5000000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15567079\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Mua 5 luot ung tuyen\",\"vnp_PayDate\":\"20260603082731\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"5RVH1EKN\",\"vnp_TransactionNo\":\"15567079\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"20260603082632_V1ZE9K\",\"vnp_SecureHash\":\"398cab951de6f85b972ccf84907d2e99a4c08702d7ba356146652087a8d84d3d05c1eeca026e7ba9cf5cf32c9a9fd8f663332ee1414abea7c0aee688f89bb164\"}', 5, NULL, '2026-06-03 01:26:32', '2026-06-03 01:27:36'),
(2, 2, 'subscription', 299000, 'success', '20260603101704_T9ZYB7', '{\"vnp_Amount\":\"29900000\",\"vnp_BankCode\":\"NCB\",\"vnp_BankTranNo\":\"VNP15567324\",\"vnp_CardType\":\"ATM\",\"vnp_OrderInfo\":\"Mua goi Premium monthly\",\"vnp_PayDate\":\"20260603101738\",\"vnp_ResponseCode\":\"00\",\"vnp_TmnCode\":\"5RVH1EKN\",\"vnp_TransactionNo\":\"15567324\",\"vnp_TransactionStatus\":\"00\",\"vnp_TxnRef\":\"20260603101704_T9ZYB7\",\"vnp_SecureHash\":\"3bbfcf52e7c7e32f7f0a1e8feb84e20b3ea4b317a9a5f94f217f7dc8008da40d08e43f51b783d74469e428750a5ffe7a9aa4e1be3cdbc370f4d4afeed76d47e3\"}', NULL, 'monthly', '2026-06-03 03:17:04', '2026-06-03 03:17:43'),
(3, 4, 'subscription', 299000, 'pending', '20260603103223_FSEHTN', NULL, NULL, 'monthly', '2026-06-03 03:32:23', '2026-06-03 03:32:23'),
(4, 1, 'token', 50000, 'pending', '20260603153808_0TZTS8', NULL, 5, NULL, '2026-06-03 08:38:08', '2026-06-03 08:38:08'),
(5, 1, 'token', 50000, 'pending', '20260603164304_BXL1YF', NULL, 5, NULL, '2026-06-03 09:43:04', '2026-06-03 09:43:04'),
(6, 1, 'token', 90000, 'pending', '20260603191944_CZMTAH', NULL, 10, NULL, '2026-06-03 12:19:44', '2026-06-03 12:19:44'),
(7, 4, 'subscription', 299000, 'pending', '20260604095404_IVKA3Z', NULL, NULL, 'monthly', '2026-06-04 02:54:04', '2026-06-04 02:54:04');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('EAwxQKLcaPKX5DnrJQsXfZCLn3mnJc3ZqQ5baWic', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRVNUNmdWZGQzenB1a1pLcTZadUdoeXVDQ3B1ODdUMng4SFF1S3FwUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zIjtzOjU6InJvdXRlIjtzOjE5OiJub3RpZmljYXRpb25zLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1780544557);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `plan` enum('monthly','yearly') NOT NULL,
  `status` enum('active','expired') NOT NULL DEFAULT 'active',
  `billing_ends` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan`, `status`, `billing_ends`, `created_at`, `updated_at`) VALUES
(1, 2, 'monthly', 'active', '2026-07-03 03:17:43', '2026-06-03 03:17:43', '2026-06-03 03:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'employee',
  `about` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `resume` varchar(255) DEFAULT NULL,
  `experience_years` tinyint(3) UNSIGNED DEFAULT NULL,
  `desired_salary` int(10) UNSIGNED DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `company_size` varchar(20) DEFAULT NULL,
  `user_trial` timestamp NULL DEFAULT NULL,
  `plan` varchar(20) DEFAULT NULL,
  `billing_ends` date DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `banned_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `user_type`, `about`, `profile_pic`, `resume`, `experience_years`, `desired_salary`, `location`, `company_name`, `company_logo`, `company_website`, `company_size`, `user_trial`, `plan`, `billing_ends`, `is_admin`, `is_banned`, `banned_at`) VALUES
(1, 'Lâm Trúc', 'lamtrucnguyen08032k5@gmail.com', '2026-06-03 01:23:44', '$2y$12$ta1OLystLVmKFHxQJnBgwu64mkgn9zRpsO8s08EZqyowdfRKzVV76', NULL, '2026-06-03 01:23:44', '2026-06-03 01:23:44', 'employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(2, 'Lâm Trúc', 'lamtruc08@gmail.com', '2026-06-03 01:24:18', '$2y$12$1mxH8pQQvG.Zw6ccpRQwDOW9.SYhhBGNjEejSM6mA3zjKIBOPcLAW', NULL, '2026-06-03 01:24:18', '2026-06-03 01:24:18', 'employer', NULL, NULL, NULL, NULL, NULL, NULL, 'FPT Software', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(3, 'Lâm Trúc', 'lamtruk0803@gmail.com', '2026-06-03 01:54:19', '$2y$12$U.sMy9gziGbh6/qjKjDajueZOZ/T42lnwBwzhE.7kq.XeCwElVGie', NULL, '2026-06-03 01:54:19', '2026-06-03 01:54:19', 'employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(4, 'Lâm Trúc', '26a4042154@hvnh.edu.vn', '2026-06-03 03:29:55', '$2y$12$HJJGW0CzxPtD18fvbOnsQuUig3goNoZ3/SNmicP8pCVasec2550jS', NULL, '2026-06-03 03:29:55', '2026-06-03 03:29:55', 'employer', NULL, NULL, NULL, NULL, NULL, NULL, 'Trung Nguyên Legend', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(5, 'Lâm Trúc', 'hoangnamnd2309@gmail.com', '2026-06-03 03:49:09', '$2y$12$U2iyeyf6HoKmiy4sH/k9Iux9s/2PiTH6/2IwvepTVANUZ/Ye7U17q', NULL, '2026-06-03 03:49:09', '2026-06-03 03:49:09', 'employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(6, 'He', 'test08@gmail.com', '2026-06-03 12:11:52', '$2y$12$5YWjrBoL5LzFoP0IFc.Mf.nsQGpXKghxCHDJzHUuyvnid2mMrlcyq', NULL, '2026-06-03 12:11:52', '2026-06-03 12:11:52', 'employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(7, 'Mèo', 'meomeo03@gmail.com', '2026-06-04 03:22:04', '$2y$12$f8spHdCX/GibBuN.OVwNjuu6e0BS4GP0g1DlSD/AZAtEQ7mNsX8ha', NULL, '2026-06-04 03:22:04', '2026-06-04 03:22:04', 'employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(8, 'Trúc Test', 'test03@gmail.com', '2026-06-04 03:23:38', '$2y$12$fwDpU7vNMe2a.M1Z19iqtumt0xCEvpp0hr86AAOggWggmg/tLoa2i', NULL, '2026-06-04 03:23:38', '2026-06-04 03:23:38', 'employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(9, 'Trukki', 'trukki03@gmail.com', '2026-06-04 03:33:23', '$2y$12$zCbtYlMxzZJixzNzxvInIudoB.Ik5A8zk4Xi9.OlAR069T8CwSST2', NULL, '2026-06-04 03:33:23', '2026-06-04 03:33:23', 'employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `balance` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_tokens`
--

INSERT INTO `user_tokens` (`id`, `user_id`, `balance`, `created_at`, `updated_at`) VALUES
(1, 1, 4, '2026-06-03 01:23:44', '2026-06-04 03:20:09'),
(2, 3, 1, '2026-06-03 01:54:19', '2026-06-03 12:10:46'),
(3, 5, 5, '2026-06-03 03:49:09', '2026-06-03 03:49:09'),
(4, 6, 4, '2026-06-03 12:11:52', '2026-06-03 12:12:10'),
(5, 7, 4, '2026-06-04 03:22:04', '2026-06-04 03:22:41'),
(6, 8, 5, '2026-06-04 03:23:38', '2026-06-04 03:23:38'),
(7, 9, 5, '2026-06-04 03:33:23', '2026-06-04 03:33:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `applications_user_id_listing_id_unique` (`user_id`,`listing_id`),
  ADD KEY `applications_listing_id_foreign` (`listing_id`),
  ADD KEY `applications_cv_id_foreign` (`cv_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cvs`
--
ALTER TABLE `cvs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cvs_user_id_foreign` (`user_id`);

--
-- Indexes for table `cv_data`
--
ALTER TABLE `cv_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cv_data_user` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `listings_slug_unique` (`slug`),
  ADD KEY `listings_user_id_foreign` (`user_id`);

--
-- Indexes for table `listing_user`
--
ALTER TABLE `listing_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_user_listing_id_foreign` (`listing_id`),
  ADD KEY `listing_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_vnpay_txn_ref_unique` (`vnpay_txn_ref`),
  ADD KEY `payments_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_tokens_user_id_unique` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cvs`
--
ALTER TABLE `cvs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cv_data`
--
ALTER TABLE `cv_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `listing_user`
--
ALTER TABLE `listing_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`),
  ADD CONSTRAINT `applications_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cvs`
--
ALTER TABLE `cvs`
  ADD CONSTRAINT `cvs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cv_data`
--
ALTER TABLE `cv_data`
  ADD CONSTRAINT `cv_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listing_user`
--
ALTER TABLE `listing_user`
  ADD CONSTRAINT `listing_user_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `listing_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
