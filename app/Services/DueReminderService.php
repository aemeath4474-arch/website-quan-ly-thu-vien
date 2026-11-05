<?php
namespace App\Services;

use App\Models\BookNotification;
use App\Models\BorrowOrder;
use App\Models\BorrowOrderItem;
use Illuminate\Support\Facades\DB;

class DueReminderService
{
    public function run(?int $userId = null): array
    {
        $created = 0;
        $overdue = 0;
        $query = BorrowOrderItem::with(['book','order'])
            ->whereNull('returned_date')
            ->whereIn('status', ['borrowing','overdue'])
            ->whereHas('order', function ($q) use ($userId) {
                $q->whereIn('status', ['borrowing','overdue']);
                if ($userId !== null) $q->where('user_id', $userId);
            });
        $query->chunkById(100, function ($items) use (&$created, &$overdue) {
            foreach ($items as $item) {
                if (!$item->due_date) continue;
                $days = (int) today()->diffInDays($item->due_date, false);
                if ($days > 2) continue;
                $type = $days < 0 ? 'overdue' : 'due_soon';
                $title = $type === 'overdue' ? 'Sách đã quá hạn trả' : 'Sắp đến hạn trả sách';
                $message = 'Sách '.($item->book?->title ?? ('#'.$item->book_id)).
                    ' - hạn trả '. $item->due_date->format('d/m/Y').'.';
                // One notification per book, type and due-date. Extensions can notify again.
                $exists = BookNotification::where('user_id', $item->order->user_id)
                    ->where('order_item_id', $item->id)->where('type', $type)
                    ->where('message', $message)->exists();
                if (!$exists) {
                    BookNotification::create([
                        'user_id' => $item->order->user_id,
                        'order_item_id' => $item->id,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'created_at' => now(),
                    ]);
                    $created++;
                }
                if ($type === 'overdue' && $item->status !== 'overdue') {
                    $item->update(['status' => 'overdue']);
                    $overdue++;
                }
            }
        });
        $orders = BorrowOrder::whereIn('status',['borrowing','overdue'])
            ->when($userId !== null, fn($q) => $q->where('user_id',$userId))->get();
        foreach ($orders as $order) {
            $hasLate = $order->items()->whereNull('returned_date')->where('status','overdue')->exists();
            $target = $hasLate ? 'overdue' : 'borrowing';
            if ($order->status !== $target) $order->update(['status'=>$target]);
        }
        return compact('created','overdue');
    }
}
