<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowOrder;
use App\Models\BorrowOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BorrowOrderController extends Controller
{
    private function refreshOverdueStatuses(): void
    {
        BorrowOrderItem::whereIn('status', ['borrowing', 'approved'])
            ->whereNull('returned_date')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue']);

        BorrowOrder::whereIn('status', ['borrowing', 'approved'])
            ->whereHas('items', fn ($q) => $q->where('status', 'overdue'))
            ->update(['status' => 'overdue']);
    }

    public function index(Request $request): View
    {
        $this->refreshOverdueStatuses();

        $query = BorrowOrder::with(['user', 'items.book'])->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->string('transaction_type')->toString());
        }
        if ($request->filled('from')) {
            $query->whereDate('order_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('order_date', '<=', $request->date('to'));
        }
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('order_code', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")));
        }

        $orders = $query->paginate(15)->withQueryString();
        $stats = [
            'pending' => BorrowOrder::where('status', 'pending')->count(),
            'borrowing' => BorrowOrder::where('status', 'borrowing')->count(),
            'overdue' => BorrowOrder::where('status', 'overdue')->count(),
            'returned' => BorrowOrder::where('status', 'returned')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(BorrowOrder $order): View
    {
        $this->refreshOverdueStatuses();
        $order->load(['user', 'items.book', 'payments']);
        return view('admin.orders.show', compact('order'));
    }

    public function approve(BorrowOrder $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể duyệt đơn đang chờ xử lý.');
        }

        try {
            DB::transaction(function () use ($order) {
                $lockedOrder = BorrowOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($lockedOrder->status !== 'pending') {
                    throw new \RuntimeException('Đơn này đã được xử lý.');
                }
                foreach ($lockedOrder->items as $item) {
                    $book = $item->book()->lockForUpdate()->firstOrFail();
                    if ($book->status !== 'active' || $book->available_copies < $item->quantity) {
                        throw new \RuntimeException('Sách "' . $book->title . '" không còn đủ số lượng.');
                    }
                    $book->decrement('available_copies', $item->quantity);
                    $days = $lockedOrder->transaction_type === 'rent' ? 7 : 14;
                    $item->update(['status' => 'borrowing', 'borrow_date' => today(), 'due_date' => today()->addDays($days)]);
                }
                $lockedOrder->update(['status' => 'borrowing', 'approved_at' => now()]);
                if ($lockedOrder->transaction_type === 'rent' && (float) $lockedOrder->total_amount > 0) {
                    $lockedOrder->payments()->create([
                        'amount' => $lockedOrder->total_amount,
                        'payment_type' => 'rental',
                        'payment_method' => 'cash',
                        'status' => 'pending',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã duyệt đơn và cập nhật số lượng sách trong kho.');
    }

    public function reject(BorrowOrder $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể từ chối đơn đang chờ xử lý.');
        }
        DB::transaction(function () use ($order) {
            $order->update(['status' => 'rejected']);
            $order->items()->update(['status' => 'rejected']);
        });
        return back()->with('success', 'Đã từ chối đơn.');
    }

    public function returnBook(BorrowOrder $order): RedirectResponse
    {
        if (!in_array($order->status, ['borrowing', 'overdue'], true)) {
            return back()->with('error', 'Đơn này không ở trạng thái đang mượn.');
        }
        DB::transaction(function () use ($order) {
            $lockedOrder = BorrowOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            foreach ($lockedOrder->items as $item) {
                $this->returnSingleItem($item);
            }
            $this->syncOrderReturnStatus($lockedOrder);
        });
        return back()->with('success', 'Đã xác nhận trả toàn bộ sách trong đơn.');
    }

    public function returnItem(BorrowOrder $order, BorrowOrderItem $item): RedirectResponse
    {
        abort_unless((int) $item->order_id === (int) $order->id, 404);
        try {
            DB::transaction(function () use ($order, $item) {
                $lockedOrder = BorrowOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $lockedItem = BorrowOrderItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
                if (!in_array($lockedOrder->status, ['borrowing', 'overdue'], true)) {
                    throw new \RuntimeException('Đơn không ở trạng thái đang mượn.');
                }
                $this->returnSingleItem($lockedItem);
                $this->syncOrderReturnStatus($lockedOrder);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Đã xác nhận trả cuốn sách này.');
    }

    public function extendDueDate(Request $request, BorrowOrder $order, BorrowOrderItem $item): RedirectResponse
    {
        abort_unless((int) $item->order_id === (int) $order->id, 404);
        $data = $request->validate(['due_date' => ['required', 'date', 'after_or_equal:today']]);
        if (!in_array($order->status, ['borrowing', 'overdue'], true) || $item->returned_date) {
            return back()->with('error', 'Không thể gia hạn sách đã trả hoặc đơn không còn hiệu lực.');
        }
        $item->update(['due_date' => $data['due_date'], 'status' => 'borrowing']);
        $this->refreshOverdueStatuses();
        $order->refresh();
        if ($order->status === 'overdue' && !$order->items()->where('status', 'overdue')->exists()) {
            $order->update(['status' => 'borrowing']);
        }
        return back()->with('success', 'Đã cập nhật hạn trả sách.');
    }

    private function returnSingleItem(BorrowOrderItem $item): void
    {
        if ($item->returned_date) {
            return;
        }
        $book = $item->book()->lockForUpdate()->firstOrFail();
        $book->increment('available_copies', $item->quantity);
        $daysLate = $item->due_date && today()->greaterThan($item->due_date) ? (int) $item->due_date->diffInDays(today()) : 0;
        $lateFee = $daysLate * 5000 * $item->quantity;
        $item->update([
            'returned_date' => today(),
            'late_fee' => $lateFee,
            'status' => $daysLate > 0 ? 'returned_late' : 'returned',
        ]);
        if ($lateFee > 0) {
            $item->order->payments()->create([
                'amount' => $lateFee,
                'payment_type' => 'late_fee',
                'payment_method' => 'cash',
                'status' => 'pending',
            ]);
        }
    }

    private function syncOrderReturnStatus(BorrowOrder $order): void
    {
        $hasUnreturned = $order->items()->whereNull('returned_date')->exists();
        if (!$hasUnreturned) {
            $order->update(['status' => 'returned', 'returned_at' => now()]);
        } else {
            $hasOverdue = $order->items()->where('status', 'overdue')->exists();
            $order->update(['status' => $hasOverdue ? 'overdue' : 'borrowing']);
        }
    }
}
