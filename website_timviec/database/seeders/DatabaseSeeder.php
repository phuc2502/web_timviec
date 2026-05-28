<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tài khoản Employee kiểm thử
        User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name'              => 'Nguyễn Văn A',
                'password'          => bcrypt('password'),
                'user_type'         => 'employee',
                'email_verified_at' => now(),
                'about'             => 'Lập trình viên Backend với 3 năm kinh nghiệm Laravel, MySQL, Redis.',
            ]
        );

        // Tài khoản Employer kiểm thử (dùng để test middleware phân quyền)
        User::updateOrCreate(
            ['email' => 'employer@example.com'],
            [
                'name'              => 'ABC Tech Vietnam',
                'password'          => bcrypt('password'),
                'user_type'         => 'employer',
                'email_verified_at' => now(),
                'company_name'      => 'ABC Tech Vietnam',
                'about'             => 'Công ty phần mềm hàng đầu Việt Nam.',
            ]
        );
    }
}
