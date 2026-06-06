<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Kiểm tra xem đã có admin chưa
        $exists = DB::table('users')->where('user_type', 'admin')->exists();

        if (!$exists) {
            DB::table('users')->insert([
                'name'              => 'Super Admin',
                'email'             => 'admin@timviec.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('Admin@123456'),
                'user_type'         => 'admin',
                'is_admin'          => 1,
                'is_banned'         => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $this->command->info('✅ Admin tạo thành công!');
            $this->command->info('   Email   : admin@timviec.com');
            $this->command->info('   Password: Admin@123456');
        } else {
            // Đảm bảo admin hiện tại có is_admin = 1
            DB::table('users')
                ->where('user_type', 'admin')
                ->update(['is_admin' => 1]);

            $this->command->warn('⚠  Admin đã tồn tại, bỏ qua. Đã đồng bộ is_admin = 1.');
        }

        // Đồng bộ: bất kỳ user nào có user_type = 'admin' đều có is_admin = 1
        DB::table('users')
            ->where('user_type', 'admin')
            ->update(['is_admin' => 1]);

        // Ngược lại: user có is_admin = 1 mà user_type không phải admin -> fix user_type
        DB::table('users')
            ->where('is_admin', 1)
            ->where('user_type', '!=', 'admin')
            ->update(['user_type' => 'admin']);
    }
}
