<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $authors = [
            ['Nguyễn Nhật Ánh', 'Nhà văn Việt Nam nổi tiếng với các tác phẩm viết về tuổi học trò.'],
            ['Nam Cao', 'Nhà văn hiện thực Việt Nam, tác giả Chí Phèo và Lão Hạc.'],
            ['Ngô Tất Tố', 'Nhà văn Việt Nam, tác giả tiểu thuyết Tắt đèn.'],
            ['Tô Hoài', 'Nhà văn Việt Nam, tác giả Dế Mèn phiêu lưu ký.'],
            ['Vũ Trọng Phụng', 'Nhà văn Việt Nam, tác giả tiểu thuyết Số đỏ.'],
            ['Paulo Coelho', 'Nhà văn Brazil, tác giả Nhà giả kim.'],
            ['Antoine de Saint-Exupéry', 'Nhà văn Pháp, tác giả Hoàng tử bé.'],
            ['George Orwell', 'Nhà văn Anh, tác giả 1984 và Animal Farm.'],
            ['J. K. Rowling', 'Nhà văn Anh, tác giả loạt truyện Harry Potter.'],
            ['Dale Carnegie', 'Tác giả các tác phẩm nổi tiếng về giao tiếp và phát triển bản thân.'],
            ['Robert T. Kiyosaki', 'Tác giả người Mỹ nổi tiếng với sách tài chính cá nhân.'],
            ['James Clear', 'Tác giả Atomic Habits, chuyên viết về thói quen và phát triển bản thân.'],
            ['Yuval Noah Harari', 'Nhà sử học, tác giả Sapiens và Homo Deus.'],
            ['Robert C. Martin', 'Tác giả nổi tiếng trong lĩnh vực kỹ thuật phần mềm.'],
            ['Thomas H. Cormen', 'Đồng tác giả sách Introduction to Algorithms.'],
        ];

        foreach ($authors as [$name, $biography]) {
            DB::table('authors')->updateOrInsert(
                ['name' => $name],
                [
                    'biography' => $biography,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
