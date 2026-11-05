<?php

namespace App\Http\Controllers;

use App\Models\BookNotification;
use App\Models\BorrowOrder;
use App\Models\BorrowOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $this->syncOrderNotifications($request->user()->id);
        $notifications = BookNotification::where('user_id', $request->user()->id)
            ->latest('created_at')->paginate(15);
        return view('user.notifications.index', compact('notifications'));
    }

    public function read(Request $request, BookNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function readAll(Request $request): RedirectResponse
    {
        BookNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    private function syncOrderNotifications(int $userId): void
    {
        app(\App\Services\DueReminderService::class)->run($userId);
        $orders = BorrowOrder::where('user_id', $userId)->get();
        foreach ($orders as $order) {
            $state = match ($order->status) {
                'pending' => ['Yêu cầu đã được tiếp nhận', 'Đơn '.$order->order_code.' đang chờ thư viện xử lý.'],
                'borrowing' => ['Đơn đã được duyệt', 'Đơn '.$order->order_code.' đang được mượn/thuê.'],
                'rejected' => ['Đơn bị từ chối', 'Đơn '.$order->order_code.' đã bị từ chối.'],
                'returned' => ['Đã trả sách', 'Đơn '.$order->order_code.' đã hoàn tất trả sách.'],
                'cancelled' => ['Đã hủy đơn', 'Đơn '.$order->order_code.' đã được hủy.'],
                default => null,
            };
            if (!$state) continue;
            [$title,$message] = $state;
            if (!BookNotification::where('user_id',$userId)->whereNull('order_item_id')
                ->where('type','order')->where('message',$message)->exists()) {
                BookNotification::create([
                    'user_id'=>$userId,'title'=>$title,'message'=>$message,
                    'type'=>'order','created_at'=>now(),
                ]);
            }
        }
    }
}
