<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowOrder;
use App\Models\BorrowOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BorrowOrderController extends Controller
{
    public function index(): View
    {
        BorrowOrderItem::whereIn('status', ['borrowing', 'approved'])->whereNull('returned_date')->whereDate('due_date', '<', today())->update(['status' => 'overdue']);
        BorrowOrder::where('status', 'borrowing')->whereHas('items', fn($q) => $q->where('status', 'overdue'))->update(['status' => 'overdue']);
        $orders = BorrowOrder::with(['items.book','payments'])->where('user_id', auth()->id())->latest()->paginate(10);
        return view('user.orders.index', compact('orders'));
    }

    public function store(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'transaction_type' => ['required', 'in:borrow,rent'],
            'quantity' => ['required', 'integer', 'min:1', 'max:5'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($book->status !== 'active') {
            return back()->withErrors(['book' => 'Sách hiện không được cung cấp.'])->withInput();
        }
        if ($book->available_copies < (int) $data['quantity']) {
            return back()->withErrors(['quantity' => 'Số lượng sách còn lại không đủ.'])->withInput();
        }

        $days = $data['transaction_type'] === 'rent' ? 7 : 14;
        $price = $data['transaction_type'] === 'rent' ? $book->rental_price : 0;

        DB::transaction(function () use ($data, $book, $days, $price) {
            $order = BorrowOrder::create([
                'user_id' => auth()->id(),
                'order_code' => 'BH' . now()->format('ymd') . strtoupper(Str::random(6)),
                'transaction_type' => $data['transaction_type'],
                'order_date' => now(),
                'status' => 'pending',
                'total_amount' => $price * (int) $data['quantity'],
                'note' => $data['note'] ?? null,
            ]);

            $order->items()->create([
                'book_id' => $book->id,
                'quantity' => (int) $data['quantity'],
                'unit_price' => $price,
                'borrow_date' => today(),
                'due_date' => today()->addDays($days),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('my-orders.index')->with('success', 'Đã gửi yêu cầu. Vui lòng chờ quản trị viên xác nhận.');
    }

    public function cancel(BorrowOrder $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);
        if ($order->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể hủy yêu cầu đang chờ duyệt.');
        }
        $order->update(['status' => 'cancelled']);
        $order->items()->update(['status' => 'cancelled']);
        return back()->with('success', 'Đã hủy yêu cầu.');
    }
}
