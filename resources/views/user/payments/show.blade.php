@extends('layouts.app')
@section('title','Chuyển khoản thanh toán - Bookhub')
@section('content')
<section class="container py-5" style="max-width:960px">
    <a href="{{ route('my-orders.index') }}" class="text-decoration-none">← Quay lại đơn hàng</a>
    <h1 class="h2 my-3">Thanh toán chuyển khoản</h1>
    <p class="text-muted">Đơn {{ $payment->order->order_code }} · {{ ['rental'=>'Tiền thuê sách','late_fee'=>'Phí trả muộn','other'=>'Khoản thu khác'][$payment->payment_type] ?? 'Thanh toán' }}</p>
    <div class="row g-4"><div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100"><div class="card-body text-center">
            <h2 class="h5 mb-3">Quét mã VietQR</h2>
            @if($payment->status === 'completed')<div class="alert alert-success">Khoản này đã được xác nhận thanh toán.</div>
            @elseif($payment->qrUrl())
                <img class="img-fluid rounded border p-2" style="max-width:320px;width:100%" src="{{ $payment->qrUrl() }}" alt="Mã QR chuyển khoản đến tài khoản ngân hàng của Bookhub" referrerpolicy="no-referrer">
                <p class="small text-muted mt-3">Mở ứng dụng ngân hàng để quét QR. Kiểm tra đúng người thụ hưởng và số tiền trước khi xác nhận.</p>
                <p class="small text-muted">Ảnh QR được cung cấp bởi VietQR và cần kết nối Internet.</p>
            @else<div class="alert alert-warning">Chưa cấu hình thông tin ngân hàng để tạo mã QR. Vui lòng liên hệ quản trị viên.</div>@endif
        </div></div></div>
        <div class="col-lg-6"><div class="card shadow-sm border-0 h-100"><div class="card-body">
            <h2 class="h5 mb-3">Thông tin chuyển khoản</h2>
            <dl class="row mb-3">
                <dt class="col-5">Ngân hàng (BIN)</dt><dd class="col-7">{{ config('bookhub_payment.bank_bin') ?: 'Chưa cấu hình' }}</dd>
                <dt class="col-5">Số tài khoản</dt><dd class="col-7 fw-semibold">{{ config('bookhub_payment.bank_account') ?: '—' }}</dd>
                <dt class="col-5">Chủ tài khoản</dt><dd class="col-7">{{ config('bookhub_payment.bank_name') ?: '—' }}</dd>
                <dt class="col-5">Số tiền</dt><dd class="col-7 fw-bold text-success">{{ number_format($payment->amount,0,',','.') }} đ</dd>
                <dt class="col-5">Nội dung CK</dt><dd class="col-7 fw-bold user-select-all">{{ $payment->transferContent() }}</dd>
            </dl>
            @if($payment->status === 'completed')<div class="alert alert-success">Đã thanh toán · {{ $payment->paid_at?->format('d/m/Y H:i') }}</div>
            @elseif($payment->verification_status === 'submitted')<div class="alert alert-info">Đã gửi mã giao dịch <strong>{{ $payment->customer_transaction_code }}</strong>. Admin đang đối soát; chưa được ghi nhận đã thu.</div>
            @else
                @if($payment->verification_status === 'rejected')<div class="alert alert-danger">Thông tin đã gửi chưa được xác minh: {{ $payment->verification_note }}. Bạn có thể gửi lại mã giao dịch.</div>@endif
                @if($payment->bankConfigured())
                <form action="{{ route('my-payments.submit', $payment) }}" method="POST">@csrf
                    <label for="transaction_code" class="form-label fw-semibold">Mã giao dịch / mã tham chiếu từ ngân hàng</label>
                    <input id="transaction_code" name="transaction_code" class="form-control mb-2" required minlength="4" maxlength="100" value="{{ old('transaction_code') }}" placeholder="Nhập mã giao dịch trên biên lai ngân hàng">
                    <p class="small text-muted">Chỉ gửi sau khi đã thực hiện chuyển khoản. Việc gửi mã không đồng nghĩa khoản tiền đã được xác nhận.</p>
                    <button class="btn btn-primary w-100">Tôi đã chuyển khoản – Gửi xác minh</button>
                </form>
                @endif
            @endif
        </div></div></div>
    </div>
</section>
@endsection
