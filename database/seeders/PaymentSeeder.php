<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $payments = [
            [
                'LIB002', 'rental',
                'bank_transfer', 'completed', 'DEMO-PAY-002'
            ],
            [
                'LIB004', 'rental',
                'online', 'pending', 'DEMO-PAY-004'
            ],
        ];

        foreach ($payments as $data) {
            [$code, $type, $method, $status, $transactionCode] = $data;

            $order = DB::table('borrow_orders')
                ->where('order_code', $code)
                ->first();

            if (!$order) {
                throw new \RuntimeException("Không tìm thấy đơn {$code}");
            }

            DB::table('payments')->updateOrInsert(
                ['transaction_code' => $transactionCode],
                [
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'payment_type' => $type,
                    'payment_method' => $method,
                    'status' => $status,
                    'paid_at' => $status === 'completed'
                        ? now()->subDays(15) : null,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
