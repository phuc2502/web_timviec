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

        // ── Tạo Jobs mẫu ──────────────────────────────────────────────────
        $jobs = [
            [
                'title'       => 'Senior PHP / Laravel Developer',
                'description' => "Chúng tôi đang tìm kiếm Senior PHP Developer với kinh nghiệm tối thiểu 3 năm làm việc với Laravel Framework để tham gia nhóm phát triển sản phẩm SaaS.\n\n- Thiết kế và phát triển API RESTful với Laravel\n- Tối ưu hóa hiệu suất hệ thống và cơ sở dữ liệu\n- Code review và hướng dẫn các thành viên junior",
                'roles'       => "- Tối thiểu 3 năm kinh nghiệm PHP/Laravel\n- Thành thạo MySQL, Redis, Docker\n- Hiểu biết về Git, CI/CD",
                'predes'      => "- Lương: 25-40 triệu/tháng\n- MacBook Pro / setup tuỳ chọn\n- Remote 2 ngày/tuần",
                'salary'      => 35000000,
                'address'     => 'Hà Nội',
                'job_type'    => 'Full-time',
                'application_close_date' => now()->addDays(15),
            ],
            [
                'title'       => 'Frontend Developer (ReactJS)',
                'description' => "Tìm kiếm Frontend Developer có kinh nghiệm với ReactJS để xây dựng giao diện người dùng cho các sản phẩm web hiện đại.\n\n- Phát triển UI components với React/TypeScript\n- Tối ưu performance frontend\n- Làm việc cùng team Backend qua REST API",
                'roles'       => "- 2+ năm kinh nghiệm ReactJS\n- Thành thạo HTML/CSS/JavaScript\n- Có kinh nghiệm với Redux, Tailwind CSS",
                'predes'      => "- Lương: 20-35 triệu/tháng\n- Thưởng dự án hấp dẫn\n- Môi trường Agile/Scrum",
                'salary'      => 28000000,
                'address'     => 'Hồ Chí Minh',
                'job_type'    => 'Full-time',
                'application_close_date' => now()->addDays(20),
            ],
            [
                'title'       => 'DevOps Engineer',
                'description' => "Chúng tôi cần DevOps Engineer có kinh nghiệm quản lý hạ tầng cloud và CI/CD pipeline.\n\n- Quản lý hạ tầng AWS/GCP\n- Xây dựng và duy trì CI/CD pipeline\n- Monitoring và alerting hệ thống",
                'roles'       => "- 2+ năm kinh nghiệm DevOps\n- Thành thạo Docker, Kubernetes\n- Kinh nghiệm với AWS/GCP",
                'predes'      => "- Lương: 30-50 triệu/tháng\n- Budget học tập & chứng chỉ\n- Flexible working hours",
                'salary'      => 40000000,
                'address'     => 'Remote',
                'job_type'    => 'Remote',
                'application_close_date' => now()->addDays(25),
            ],
            [
                'title'       => 'Mobile Developer (Flutter)',
                'description' => "Tuyển Mobile Developer sử dụng Flutter để phát triển ứng dụng đa nền tảng iOS/Android.\n\n- Phát triển ứng dụng Flutter cho iOS và Android\n- Tích hợp REST API và Firebase\n- Publish lên App Store và Google Play",
                'roles'       => "- 1+ năm kinh nghiệm Flutter/Dart\n- Hiểu biết về iOS/Android native\n- Kinh nghiệm với Firebase là lợi thế",
                'predes'      => "- Lương: 18-30 triệu/tháng\n- Môi trường trẻ trung, năng động\n- Cơ hội thăng tiến nhanh",
                'salary'      => 25000000,
                'address'     => 'Đà Nẵng',
                'job_type'    => 'Full-time',
                'application_close_date' => now()->addDays(30),
            ],
        ];

        foreach ($jobs as $job) {
            Listing::firstOrCreate(
                ['slug' => Str::slug($job['title'])],
                array_merge($job, ['user_id' => $employer->id])
            );
        }

        $this->command->info('✅ Seeder xong! Tài khoản mẫu:');
        $this->command->info('   Employer : employer@demo.com / password123');
        $this->command->info('   Ứng viên : candidate@demo.com / password123');
    }
}