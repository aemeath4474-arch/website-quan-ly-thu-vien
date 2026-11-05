@extends('layouts.app')
@section('title', 'Đơn mượn / thuê sách - Bookhub')
@section('content')
<section class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div><p class="text-uppercase text-primary fw-bold small mb-1">Tài khoản của tôi</p><h1 class="h2 mb-0">Đơn mượn / thuê sách</h1></div>
        <a class="btn btn-outline-dark" href="{{ route('books.index') }}">Tiếp tục khám phá sách</a>
    </div>
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>Mã đơn</th><th>Sách</th><th>Hình thức</th><th>Ngày gửi</th><th>Hạn trả</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            @php($item = $order->items->first())
            <tr>
                <td class="fw-semibold">{{ $order->order_code }}</td>
                <td>{{ $item?->book?->title ?? 'Sách đã xóa' }} <span class="text-muted small">× {{ $item?->quantity }}</span></td>
                <td>{{ $order->transaction_type === 'rent' ? 'Thuê sách' : 'Mượn sách' }}</td>
                <td>{{ $order->order_date?->format('d/m/Y') }}</td>
                <td>{{ $item?->due_date?->format('d/m/Y') ?? '—' }}</td>
                <td><span class="badge rounded-pill text-bg-{{ in_array($order->status,['returned','approved','borrowing']) ? 'success' : (in_array($order->status,['rejected','cancelled','overdue']) ? 'danger' : 'warning') }}">{{ ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối','borrowing'=>'Đang mượn','returned'=>'Đã trả','overdue'=>'Quá hạn','cancelled'=>'Đã hủy'][$order->status] ?? $order->status }}</span></td>
                <td class="text-end">@if($order->status === 'pending')<form method="POST" action="{{ route('my-orders.cancel', $order) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy yêu cầu này?')">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger">Hủy đơn</button></form>@endif</td>
            </tr>
            @if($order->payments->isNotEmpty())<tr class="table-light"><td colspan="7"><div class="small"><strong>Thanh toán:</strong> @foreach($order->payments as $payment)<span class="me-3">{{ $payment->payment_type==='late_fee'?'Phí trễ':'Tiền thuê' }} {{ number_format($payment->amount,0,',','.') }} đ — {{ $payment->status==='completed'?'Đã thu':($payment->verification_status==='submitted'?'Chờ xác minh':'Chưa thanh toán') }} @if($payment->status==='pending')<a class="btn btn-sm btn-outline-primary ms-2" href="{{ route('my-payments.show', $payment) }}">Chuyển khoản / QR</a>@endif</span>@endforeach</div></td></tr>@endif
            @if($item && (float)$item->late_fee > 0)<tr class="table-light"><td colspan="7" class="small text-danger">Phí trả trễ: {{ number_format($item->late_fee,0,',','.') }} đ</td></tr>@endif
        @empty <tr><td colspan="7" class="text-center py-5 text-muted">Bạn chưa có đơn mượn hoặc thuê sách nào.</td></tr> @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $orders->links() }}</div>
</section>
@endsection
