<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BorrowOrderSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $orders = [
            [
                'LIB001', 'an@example.test',
                'borrow', 'borrowing',
                'Mắt biếc', -5, 9, null
            ],
            [
                'LIB002', 'binh@example.test',
                'rent', 'returned',
                'Nhà giả kim', -15, 10, -7
            ],
            [
                'LIB003', 'cuong@example.test',
                'borrow', 'overdue',
                'Clean Code', -20, 7, null
            ],
            [
                'LIB004', 'ha@example.test',
                'rent', 'pending',
                'Harry Potter và Hòn đá Phù thủy', 0, 10, null
            ],
            [
                'LIB005', 'huy@example.test',
                'borrow', 'approved',
                'Dế Mèn phiêu lưu ký', 0, 14, null
            ],
        ];

        foreach ($orders as $data) {
            [
                $code, $email, $type, $status,
                $title, $offset, $duration, $returnOffset
            ] = $data;

            $userId = DB::table('users')
                ->where('email', $email)
                ->value('id');

            $book = DB::table('books')
                ->where('title', $title)
                ->first();

            if (!$userId || !$book) {
                throw new \RuntimeException("Thiếu người dùng hoặc sách cho đơn {$code}");
            }

            $borrowDate = $today->copy()->addDays($offset);
            $dueDate = $borrowDate->copy()->addDays($duration);

            $returnedDate = $returnOffset !== null
                ? $today->copy()->addDays($returnOffset)
                : null;

            $amount = $type === 'rent'
                ? $book->rental_price
                : 0;

            $approvedAt = in_array($status, [
                'approved', 'borrowing', 'returned', 'overdue'
            ]) ? $borrowDate->copy()->toDateTimeString() : null;

            DB::table('borrow_orders')->updateOrInsert(
                ['order_code' => $code],
                [
                    'user_id' => $userId,
                    'transaction_type' => $type,
                    'order_date' => $borrowDate,
                    'status' => $status,
                    'total_amount' => $amount,
                    'note' => 'Đơn hàng dữ liệu mẫu',
                    'approved_at' => $approvedAt,
                    'returned_at' => $returnedDate,
                    'updated_at' => now(),
                ]
            );

            $orderId = DB::table('borrow_orders')
                ->where('order_code', $code)
                ->value('id');

            DB::table('borrow_order_items')->updateOrInsert(
                [
                    'order_id' => $orderId,
                    'book_id' => $book->id,
                ],
                [
                    'quantity' => 1,
                    'unit_price' => $type === 'rent'
                        ? $book->rental_price : 0,
                    'borrow_date' => $borrowDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'returned_date' => $returnedDate?->toDateString(),
                    'status' => $status,
                    'late_fee' => 0,
                    'updated_at' => now(),
                ]
            );
        }

        // Đồng bộ số lượng còn trong kho với đơn đang mượn.
        $books = DB::table('books')->get();

        foreach ($books as $book) {
            $borrowed = DB::table('borrow_order_items as items')
                ->join('borrow_orders as orders', 'items.order_id', '=', 'orders.id')
                ->where('items.book_id', $book->id)
                ->whereIn('orders.status', ['borrowing', 'overdue'])
                ->sum('items.quantity');

            DB::table('books')
                ->where('id', $book->id)
                ->update([
                    'available_copies' => max(0, $book->total_copies - $borrowed)
                ]);
        }
    }
}
