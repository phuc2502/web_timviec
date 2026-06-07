<?php

namespace Database\Seeders;

use App\Models\BannedKeyword;
use Illuminate\Database\Seeder;

class BannedKeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keywords = [
            ['keyword' => 'lừa đảo', 'severity' => 'high'],
            ['keyword' => 'scam', 'severity' => 'high'],
            ['keyword' => 'cờ bạc', 'severity' => 'high'],
            ['keyword' => 'đánh bạc', 'severity' => 'high'],
            ['keyword' => 'việc nhẹ lương cao', 'severity' => 'high'],
            ['keyword' => 'cộng tác viên shopee', 'severity' => 'high'],
            ['keyword' => 'ctv shopee', 'severity' => 'high'],
            ['keyword' => 'tuyển ctv tiki', 'severity' => 'high'],
            ['keyword' => 'kiếm tiền online', 'severity' => 'medium'],
            ['keyword' => 'hoa hồng cao', 'severity' => 'medium'],
            ['keyword' => 'không cần kinh nghiệm lương 2000$', 'severity' => 'medium'],
            ['keyword' => 'nộp phí đặt cọc', 'severity' => 'high'],
            ['keyword' => 'rút gọn link kiếm tiền', 'severity' => 'high'],
            ['keyword' => 'đa cấp biến tướng', 'severity' => 'high'],
            ['keyword' => 'tuyển tài khoản ngân hàng', 'severity' => 'high'],
        ];

        foreach ($keywords as $kw) {
            BannedKeyword::firstOrCreate(
                ['keyword' => $kw['keyword']],
                [
                    'is_active' => true,
                    'severity' => $kw['severity'],
                ]
            );
        }
    }
}
