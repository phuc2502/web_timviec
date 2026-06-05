<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Lập trình Web',
            'Phát triển Ứng dụng Di động',
            'Trí tuệ Nhân tạo & Học máy',
            'Khoa học Dữ liệu',
            'Kiểm thử Phần mềm (QA/QC)',
            'Điện toán Đám mây & DevOps',
            'Bảo mật Thông tin (Cyber Security)',
            'Quản trị Hệ thống & Mạng',
            'Thiết kế Giao diện (UI/UX Design)',
            'Quản lý Dự án Công nghệ (Project Management)',
            'Phân tích Nghiệp vụ (Business Analyst)',
            'Phát triển Trò chơi (Game Development)',
            'Kỹ sư Cầu nối (BrSE)',
            'Quản trị Cơ sở Dữ liệu (DBA)',
            'Hỗ trợ Kỹ thuật IT (Helpdesk/Support)',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'is_active' => true,
                ]
            );
        }
    }
}
