<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DatabaseSeeder — Dữ liệu mẫu phong phú cho module Job Search Filter.
 *
 * Tạo:
 *   - 25 kỹ năng IT phổ biến
 *   - 8 employer (các công ty đa dạng quy mô, địa điểm)
 *   - 60+ tin tuyển dụng đa dạng (job_type, work_mode, salary, exp, job_level)
 *
 * Chạy: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------
        // 1. Skills IT phổ biến
        // ---------------------------------------------------------------
        $skillData = [
            'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'ReactJS',
            'VueJS', 'NodeJS', 'Python', 'Django', 'Java',
            'Spring Boot', 'MySQL', 'PostgreSQL', 'MongoDB', 'Redis',
            'Docker', 'Kubernetes', 'AWS', 'Git', 'Linux',
            'Flutter', 'Swift', 'Kotlin', 'Go', 'Rust',
        ];

        $skills = collect($skillData)->mapWithKeys(function (string $name) {
            $skill = Skill::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
            return [$name => $skill];
        });

        // ---------------------------------------------------------------
        // 2. Employers — đa dạng quy mô & thành phố
        // ---------------------------------------------------------------
        $employers = [
            [
                'name'         => 'Nguyen Van Admin',
                'email'        => 'admin@fpt.com',
                'company_name' => 'FPT Software',
                'company_size' => '500+',
                'address_hint' => 'Hà Nội',
            ],
            [
                'name'         => 'Tran Thi B',
                'email'        => 'hr@topdev.com',
                'company_name' => 'TopDev Vietnam',
                'company_size' => '200-499',
                'address_hint' => 'TP.HCM',
            ],
            [
                'name'         => 'Le Van C',
                'email'        => 'hr@vng.com',
                'company_name' => 'VNG Corporation',
                'company_size' => '500+',
                'address_hint' => 'TP.HCM',
            ],
            [
                'name'         => 'Pham Thi D',
                'email'        => 'hr@nashtech.com',
                'company_name' => 'NashTech Global',
                'company_size' => '200-499',
                'address_hint' => 'Đà Nẵng',
            ],
            [
                'name'         => 'Hoang Van E',
                'email'        => 'hr@startup.com',
                'company_name' => 'TechStartup JSC',
                'company_size' => '10-49',
                'address_hint' => 'Hà Nội',
            ],
            [
                'name'         => 'Vo Thi F',
                'email'        => 'hr@smallshop.com',
                'company_name' => 'CodeLab Studio',
                'company_size' => '1-9',
                'address_hint' => 'Cần Thơ',
            ],
            [
                'name'         => 'Dang Van G',
                'email'        => 'hr@viettel.com',
                'company_name' => 'Viettel Cyber Security',
                'company_size' => '50-199',
                'address_hint' => 'Hà Nội',
            ],
            [
                'name'         => 'Bui Thi H',
                'email'        => 'hr@got-it.com',
                'company_name' => 'Got It Inc.',
                'company_size' => '50-199',
                'address_hint' => 'TP.HCM',
            ],
        ];

        $employerModels = [];
        foreach ($employers as $e) {
            $user = User::firstOrCreate(
                ['email' => $e['email']],
                [
                    'name'         => $e['name'],
                    'password'     => Hash::make('password'),
                    'user_type'    => 'employer',
                    'company_name' => $e['company_name'],
                    'company_logo' => null,
                    'company_size' => $e['company_size'],
                ]
            );
            $employerModels[] = ['user' => $user, 'address_hint' => $e['address_hint']];
        }

        // ---------------------------------------------------------------
        // 3. Listings — 60+ bản ghi đa dạng
        // ---------------------------------------------------------------
        $listingsData = [
            // ---- FPT Software (Hà Nội, 500+) ----
            [
                'title'       => 'Senior PHP Developer (Laravel)',
                'predes'      => 'FPT Software tuyển PHP Developer có kinh nghiệm Laravel, RESTful API và microservices. Môi trường làm việc chuyên nghiệp, lương cạnh tranh.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 25000000,
                'exp_min'     => 3,
                'exp_max'     => 7,
                'job_level'   => 'senior',
                'address'     => 'Tòa nhà FPT, 17 Duy Tân, Cầu Giấy, Hà Nội',
                'skills'      => ['PHP', 'Laravel', 'MySQL', 'Docker', 'Git'],
                'close_days'  => 30,
                'employer_idx' => 0,
            ],
            [
                'title'       => 'Junior ReactJS Developer',
                'predes'      => 'Tuyển dụng lập trình viên ReactJS mới tốt nghiệp, có kiến thức về hooks, Redux và REST API. Được đào tạo thêm về TypeScript.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 10000000,
                'exp_min'     => 0,
                'exp_max'     => 1,
                'job_level'   => 'junior',
                'address'     => 'Tòa nhà FPT, 17 Duy Tân, Cầu Giấy, Hà Nội',
                'skills'      => ['JavaScript', 'ReactJS', 'TypeScript', 'Git'],
                'close_days'  => 45,
                'employer_idx' => 0,
            ],
            [
                'title'       => 'DevOps Engineer',
                'predes'      => 'Vị trí DevOps tại FPT Software, phụ trách CI/CD pipeline, quản lý hạ tầng AWS và container hóa ứng dụng với Docker/Kubernetes.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 35000000,
                'exp_min'     => 3,
                'exp_max'     => 6,
                'job_level'   => 'middle',
                'address'     => 'Tòa nhà FPT, 17 Duy Tân, Cầu Giấy, Hà Nội',
                'skills'      => ['Docker', 'Kubernetes', 'AWS', 'Linux', 'Git'],
                'close_days'  => 20,
                'employer_idx' => 0,
            ],
            [
                'title'       => 'Technical Lead - Java Spring Boot',
                'predes'      => 'Technical Lead cho team backend Java tại FPT, chịu trách nhiệm kiến trúc hệ thống, review code và mentoring junior.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 50000000,
                'exp_min'     => 5,
                'exp_max'     => 10,
                'job_level'   => 'lead',
                'address'     => '17 Duy Tân, Cầu Giấy, Hà Nội',
                'skills'      => ['Java', 'Spring Boot', 'MySQL', 'Docker', 'AWS'],
                'close_days'  => 25,
                'employer_idx' => 0,
            ],

            // ---- TopDev (TP.HCM, 200-499) ----
            [
                'title'       => 'Backend Developer Python/Django',
                'predes'      => 'TopDev tuyển Backend Developer Python với kinh nghiệm Django, PostgreSQL. Ưu tiên ứng viên có kinh nghiệm với Redis caching và Celery.',
                'job_type'    => 'full-time',
                'work_mode'   => 'remote',
                'salary'      => 20000000,
                'exp_min'     => 1,
                'exp_max'     => 3,
                'job_level'   => 'junior',
                'address'     => '123 Nguyễn Đình Chiểu, Quận 3, TP.HCM',
                'skills'      => ['Python', 'Django', 'PostgreSQL', 'Redis', 'Docker'],
                'close_days'  => 35,
                'employer_idx' => 1,
            ],
            [
                'title'       => 'Fullstack Developer NodeJS + VueJS',
                'predes'      => 'Tuyển Fullstack Developer thành thạo NodeJS (Express/Fastify) và VueJS 3. Tham gia xây dựng nền tảng tuyển dụng next-gen.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 18000000,
                'exp_min'     => 1,
                'exp_max'     => 4,
                'job_level'   => 'middle',
                'address'     => '123 Nguyễn Đình Chiểu, Quận 3, TP.HCM',
                'skills'      => ['NodeJS', 'VueJS', 'JavaScript', 'MongoDB', 'Git'],
                'close_days'  => 40,
                'employer_idx' => 1,
            ],
            [
                'title'       => 'Data Engineer (Part-time)',
                'predes'      => 'Tuyển Data Engineer bán thời gian, xây dựng data pipeline và ETL processes. Kinh nghiệm Python và SQL là bắt buộc.',
                'job_type'    => 'part-time',
                'work_mode'   => 'remote',
                'salary'      => 8000000,
                'exp_min'     => 1,
                'exp_max'     => 3,
                'job_level'   => 'junior',
                'address'     => 'Remote - TP.HCM',
                'skills'      => ['Python', 'PostgreSQL', 'AWS', 'Git'],
                'close_days'  => 15,
                'employer_idx' => 1,
            ],
            [
                'title'       => 'Engineering Manager',
                'predes'      => 'Engineering Manager cho TopDev Platform, quản lý team 15+ engineer, define technical roadmap và đảm bảo delivery đúng hạn.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 80000000,
                'exp_min'     => 7,
                'exp_max'     => null,
                'job_level'   => 'manager',
                'address'     => '123 Nguyễn Đình Chiểu, Quận 3, TP.HCM',
                'skills'      => ['Java', 'MySQL', 'AWS', 'Docker', 'Git'],
                'close_days'  => 50,
                'employer_idx' => 1,
            ],

            // ---- VNG Corporation (TP.HCM, 500+) ----
            [
                'title'       => 'Senior Go Developer - ZaloPay',
                'predes'      => 'VNG tuyển Senior Go Developer cho team ZaloPay, phát triển core payment engine, high-throughput systems. Yêu cầu kinh nghiệm distributed systems.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 45000000,
                'exp_min'     => 4,
                'exp_max'     => 8,
                'job_level'   => 'senior',
                'address'     => 'VNG Campus, Quận 12, TP.HCM',
                'skills'      => ['Go', 'MySQL', 'Redis', 'Kubernetes', 'Linux'],
                'close_days'  => 30,
                'employer_idx' => 2,
            ],
            [
                'title'       => 'iOS Developer (Swift)',
                'predes'      => 'Tuyển iOS Developer cho app Zalo, phát triển tính năng chat, media và security. Kinh nghiệm với Swift, UIKit/SwiftUI là bắt buộc.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 30000000,
                'exp_min'     => 2,
                'exp_max'     => 5,
                'job_level'   => 'middle',
                'address'     => 'VNG Campus, Quận 12, TP.HCM',
                'skills'      => ['Swift', 'Git', 'Linux'],
                'close_days'  => 20,
                'employer_idx' => 2,
            ],
            [
                'title'       => 'Android Developer (Kotlin)',
                'predes'      => 'Tuyển Android Developer Kotlin cho Zalo Android, phát triển UI components và tối ưu performance cho hàng triệu người dùng.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 28000000,
                'exp_min'     => 2,
                'exp_max'     => 5,
                'job_level'   => 'middle',
                'address'     => 'VNG Campus, Quận 12, TP.HCM',
                'skills'      => ['Kotlin', 'Git', 'Java'],
                'close_days'  => 25,
                'employer_idx' => 2,
            ],
            [
                'title'       => 'Fresher Backend Java',
                'predes'      => 'Cơ hội cho sinh viên mới ra trường hoặc có dưới 6 tháng kinh nghiệm. VNG đào tạo toàn diện về Java Spring Boot và microservices.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 8000000,
                'exp_min'     => 0,
                'exp_max'     => 1,
                'job_level'   => 'fresher',
                'address'     => 'VNG Campus, Quận 12, TP.HCM',
                'skills'      => ['Java', 'MySQL', 'Git'],
                'close_days'  => 60,
                'employer_idx' => 2,
            ],
            [
                'title'       => 'Security Engineer',
                'predes'      => 'VNG tuyển Security Engineer, phụ trách pentest, vulnerability assessment và bảo vệ hệ thống payment. Kinh nghiệm với Linux và security tools.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 40000000,
                'exp_min'     => 3,
                'exp_max'     => 6,
                'job_level'   => 'senior',
                'address'     => 'VNG Campus, Quận 12, TP.HCM',
                'skills'      => ['Linux', 'Python', 'AWS', 'Docker'],
                'close_days'  => 15,
                'employer_idx' => 2,
            ],

            // ---- NashTech (Đà Nẵng, 200-499) ----
            [
                'title'       => 'PHP Developer (Remote)',
                'predes'      => 'NashTech Global tuyển PHP Developer làm remote, phát triển các giải pháp e-commerce cho khách hàng UK/Australia. Yêu cầu tiếng Anh tốt.',
                'job_type'    => 'full-time',
                'work_mode'   => 'remote',
                'salary'      => 22000000,
                'exp_min'     => 2,
                'exp_max'     => 5,
                'job_level'   => 'middle',
                'address'     => 'Toà nhà Đà Nẵng IT Park, Đà Nẵng',
                'skills'      => ['PHP', 'Laravel', 'MySQL', 'Git', 'Docker'],
                'close_days'  => 30,
                'employer_idx' => 3,
            ],
            [
                'title'       => '.NET Developer',
                'predes'      => 'Tuyển .NET Developer C# cho dự án fintech, kinh nghiệm với ASP.NET Core, SQL Server và Azure là lợi thế.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 20000000,
                'exp_min'     => 1,
                'exp_max'     => 4,
                'job_level'   => 'junior',
                'address'     => 'Toà nhà Đà Nẵng IT Park, Đà Nẵng',
                'skills'      => ['Git', 'Docker', 'PostgreSQL'],
                'close_days'  => 35,
                'employer_idx' => 3,
            ],
            [
                'title'       => 'QA Engineer - Automation',
                'predes'      => 'NashTech tuyển QA Automation Engineer, kinh nghiệm Selenium/Playwright, thiết kế test plan và báo cáo bug chuyên nghiệp.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 15000000,
                'exp_min'     => 1,
                'exp_max'     => 3,
                'job_level'   => 'junior',
                'address'     => 'Toà nhà Đà Nẵng IT Park, Đà Nẵng',
                'skills'      => ['Python', 'Git', 'Linux'],
                'close_days'  => 20,
                'employer_idx' => 3,
            ],
            [
                'title'       => 'Senior Frontend TypeScript/React',
                'predes'      => 'Senior Frontend Developer thành thạo React, TypeScript và performance optimization. Tham gia dự án SaaS quốc tế cho khách hàng Mỹ/Anh.',
                'job_type'    => 'full-time',
                'work_mode'   => 'remote',
                'salary'      => 35000000,
                'exp_min'     => 3,
                'exp_max'     => 7,
                'job_level'   => 'senior',
                'address'     => 'Remote - Đà Nẵng',
                'skills'      => ['TypeScript', 'ReactJS', 'JavaScript', 'Git'],
                'close_days'  => 45,
                'employer_idx' => 3,
            ],

            // ---- TechStartup JSC (Hà Nội, 10-49) ----
            [
                'title'       => 'Full-stack Developer React + Laravel',
                'predes'      => 'Startup công nghệ tìm kiếm Fullstack Developer đam mê xây dựng sản phẩm từ đầu. Môi trường năng động, equity package hấp dẫn.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 15000000,
                'exp_min'     => 1,
                'exp_max'     => 3,
                'job_level'   => 'junior',
                'address'     => '15 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội',
                'skills'      => ['PHP', 'Laravel', 'ReactJS', 'MySQL', 'Git'],
                'close_days'  => 30,
                'employer_idx' => 4,
            ],
            [
                'title'       => 'Mobile Developer Flutter',
                'predes'      => 'Tuyển Flutter Developer xây dựng mobile app cho cả iOS và Android. Kinh nghiệm với state management (BLoC/Riverpod) là lợi thế.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 18000000,
                'exp_min'     => 1,
                'exp_max'     => 3,
                'job_level'   => 'junior',
                'address'     => '15 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội',
                'skills'      => ['Flutter', 'Dart', 'Git'],
                'close_days'  => 25,
                'employer_idx' => 4,
            ],
            [
                'title'       => 'CTO / Technical Co-founder',
                'predes'      => 'Startup EdTech tìm kiếm CTO/Co-founder kỹ thuật, định hướng kiến trúc hệ thống và xây dựng team engineering. Equity 5-10%.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 0,
                'exp_min'     => 7,
                'exp_max'     => null,
                'job_level'   => 'manager',
                'address'     => '15 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội',
                'skills'      => ['AWS', 'Docker', 'Kubernetes', 'MySQL', 'NodeJS'],
                'close_days'  => 60,
                'employer_idx' => 4,
            ],

            // ---- CodeLab Studio (Cần Thơ, 1-9) ----
            [
                'title'       => 'WordPress Developer (Freelance)',
                'predes'      => 'CodeLab Studio tuyển WordPress Developer tự do, nhận dự án website doanh nghiệp vừa và nhỏ tại ĐBSCL. Thanh toán theo dự án.',
                'job_type'    => 'freelance',
                'work_mode'   => 'remote',
                'salary'      => 5000000,
                'exp_min'     => 0,
                'exp_max'     => 2,
                'job_level'   => 'fresher',
                'address'     => 'Ninh Kiều, Cần Thơ',
                'skills'      => ['PHP', 'JavaScript', 'MySQL', 'Git'],
                'close_days'  => 20,
                'employer_idx' => 5,
            ],
            [
                'title'       => 'Intern Web Developer',
                'predes'      => 'Thực tập sinh web developer tại CodeLab Studio, học thực tế về HTML/CSS/JS và PHP cơ bản. Phụ cấp hàng tháng.',
                'job_type'    => 'internship',
                'work_mode'   => 'onsite',
                'salary'      => 2000000,
                'exp_min'     => 0,
                'exp_max'     => 0,
                'job_level'   => 'intern',
                'address'     => 'Ninh Kiều, Cần Thơ',
                'skills'      => ['PHP', 'JavaScript', 'MySQL'],
                'close_days'  => 30,
                'employer_idx' => 5,
            ],
            [
                'title'       => 'PHP Developer (Part-time, Remote)',
                'predes'      => 'CodeLab tuyển PHP Developer part-time làm remote, hỗ trợ maintain và phát triển tính năng cho các dự án web đang vận hành.',
                'job_type'    => 'part-time',
                'work_mode'   => 'remote',
                'salary'      => 6000000,
                'exp_min'     => 0,
                'exp_max'     => 2,
                'job_level'   => 'fresher',
                'address'     => 'Remote - Cần Thơ',
                'skills'      => ['PHP', 'MySQL', 'Git'],
                'close_days'  => 15,
                'employer_idx' => 5,
            ],

            // ---- Viettel Cyber Security (Hà Nội, 50-199) ----
            [
                'title'       => 'Penetration Tester',
                'predes'      => 'Viettel Cyber Security tuyển Pentester chuyên nghiệp, thực hiện đánh giá bảo mật cho hệ thống của Viettel Group và khách hàng doanh nghiệp.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 30000000,
                'exp_min'     => 2,
                'exp_max'     => 5,
                'job_level'   => 'middle',
                'address'     => 'Trụ sở Viettel, Cầu Giấy, Hà Nội',
                'skills'      => ['Linux', 'Python', 'Git'],
                'close_days'  => 20,
                'employer_idx' => 6,
            ],
            [
                'title'       => 'Malware Analyst / Reverse Engineer',
                'predes'      => 'Tuyển Malware Analyst có kinh nghiệm phân tích mã độc, reverse engineering và threat intelligence. Làm việc trong môi trường high security.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 35000000,
                'exp_min'     => 3,
                'exp_max'     => 7,
                'job_level'   => 'senior',
                'address'     => 'Trụ sở Viettel, Cầu Giấy, Hà Nội',
                'skills'      => ['Python', 'Linux', 'Git'],
                'close_days'  => 25,
                'employer_idx' => 6,
            ],
            [
                'title'       => 'Security Intern - SOC',
                'predes'      => 'Thực tập tại Security Operations Center (SOC) của Viettel, học về SIEM, log analysis và incident response thực tế.',
                'job_type'    => 'internship',
                'work_mode'   => 'onsite',
                'salary'      => 3000000,
                'exp_min'     => 0,
                'exp_max'     => 0,
                'job_level'   => 'intern',
                'address'     => 'Trụ sở Viettel, Cầu Giấy, Hà Nội',
                'skills'      => ['Linux', 'Python'],
                'close_days'  => 30,
                'employer_idx' => 6,
            ],
            [
                'title'       => 'Cloud Security Architect',
                'predes'      => 'Vị trí Cloud Security Architect cấp cao, thiết kế security framework cho hạ tầng cloud multi-tenant, đảm bảo tuân thủ ISO 27001.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 60000000,
                'exp_min'     => 7,
                'exp_max'     => null,
                'job_level'   => 'lead',
                'address'     => 'Trụ sở Viettel, Cầu Giấy, Hà Nội',
                'skills'      => ['AWS', 'Kubernetes', 'Linux', 'Docker', 'Python'],
                'close_days'  => 30,
                'employer_idx' => 6,
            ],

            // ---- Got It Inc. (TP.HCM, 50-199) ----
            [
                'title'       => 'AI/ML Engineer - Python',
                'predes'      => 'Got It tuyển ML Engineer xây dựng mô hình NLP và Computer Vision cho nền tảng Q&A. Kinh nghiệm với TensorFlow/PyTorch và deploy model là bắt buộc.',
                'job_type'    => 'full-time',
                'work_mode'   => 'remote',
                'salary'      => 40000000,
                'exp_min'     => 2,
                'exp_max'     => 5,
                'job_level'   => 'middle',
                'address'     => '220 Hoàng Diệu 2, Thủ Đức, TP.HCM',
                'skills'      => ['Python', 'AWS', 'Docker', 'Git', 'Linux'],
                'close_days'  => 40,
                'employer_idx' => 7,
            ],
            [
                'title'       => 'Backend Engineer - NodeJS',
                'predes'      => 'Got It cần Backend Engineer NodeJS cho platform giáo dục, xây dựng microservices, GraphQL APIs và real-time features với WebSocket.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 25000000,
                'exp_min'     => 2,
                'exp_max'     => 4,
                'job_level'   => 'middle',
                'address'     => '220 Hoàng Diệu 2, Thủ Đức, TP.HCM',
                'skills'      => ['NodeJS', 'MongoDB', 'Redis', 'Docker', 'AWS'],
                'close_days'  => 35,
                'employer_idx' => 7,
            ],
            [
                'title'       => 'Frontend Developer VueJS',
                'predes'      => 'Tuyển Frontend VueJS 3 developer, xây dựng interactive UI cho nền tảng học tập online. Yêu cầu kinh nghiệm Vuex/Pinia và Tailwind CSS.',
                'job_type'    => 'full-time',
                'work_mode'   => 'remote',
                'salary'      => 18000000,
                'exp_min'     => 1,
                'exp_max'     => 3,
                'job_level'   => 'junior',
                'address'     => 'Remote - TP.HCM',
                'skills'      => ['VueJS', 'TypeScript', 'JavaScript', 'Git'],
                'close_days'  => 30,
                'employer_idx' => 7,
            ],
            [
                'title'       => 'Senior Data Engineer',
                'predes'      => 'Got It tuyển Senior Data Engineer xây dựng data warehouse, ETL pipelines và BI dashboards. Stack: Airflow, dbt, BigQuery, Python.',
                'job_type'    => 'full-time',
                'work_mode'   => 'remote',
                'salary'      => 45000000,
                'exp_min'     => 4,
                'exp_max'     => 8,
                'job_level'   => 'senior',
                'address'     => 'Remote - Toàn quốc',
                'skills'      => ['Python', 'PostgreSQL', 'AWS', 'Docker', 'Git'],
                'close_days'  => 45,
                'employer_idx' => 7,
            ],
            // ---- Thêm một số tin thỏa thuận / diverse ----
            [
                'title'       => 'Rust Developer - Blockchain',
                'predes'      => 'Startup blockchain tìm kiếm Rust Developer có đam mê với decentralized systems, smart contracts và WebAssembly.',
                'job_type'    => 'full-time',
                'work_mode'   => 'remote',
                'salary'      => 0,
                'exp_min'     => 2,
                'exp_max'     => null,
                'job_level'   => 'middle',
                'address'     => 'Remote - Toàn quốc',
                'skills'      => ['Rust', 'Git', 'Linux'],
                'close_days'  => 20,
                'employer_idx' => 4,
            ],
            [
                'title'       => 'Laravel + Vue Freelancer',
                'predes'      => 'Nhận dự án freelance Laravel + VueJS theo yêu cầu. Phù hợp developer muốn làm thêm ngoài giờ hành chính.',
                'job_type'    => 'freelance',
                'work_mode'   => 'remote',
                'salary'      => 0,
                'exp_min'     => 1,
                'exp_max'     => 5,
                'job_level'   => null,
                'address'     => 'Remote - Toàn quốc',
                'skills'      => ['PHP', 'Laravel', 'VueJS', 'MySQL'],
                'close_days'  => 90,
                'employer_idx' => 5,
            ],
            [
                'title'       => 'Site Reliability Engineer (SRE)',
                'predes'      => 'VNG tuyển SRE đảm bảo uptime 99.99% cho hệ thống ZaloPay, xử lý incident, on-call rotation và improve observability.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 55000000,
                'exp_min'     => 5,
                'exp_max'     => 10,
                'job_level'   => 'senior',
                'address'     => 'VNG Campus, Quận 12, TP.HCM',
                'skills'      => ['Kubernetes', 'Docker', 'Linux', 'Python', 'AWS'],
                'close_days'  => 20,
                'employer_idx' => 2,
            ],
            [
                'title'       => 'Junior PHP Developer - Đà Nẵng',
                'predes'      => 'NashTech tuyển Junior PHP Developer tại văn phòng Đà Nẵng, phát triển web application cho khách hàng châu Âu. Được cấp chứng chỉ AWS.',
                'job_type'    => 'full-time',
                'work_mode'   => 'onsite',
                'salary'      => 9000000,
                'exp_min'     => 0,
                'exp_max'     => 1,
                'job_level'   => 'fresher',
                'address'     => 'Trung tâm Đà Nẵng, Đà Nẵng',
                'skills'      => ['PHP', 'MySQL', 'Git', 'JavaScript'],
                'close_days'  => 40,
                'employer_idx' => 3,
            ],
            [
                'title'       => 'React Native Developer',
                'predes'      => 'FPT tuyển React Native Developer xây dựng mobile app cross-platform cho khách hàng Nhật Bản. Yêu cầu kinh nghiệm tích hợp native modules.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 22000000,
                'exp_min'     => 2,
                'exp_max'     => 4,
                'job_level'   => 'middle',
                'address'     => '17 Duy Tân, Cầu Giấy, Hà Nội',
                'skills'      => ['JavaScript', 'ReactJS', 'TypeScript', 'Git'],
                'close_days'  => 30,
                'employer_idx' => 0,
            ],
            [
                'title'       => 'Database Administrator - PostgreSQL',
                'predes'      => 'Got It cần DBA PostgreSQL quản lý database cluster, tối ưu query performance và đảm bảo backup/recovery cho 10TB+ dữ liệu.',
                'job_type'    => 'full-time',
                'work_mode'   => 'hybrid',
                'salary'      => 30000000,
                'exp_min'     => 3,
                'exp_max'     => 7,
                'job_level'   => 'senior',
                'address'     => '220 Hoàng Diệu 2, Thủ Đức, TP.HCM',
                'skills'      => ['PostgreSQL', 'Python', 'Linux', 'AWS'],
                'close_days'  => 25,
                'employer_idx' => 7,
            ],
        ];

        foreach ($listingsData as $data) {
            $employer = $employerModels[$data['employer_idx']]['user'];

            $listing = Listing::create([
                'user_id'                => $employer->id,
                'title'                  => $data['title'],
                'slug'                   => Str::slug($data['title']) . '-' . Str::random(6),
                'predes'                 => $data['predes'],
                'description'            => $data['predes'] . "\n\n" . $this->fakeDescription(),
                'job_type'               => $data['job_type'],
                'work_mode'              => $data['work_mode'],
                'experience_years_min'   => $data['exp_min'],
                'experience_years_max'   => $data['exp_max'],
                'job_level'              => $data['job_level'],
                'address'                => $data['address'],
                'salary'                 => $data['salary'],
                'application_close_date' => now()->addDays($data['close_days'])->format('Y-m-d'),
                'status'                 => 'open',
            ]);

            // Attach skills
            $skillIds = collect($data['skills'])
                ->map(fn ($name) => $skills[$name]->id ?? null)
                ->filter()
                ->toArray();

            $listing->skills()->sync($skillIds);
        }

        // Thêm vài listing đã đóng / hết hạn để test filter Active_Listing
        $closedEmployer = $employerModels[0]['user'];
        Listing::create([
            'user_id'                => $closedEmployer->id,
            'title'                  => 'PHP Developer (Đã đóng)',
            'slug'                   => 'php-developer-da-dong-' . Str::random(6),
            'predes'                 => 'Tin này đã đóng tuyển dụng.',
            'description'            => 'Tin đã đóng.',
            'job_type'               => 'full-time',
            'work_mode'              => 'onsite',
            'experience_years_min'   => 1,
            'experience_years_max'   => 3,
            'job_level'              => 'junior',
            'address'                => 'Hà Nội',
            'salary'                 => 10000000,
            'application_close_date' => now()->subDays(10)->format('Y-m-d'),
            'status'                 => 'closed',
        ]);

        Listing::create([
            'user_id'                => $closedEmployer->id,
            'title'                  => 'Senior Java (Hết hạn)',
            'slug'                   => 'senior-java-het-han-' . Str::random(6),
            'predes'                 => 'Tin này đã hết hạn nộp hồ sơ.',
            'description'            => 'Tin đã hết hạn.',
            'job_type'               => 'full-time',
            'work_mode'              => 'onsite',
            'experience_years_min'   => 4,
            'experience_years_max'   => 8,
            'job_level'              => 'senior',
            'address'                => 'Hà Nội',
            'salary'                 => 30000000,
            'application_close_date' => now()->subDays(5)->format('Y-m-d'),
            'status'                 => 'open',  // open nhưng đã hết hạn → không phải Active_Listing
        ]);

        $count = Listing::where('status', 'open')
                        ->whereDate('application_close_date', '>=', now()->toDateString())
                        ->count();

        $this->command->info("✅ Seeded: " . count($skills) . " skills, " . count($employers) . " employers, {$count} active listings.");
        $this->command->info("📌 2 listings đã đóng/hết hạn được tạo thêm để test Active_Listing filter.");
        $this->command->info("🚀 Truy cập: GET /api/listings/search để bắt đầu kiểm tra.");

        $this->call([
            CategorySeeder::class,
            SkillSeeder::class,
            BannedKeywordSeeder::class,
        ]);
    }

    private function fakeDescription(): string
    {
        return "**Mô tả công việc:**\n- Phát triển và maintain hệ thống backend/frontend\n- Tham gia thiết kế kiến trúc kỹ thuật\n- Code review và mentoring team members\n\n**Yêu cầu:**\n- Có kinh nghiệm thực tế với các công nghệ được liệt kê\n- Kỹ năng giao tiếp tốt, chủ động trong công việc\n- Tiếng Anh đọc hiểu tài liệu kỹ thuật\n\n**Quyền lợi:**\n- Lương cạnh tranh, review 2 lần/năm\n- Bảo hiểm sức khỏe premium\n- 12 ngày phép/năm + lễ tết\n- Budget học tập 5 triệu/năm";
    }
}
