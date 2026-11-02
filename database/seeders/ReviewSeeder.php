<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            ['an@example.test', 'Mắt biếc', 5, 'Câu chuyện rất cảm động và đáng đọc.'],
            ['binh@example.test', 'Nhà giả kim', 5, 'Một cuốn sách truyền cảm hứng rất tốt.'],
            ['cuong@example.test', 'Clean Code', 5, 'Sách rất hữu ích cho lập trình viên.'],
            ['ha@example.test', 'Đắc nhân tâm', 4, 'Nội dung thực tế và dễ áp dụng.'],
            ['huy@example.test', 'Dế Mèn phiêu lưu ký', 5, 'Tác phẩm phù hợp với nhiều lứa tuổi.'],
            ['an@example.test', 'Atomic Habits', 4, 'Nhiều kiến thức bổ ích về thói quen.'],
            ['binh@example.test', 'Hoàng tử bé', 5, 'Câu chuyện nhẹ nhàng nhưng sâu sắc.'],
            ['cuong@example.test', '1984', 4, 'Tác phẩm có nhiều ý tưởng đáng suy ngẫm.'],
        ];

        foreach ($reviews as [$email, $title, $rating, $comment]) {
            $userId = DB::table('users')
                ->where('email', $email)->value('id');

            $bookId = DB::table('books')
                ->where('title', $title)->value('id');

            if (!$userId || !$bookId) {
                throw new \RuntimeException("Không tìm thấy dữ liệu đánh giá: {$title}");
            }

            DB::table('reviews')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'book_id' => $bookId,
                ],
                [
                    'rating' => $rating,
                    'comment' => $comment,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
