<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            [
                'LIB001', 'order',
                'Đơn mượn sách đang hoạt động',
                'Bạn đã nhận sách thành công.'
            ],
            [
                'LIB002', 'order',
                'Trả sách thành công',
                'Thư viện đã xác nhận bạn trả sách.'
            ],
            [
                'LIB003', 'overdue',
                'Sách đã quá hạn trả',
                'Vui lòng trả sách sớm để tránh phí phạt.'
            ],
            [
                'LIB004', 'order',
                'Đơn thuê đang chờ duyệt',
                'Yêu cầu thuê sách của bạn đang được xử lý.'
            ],
            [
                'LIB005', 'order',
                'Đơn mượn đã được duyệt',
                'Bạn có thể liên hệ thư viện để nhận sách.'
            ],
        ];

        foreach ($notifications as [$code, $type, $title, $message]) {
            $order = DB::table('borrow_orders')
                ->where('order_code', $code)
                ->first();

            if (!$order) {
                throw new \RuntimeException("Không tìm thấy đơn {$code}");
            }

            $itemId = DB::table('borrow_order_items')
                ->where('order_id', $order->id)
                ->value('id');

            // Tránh tạo thông báo trùng khi chạy nhiều lần.
            $exists = DB::table('notifications')
                ->where('user_id', $order->user_id)
                ->where('order_item_id', $itemId)
                ->where('type', $type)
                ->where('title', $title)
                ->exists();

            if (!$exists) {
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'order_item_id' => $itemId,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'read_at' => null,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
