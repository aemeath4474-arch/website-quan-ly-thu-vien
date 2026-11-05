@extends('layouts.admin')
@section('title','Quản lý thanh toán')
@section('admin-content')
<h1 class="h3 mb-3">Thanh toán và đối soát chuyển khoản</h1>
<div class="alert alert-info">Chỉ duyệt chuyển khoản khi sao kê ngân hàng đã được kiểm tra nguồn gốc và giao dịch khớp chính xác: mã giao dịch, tài khoản nhận, số tiền, nội dung chuyển khoản. <a href="{{ route('admin.bank-statements.index') }}" class="alert-link">Nhập và xem sao kê CSV</a>. Dữ liệu CSV không tự chứng minh tiền đã vào ngân hàng.</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">Chờ thu<h3>{{ number_format($pendingAmount,0,',','.') }} đ</h3></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">Đã thu<h3>{{ number_format($receivedAmount,0,',','.') }} đ</h3></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">Yêu cầu cần đối soát<h3>{{ $awaitingVerification }}</h3></div></div></div>
</div>
<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
<select name="status" class="form-select w-auto"><option value="">Mọi trạng thái</option>@foreach(['pending'=>'Chờ thu','completed'=>'Đã thu','failed'=>'Thất bại','refunded'=>'Đã hoàn'] as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select>
<select name="verification" class="form-select w-auto"><option value="">Mọi yêu cầu</option>@foreach(['awaiting'=>'Chưa gửi','submitted'=>'Cần đối soát','rejected'=>'Đã yêu cầu gửi lại','verified'=>'Đã xác minh'] as $key=>$label)<option value="{{ $key }}" @selected(request('verification')===$key)>{{ $label }}</option>@endforeach</select>
<select name="type" class="form-select w-auto"><option value="">Mọi loại thu</option><option value="rental" @selected(request('type')==='rental')>Tiền thuê</option><option value="late_fee" @selected(request('type')==='late_fee')>Phí trả muộn</option></select>
<button class="btn btn-dark">Lọc</button>
</form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
<thead class="table-light"><tr><th>Đơn / Người dùng</th><th>Khoản thu</th><th>Số tiền</th><th>Thông tin đối soát</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($payments as $payment)
<tr>
<td><a href="{{ route('admin.orders.show',$payment->order_id) }}">{{ $payment->order?->order_code }}</a><div class="small text-muted">{{ $payment->order?->user?->name }}</div></td>
<td>{{ ['rental'=>'Tiền thuê','late_fee'=>'Phí trả muộn','other'=>'Khác'][$payment->payment_type] ?? $payment->payment_type }}</td>
<td class="fw-semibold">{{ number_format($payment->amount,0,',','.') }} đ</td>
<td class="small"><div>Nội dung: <strong>{{ $payment->transferContent() }}</strong></div><div>Khách khai báo: <strong>{{ $payment->customer_transaction_code ?: '—' }}</strong></div>@if($payment->customer_submitted_at)<div class="text-muted">{{ $payment->customer_submitted_at->format('d/m/Y H:i') }}</div>@endif @if($payment->verification_note)<div class="text-danger">{{ $payment->verification_note }}</div>@endif</td>
<td>@if($payment->status==='completed')<span class="badge text-bg-success">Đã thu</span>@elseif($payment->verification_status==='submitted')<span class="badge text-bg-warning">Cần đối soát</span>@elseif($payment->verification_status==='rejected')<span class="badge text-bg-danger">Đã yêu cầu gửi lại</span>@else<span class="badge text-bg-secondary">Chưa thanh toán</span>@endif @if($payment->paid_at)<div class="small text-muted">{{ $payment->paid_at->format('d/m/Y H:i') }}</div>@endif</td>
<td style="min-width:230px">
@if($payment->status==='pending')
    @if($payment->verification_status==='submitted')
      @php($issues=$matchErrors[$payment->id] ?? ['Không có dữ liệu sao kê.'])
      @if(count($issues)===0 && isset($matchIds[$payment->id]))
      <div class="small text-success mb-2">✓ Đủ điều kiện khớp dữ liệu sao kê. Hãy xác minh tệp sao kê là dữ liệu ngân hàng thật.</div>
      <form method="POST" action="{{ route('admin.payments.complete',$payment) }}" onsubmit="return confirm('Đã kiểm tra nguồn sao kê chính thức và tiền thực nhận?')" class="mb-2">@csrf @method('PATCH')
       <input type="hidden" name="bank_transaction_id" value="{{ $matchIds[$payment->id] }}">
       <button class="btn btn-success btn-sm w-100">Duyệt sau khi đối soát</button>
      </form>
      @else
      <div class="small text-danger mb-2">Chưa thể duyệt:<ul class="ps-3 mb-1">@foreach($issues as $issue)<li>{{ $issue }}</li>@endforeach</ul></div>
      <button class="btn btn-secondary btn-sm w-100 mb-2" disabled>Chưa đủ điều kiện duyệt</button>
      @endif
    @else
      <span class="small text-muted">Chưa có xác nhận chuyển khoản từ khách. Không thể duyệt.</span>
    @endif
    @if($payment->verification_status==='submitted')
    <form method="POST" action="{{ route('admin.payments.reject',$payment) }}" class="d-grid gap-1">@csrf @method('PATCH')
        <input name="reason" class="form-control form-control-sm" required minlength="5" maxlength="500" placeholder="Lý do chưa xác minh được">
        <button class="btn btn-sm btn-outline-danger">Yêu cầu gửi lại</button>
    </form>
    @endif
@else<span class="small text-muted">{{ $payment->transaction_code ?: 'Hoàn tất' }}</span>@endif
</td></tr>
@empty<tr><td colspan="6" class="text-center py-5 text-muted">Chưa có khoản thanh toán.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $payments->links() }}</div>
@endsection
