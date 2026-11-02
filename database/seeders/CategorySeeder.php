<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Văn học Việt Nam', 'description' => 'Tiểu thuyết, truyện ngắn và tác phẩm văn học Việt Nam.'],
            ['name' => 'Văn học nước ngoài', 'description' => 'Tác phẩm văn học nổi tiếng trên thế giới.'],
            ['name' => 'Khoa học và công nghệ', 'description' => 'Sách khoa học, lập trình và công nghệ thông tin.'],
            ['name' => 'Kinh tế và kinh doanh', 'description' => 'Kiến thức kinh tế, quản trị và tài chính.'],
            ['name' => 'Tâm lý và kỹ năng sống', 'description' => 'Phát triển bản thân, tâm lý học và kỹ năng sống.'],
            ['name' => 'Lịch sử và văn hóa', 'description' => 'Lịch sử, văn hóa Việt Nam và thế giới.'],
            ['name' => 'Thiếu nhi', 'description' => 'Truyện và sách giáo dục dành cho thiếu nhi.'],
            ['name' => 'Giáo dục và học tập', 'description' => 'Sách phục vụ học tập, nghiên cứu và giảng dạy.'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
