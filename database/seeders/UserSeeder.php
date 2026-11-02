<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Quản trị viên', 'admin@example.test', '0900000001', 'Hà Nội', 'admin'],
            ['Nguyễn Văn An', 'an@example.test', '0900000002', 'Hà Nội', 'user'],
            ['Trần Thị Bình', 'binh@example.test', '0900000003', 'Đà Nẵng', 'user'],
            ['Lê Minh Cường', 'cuong@example.test', '0900000004', 'TP. Hồ Chí Minh', 'user'],
            ['Phạm Thu Hà', 'ha@example.test', '0900000005', 'Hải Phòng', 'user'],
            ['Hoàng Gia Huy', 'huy@example.test', '0900000006', 'Cần Thơ', 'user'],
        ];

        foreach ($users as [$name, $email, $phone, $address, $role]) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('Demo@12345'),
                    'phone' => $phone,
                    'address' => $address,
                    'role' => $role,
                    'status' => 'active',
                    'updated_at' => now(),
                ]
            );
        }
    }
}
