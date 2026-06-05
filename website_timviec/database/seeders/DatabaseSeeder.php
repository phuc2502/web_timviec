<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tạo Employer mẫu ──────────────────────────────────────────────
        $employer = User::firstOrCreate(
            ['email' => 'employer@demo.com'],
            [
                'name'               => 'ABC Tech Vietnam',
                'password'           => Hash::make('password123'),
                'user_type'          => 'employer',
                'company_name'       => 'ABC Tech Vietnam',
                'about'              => 'Công ty công nghệ hàng đầu Việt Nam',
                'email_verified_at'  => now(),
            ]
        );

        // ── Tạo Employer 2 ────────────────────────────────────────────────
        $employer2 = User::firstOrCreate(
            ['email' => 'employer2@demo.com'],
            [
                'name'               => 'FPT Software',
                'password'           => Hash::make('password123'),
                'user_type'          => 'employer',
                'company_name'       => 'FPT Software',
                'about'              => 'Tập đoàn FPT - Phần mềm hàng đầu Việt Nam',
                'email_verified_at'  => now(),
            ]
        );

        // ── Tạo Ứng viên mẫu ──────────────────────────────────────────────
        $candidate = User::firstOrCreate(
            ['email' => 'candidate@demo.com'],
            [
                'name'              => 'Nguyễn Văn Demo',
                'password'          => Hash::make('password123'),
                'user_type'         => 'employee',
                'about'             => 'Lập trình viên Backend 3 năm kinh nghiệm Laravel',
                'email_verified_at' => now(),
            ]
        );

        // ── Tạo Jobs mẫu (đầy đủ các trường tìm kiếm & lọc) ─────────────
        $jobs = [
            [
                'title'       => 'Senior PHP / Laravel Developer',
                'description' => "Chúng tôi đang tìm kiếm Senior PHP Developer với kinh nghiệm tối thiểu 3 năm làm việc với Laravel Framework để tham gia nhóm phát triển sản phẩm SaaS.\n\n- Thiết kế và phát triển API RESTful với Laravel\n- Tối ưu hóa hiệu suất hệ thống và cơ sở dữ liệu\n- Code review và hướng dẫn các thành viên junior",
                'requirements' => "- Tối thiểu 3 năm kinh nghiệm PHP/Laravel\n- Thành thạo MySQL, Redis, Docker\n- Hiểu biết về Git, CI/CD",
                'benefits'     => "- Lương: 25-40 triệu/tháng\n- MacBook Pro / setup tuỳ chọn\n- Remote 2 ngày/tuần",
                'salary'      => 35000000,
                'address'     => 'Hà Nội',
                'job_type'    => 'Full-time',
                'work_mode'   => 'hybrid',
                'job_level'   => 'senior',
                'experience_years_min' => 3,
                'experience_years_max' => 7,
                'application_close_date' => now()->addDays(15),
                'user_id'     => $employer->id,
            ],
            [
                'title'       => 'Frontend Developer (ReactJS)',
                'description' => "Tìm kiếm Frontend Developer có kinh nghiệm với ReactJS để xây dựng giao diện người dùng cho các sản phẩm web hiện đại.\n\n- Phát triển UI components với React/TypeScript\n- Tối ưu performance frontend\n- Làm việc cùng team Backend qua REST API",
                'requirements' => "- 2+ năm kinh nghiệm ReactJS\n- Thành thạo HTML/CSS/JavaScript\n- Có kinh nghiệm với Redux, Tailwind CSS",
                'benefits'     => "- Lương: 20-35 triệu/tháng\n- Thưởng dự án hấp dẫn\n- Môi trường Agile/Scrum",
                'salary'      => 28000000,
                'address'     => 'Hồ Chí Minh',
                'job_type'    => 'Full-time',
                'work_mode'   => 'onsite',
                'job_level'   => 'middle',
                'experience_years_min' => 2,
                'experience_years_max' => 4,
                'application_close_date' => now()->addDays(20),
                'user_id'     => $employer->id,
            ],
            [
                'title'       => 'DevOps Engineer',
                'description' => "Chúng tôi cần DevOps Engineer có kinh nghiệm quản lý hạ tầng cloud và CI/CD pipeline.\n\n- Quản lý hạ tầng AWS/GCP\n- Xây dựng và duy trì CI/CD pipeline\n- Monitoring và alerting hệ thống",
                'requirements' => "- 2+ năm kinh nghiệm DevOps\n- Thành thạo Docker, Kubernetes\n- Kinh nghiệm với AWS/GCP",
                'benefits'     => "- Lương: 30-50 triệu/tháng\n- Budget học tập & chứng chỉ\n- Flexible working hours",
                'salary'      => 40000000,
                'address'     => 'Remote',
                'job_type'    => 'Remote',
                'work_mode'   => 'remote',
                'job_level'   => 'senior',
                'experience_years_min' => 2,
                'experience_years_max' => 5,
                'application_close_date' => now()->addDays(25),
                'user_id'     => $employer->id,
            ],
            [
                'title'       => 'Mobile Developer (Flutter)',
                'description' => "Tuyển Mobile Developer sử dụng Flutter để phát triển ứng dụng đa nền tảng iOS/Android.\n\n- Phát triển ứng dụng Flutter cho iOS và Android\n- Tích hợp REST API và Firebase\n- Publish lên App Store và Google Play",
                'requirements' => "- 1+ năm kinh nghiệm Flutter/Dart\n- Hiểu biết về iOS/Android native\n- Kinh nghiệm với Firebase là lợi thế",
                'benefits'     => "- Lương: 18-30 triệu/tháng\n- Môi trường trẻ trung, năng động\n- Cơ hội thăng tiến nhanh",
                'salary'      => 25000000,
                'address'     => 'Đà Nẵng',
                'job_type'    => 'Full-time',
                'work_mode'   => 'onsite',
                'job_level'   => 'junior',
                'experience_years_min' => 1,
                'experience_years_max' => 3,
                'application_close_date' => now()->addDays(30),
                'user_id'     => $employer->id,
            ],
            [
                'title'       => 'Data Engineer / Python',
                'description' => "Vị trí Data Engineer tại FPT Software. Bạn sẽ xây dựng data pipeline, ETL và hệ thống phân tích dữ liệu lớn.\n\n- Thiết kế và xây dựng data pipeline\n- Làm việc với Big Data: Spark, Hadoop\n- Tích hợp ML models vào hệ thống",
                'requirements' => "- 1-3 năm kinh nghiệm Data Engineering\n- Thành thạo Python, SQL\n- Kinh nghiệm với Airflow, Spark là lợi thế",
                'benefits'     => "- Lương: 20-40 triệu/tháng\n- Bảo hiểm sức khỏe cao cấp\n- Training & certification budget",
                'salary'      => 30000000,
                'address'     => 'Hà Nội',
                'job_type'    => 'Full-time',
                'work_mode'   => 'hybrid',
                'job_level'   => 'junior',
                'experience_years_min' => 1,
                'experience_years_max' => 3,
                'application_close_date' => now()->addDays(18),
                'user_id'     => $employer2->id,
            ],
            [
                'title'       => 'Backend Developer (Node.js)',
                'description' => "Tuyển Backend Developer Node.js cho hệ thống microservices quy mô lớn tại FPT Software.\n\n- Phát triển và maintain các microservices\n- Làm việc với message queue (Kafka, RabbitMQ)\n- RESTful API và GraphQL",
                'requirements' => "- Thành thạo Node.js, Express/NestJS\n- Kinh nghiệm với MongoDB, PostgreSQL\n- Hiểu biết về microservices architecture",
                'benefits'     => "- Lương: 25-45 triệu/tháng\n- Stock options\n- Làm việc Agile/Scrum",
                'salary'      => 35000000,
                'address'     => 'Hồ Chí Minh',
                'job_type'    => 'Full-time',
                'work_mode'   => 'remote',
                'job_level'   => 'middle',
                'experience_years_min' => 2,
                'experience_years_max' => 5,
                'application_close_date' => now()->addDays(22),
                'user_id'     => $employer2->id,
            ],
            [
                'title'       => 'UI/UX Designer',
                'description' => "Tìm kiếm UI/UX Designer sáng tạo, đam mê thiết kế sản phẩm số tại ABC Tech Vietnam.\n\n- Thiết kế UI cho web và mobile app\n- User research và usability testing\n- Tạo design system và prototype",
                'requirements' => "- 1+ năm kinh nghiệm UI/UX\n- Thành thạo Figma, Adobe XD\n- Portfolio ấn tượng",
                'benefits'     => "- Lương: 15-25 triệu/tháng\n- Creative freedom\n- MacBook & tool license",
                'salary'      => 20000000,
                'address'     => 'Hà Nội',
                'job_type'    => 'Full-time',
                'work_mode'   => 'hybrid',
                'job_level'   => 'junior',
                'experience_years_min' => 1,
                'experience_years_max' => 3,
                'application_close_date' => now()->addDays(28),
                'user_id'     => $employer->id,
            ],
            [
                'title'       => 'Thực tập sinh Lập trình viên Java',
                'description' => "Cơ hội thực tập tuyệt vời cho sinh viên CNTT năm 3-4 hoặc mới ra trường. Được đào tạo bài bản về Java Spring Boot.\n\n- Học và phát triển kỹ năng Java/Spring Boot\n- Tham gia dự án thực tế\n- Mentoring 1-1 với senior developer",
                'requirements' => "- Sinh viên năm 3-4 ngành CNTT\n- Có kiến thức cơ bản về Java/OOP\n- Ham học hỏi, chăm chỉ",
                'benefits'     => "- Hỗ trợ: 3-5 triệu/tháng\n- Cơ hội chuyển chính thức\n- Certificate sau khi hoàn thành",
                'salary'      => 4000000,
                'address'     => 'Hà Nội',
                'job_type'    => 'Internship',
                'work_mode'   => 'onsite',
                'job_level'   => 'intern',
                'experience_years_min' => 0,
                'experience_years_max' => 1,
                'application_close_date' => now()->addDays(35),
                'user_id'     => $employer2->id,
            ],
            [
                'title'       => 'QA Engineer / Tester',
                'description' => "Tuyển QA Engineer cho dự án fintech. Đảm bảo chất lượng sản phẩm phần mềm trước khi release.\n\n- Viết và thực thi test cases\n- Automation testing với Selenium/Cypress\n- Bug tracking và reporting",
                'requirements' => "- 2+ năm kinh nghiệm QA/Testing\n- Biết automation testing\n- Kinh nghiệm Agile/Scrum",
                'benefits'     => "- Lương: 18-30 triệu/tháng\n- Flexible remote 2 ngày/tuần\n- Học tập & phát triển",
                'salary'      => 22000000,
                'address'     => 'Hồ Chí Minh',
                'job_type'    => 'Full-time',
                'work_mode'   => 'hybrid',
                'job_level'   => 'middle',
                'experience_years_min' => 2,
                'experience_years_max' => 4,
                'application_close_date' => now()->addDays(20),
                'user_id'     => $employer->id,
            ],
            [
                'title'       => 'Blockchain Developer (Solidity)',
                'description' => "Phát triển smart contracts và DApps trên nền tảng Ethereum. Cơ hội làm việc trong lĩnh vực Web3 tiên tiến.\n\n- Phát triển smart contracts với Solidity\n- Xây dựng DApps tích hợp Web3.js/Ethers.js\n- Security audit smart contracts",
                'requirements' => "- Kinh nghiệm với Solidity/EVM\n- Hiểu biết về DeFi protocols\n- Biết React/TypeScript là lợi thế",
                'benefits'     => "- Lương: Thỏa thuận (rất cạnh tranh)\n- Token allocation\n- Remote 100%",
                'salary'      => 0,
                'address'     => 'Remote',
                'job_type'    => 'Freelance',
                'work_mode'   => 'remote',
                'job_level'   => 'senior',
                'experience_years_min' => 2,
                'experience_years_max' => null,
                'application_close_date' => now()->addDays(45),
                'user_id'     => $employer2->id,
            ],
        ];

        foreach ($jobs as $job) {
            Listing::firstOrCreate(
                ['slug' => Str::slug($job['title'])],
                array_merge($job, [
                    'slug' => Str::slug($job['title']) . '-' . Str::random(4),
                ])
            );
        }

        $this->command->info('✅ Seeder xong! Tài khoản mẫu:');
        $this->command->info('   Employer  : employer@demo.com  / password123 (ABC Tech Vietnam)');
        $this->command->info('   Employer 2: employer2@demo.com / password123 (FPT Software)');
        $this->command->info('   Ứng viên  : candidate@demo.com / password123');
    }
}