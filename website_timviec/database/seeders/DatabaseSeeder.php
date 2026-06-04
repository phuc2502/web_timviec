<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Listing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Tạo tài khoản Admin kiểm thử
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Quản trị viên Hệ thống',
                'password'          => bcrypt('password'),
                'user_type'         => 'admin',
                'email_verified_at' => now(),
                'about'             => 'Quản trị viên có toàn quyền kiểm soát hệ thống, phân quyền chức năng và điều phối phân quyền dữ liệu.',
            ]
        );

        // 2. Tạo tài khoản Employee (Ứng viên / Người tìm việc)
        $employee = User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name'              => 'Nguyễn Văn A',
                'password'          => bcrypt('password'),
                'user_type'         => 'employee',
                'email_verified_at' => now(),
                'about'             => 'Lập trình viên Backend với 3 năm kinh nghiệm Laravel, MySQL, Redis.',
            ]
        );

        $employee2 = User::updateOrCreate(
            ['email' => 'employee2@example.com'],
            [
                'name'              => 'Trần Thị B',
                'password'          => bcrypt('password'),
                'user_type'         => 'employee',
                'email_verified_at' => now(),
                'about'             => 'Kỹ sư cầu nối tiếng Nhật và lập trình viên Frontend ReactJS.',
            ]
        );

        // 3. Tạo tài khoản Employer (Nhà tuyển dụng / Doanh nghiệp)
        $employer = User::updateOrCreate(
            ['email' => 'employer@example.com'],
            [
                'name'              => 'ABC Tech Vietnam',
                'password'          => bcrypt('password'),
                'user_type'         => 'employer',
                'email_verified_at' => now(),
                'company_name'      => 'ABC Tech Vietnam',
                'about'             => 'Công ty công nghệ phần mềm hàng đầu Việt Nam.',
                'plan'              => 'premium',
                'billing_ends'      => now()->addDays(30),
            ]
        );

        $employer2 = User::updateOrCreate(
            ['email' => 'employer2@example.com'],
            [
                'name'              => 'DEF Software Corp',
                'password'          => bcrypt('password'),
                'user_type'         => 'employer',
                'email_verified_at' => now(),
                'company_name'      => 'DEF Software Corp',
                'about'             => 'Chúng tôi tạo nên những sản phẩm thay đổi cuộc sống.',
                'plan'              => 'trial',
                'user_trial'        => now()->addDays(7),
            ]
        );

        // 4. Tạo tin tuyển dụng (Listings)
        $job1 = Listing::updateOrCreate(
            ['slug' => 'senior-php-laravel-developer'],
            [
                'user_id'                => $employer->id,
                'title'                  => 'Senior PHP / Laravel Developer',
                'description'            => 'Chúng tôi đang tìm kiếm Senior PHP Developer tham gia phát triển sản phẩm SaaS.',
                'roles'                  => "- Tối thiểu 3 năm kinh nghiệm PHP/Laravel\n- Thành thạo MySQL, Redis, Docker",
                'predes'                 => "- Lương: 25-40 triệu/tháng\n- MacBook Pro làm việc\n- Remote 2 ngày/tuần",
                'salary'                 => 35000000,
                'address'                => 'Quận 1, TP. Hồ Chí Minh',
                'job_type'               => 'Full-time',
                'application_close_date' => now()->addDays(15),
            ]
        );

        $job2 = Listing::updateOrCreate(
            ['slug' => 'frontend-reactjs-engineer'],
            [
                'user_id'                => $employer->id,
                'title'                  => 'Frontend ReactJS Engineer',
                'description'            => 'Xây dựng giao diện Responsive, hiệu năng cao cho hệ thống của công ty.',
                'roles'                  => "- Thành thạo ReactJS, Javascript, HTML/CSS\n- Có kinh nghiệm tối ưu hóa Core Web Vitals",
                'predes'                 => "- Lương: 18-30 triệu/tháng\n- Bảo hiểm sức khỏe cao cấp",
                'salary'                 => 25000000,
                'address'                => 'Cầu Giấy, Hà Nội',
                'job_type'               => 'Full-time',
                'application_close_date' => now()->addDays(12),
            ]
        );

        $job3 = Listing::updateOrCreate(
            ['slug' => 'devops-engineer-cloud'],
            [
                'user_id'                => $employer2->id,
                'title'                  => 'DevOps Engineer (AWS/Cloud)',
                'description'            => 'Thiết lập hệ thống CI/CD, vận hành hạ tầng Cloud hiệu quả, ổn định.',
                'roles'                  => "- Có kinh nghiệm thiết lập AWS, Terraform\n- Thành thạo Docker, Kubernetes",
                'predes'                 => "- Lương thỏa thuận cực kỳ cạnh tranh\n- Thưởng dự án hấp dẫn",
                'salary'                 => 45000000,
                'address'                => 'Hải Châu, Đà Nẵng',
                'job_type'               => 'Full-time',
                'application_close_date' => now()->addDays(20),
            ]
        );

        $job4 = Listing::updateOrCreate(
            ['slug' => 'mobile-flutter-developer-intern'],
            [
                'user_id'                => $employer2->id,
                'title'                  => 'Thực tập sinh Mobile Developer (Flutter)',
                'description'            => 'Tham gia phát triển app di động đa nền tảng Flutter dưới sự hướng dẫn của các Senior.',
                'roles'                  => "- Biết lập trình Dart/Flutter căn bản\n- Nhiệt huyết, ham học hỏi",
                'predes'                 => "- Trợ cấp thực tập hấp dẫn\n- Cơ hội lên chính thức sau 3 tháng",
                'salary'                 => 6000000,
                'address'                => 'Quận 3, TP. Hồ Chí Minh',
                'job_type'               => 'Internship',
                'application_close_date' => now()->addDays(5),
            ]
        );

        // 5. Tạo đơn ứng tuyển (listing_user)
        // Ứng viên employee ứng tuyển job1 và job3
        $employee->appliedListings()->sync([
            $job1->id => ['shortlisted' => true, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            $job3->id => ['shortlisted' => false, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()]
        ]);

        // Ứng viên employee2 ứng tuyển job2 và job3
        $employee2->appliedListings()->sync([
            $job2->id => ['shortlisted' => false, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            $job3->id => ['shortlisted' => true, 'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)]
        ]);
    }
}
