<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $favorites = [
            ['an@example.test', 'Mắt biếc'],
            ['an@example.test', 'Atomic Habits'],
            ['binh@example.test', 'Nhà giả kim'],
            ['binh@example.test', 'Hoàng tử bé'],
            ['cuong@example.test', 'Clean Code'],
            ['cuong@example.test', '1984'],
            ['ha@example.test', 'Đắc nhân tâm'],
            ['ha@example.test', 'Harry Potter và Hòn đá Phù thủy'],
            ['huy@example.test', 'Dế Mèn phiêu lưu ký'],
            ['huy@example.test', 'Cho tôi xin một vé đi tuổi thơ'],
        ];

        foreach ($favorites as [$email, $title]) {
            $userId = DB::table('users')
                ->where('email', $email)->value('id');

            $bookId = DB::table('books')
                ->where('title', $title)->value('id');

            if (!$userId || !$bookId) {
                throw new \RuntimeException("Không tìm thấy dữ liệu yêu thích: {$title}");
            }

            DB::table('favorites')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'book_id' => $bookId,
                ],
                [
                    'created_at' => now(),
                ]
            );
        }
    }
}
