<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        // ISBN dưới đây là mã thử nghiệm.
        $books = [
            ['Cho tôi xin một vé đi tuổi thơ', 'Văn học Việt Nam', 'Nguyễn Nhật Ánh', 2008, 85000, 12000],
            ['Mắt biếc', 'Văn học Việt Nam', 'Nguyễn Nhật Ánh', 1990, 110000, 15000],
            ['Tôi thấy hoa vàng trên cỏ xanh', 'Văn học Việt Nam', 'Nguyễn Nhật Ánh', 2010, 125000, 16000],
            ['Chí Phèo', 'Văn học Việt Nam', 'Nam Cao', 1941, 65000, 10000],
            ['Tắt đèn', 'Văn học Việt Nam', 'Ngô Tất Tố', 1939, 70000, 10000],
            ['Dế Mèn phiêu lưu ký', 'Thiếu nhi', 'Tô Hoài', 1941, 90000, 12000],
            ['Số đỏ', 'Văn học Việt Nam', 'Vũ Trọng Phụng', 1936, 85000, 12000],
            ['Nhà giả kim', 'Văn học nước ngoài', 'Paulo Coelho', 1988, 95000, 15000],
            ['Hoàng tử bé', 'Văn học nước ngoài', 'Antoine de Saint-Exupéry', 1943, 80000, 12000],
            ['1984', 'Văn học nước ngoài', 'George Orwell', 1949, 145000, 20000],
            ['Harry Potter và Hòn đá Phù thủy', 'Văn học nước ngoài', 'J. K. Rowling', 1997, 185000, 25000],
            ['Đắc nhân tâm', 'Tâm lý và kỹ năng sống', 'Dale Carnegie', 1936, 120000, 18000],
            ['Cha giàu cha nghèo', 'Kinh tế và kinh doanh', 'Robert T. Kiyosaki', 1997, 135000, 20000],
            ['Atomic Habits', 'Tâm lý và kỹ năng sống', 'James Clear', 2018, 175000, 25000],
            ['Clean Code', 'Khoa học và công nghệ', 'Robert C. Martin', 2008, 350000, 45000],
        ];

        foreach ($books as $index => $book) {
            [$title, $category, $author, $year, $price, $rentalPrice] = $book;

            $categoryId = DB::table('categories')
                ->where('name', $category)
                ->value('id');

            $authorId = DB::table('authors')
                ->where('name', $author)
                ->value('id');

            if (!$categoryId || !$authorId) {
                throw new \RuntimeException("Thiếu thể loại hoặc tác giả của sách: {$title}");
            }

            $isbn = '9780000' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT);

            DB::table('books')->updateOrInsert(
                ['isbn' => $isbn],
                [
                    'category_id' => $categoryId,
                    'title' => $title,
                    'publisher' => 'Nhà xuất bản mẫu',
                    'publication_year' => $year,
                    'description' => 'Sách ' . $title . ', thuộc bộ dữ liệu mẫu của thư viện.',
                    'cover_image' => null,
                    'price' => $price,
                    'rental_price' => $rentalPrice,
                    'total_copies' => 10,
                    'available_copies' => 10,
                    'status' => 'active',
                    'updated_at' => now(),
                ]
            );

            $bookId = DB::table('books')
                ->where('isbn', $isbn)
                ->value('id');

            DB::table('book_authors')->updateOrInsert([
                'book_id' => $bookId,
                'author_id' => $authorId,
            ]);
        }
    }
}
