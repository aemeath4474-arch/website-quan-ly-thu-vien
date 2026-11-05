@extends('layouts.admin')
@section('title','Đối chiếu sao kê ngân hàng')
@section('admin-content')
<h1 class="h3 mb-3">Nhập giao dịch ngân hàng để đối soát</h1>
<a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm mb-3">← Quay lại thanh toán</a>
<div class="alert alert-warning">Chỉ nhập dữ liệu giao dịch tiền <strong>đã ghi có</strong> từ sao kê chính thức do ngân hàng cung cấp, không nhập ảnh chụp hoặc thông tin khách hàng tự gửi. CSV tự nhập không có chữ ký ngân hàng nên hệ thống không thể xác thực tính thật giả của tệp. Admin phải kiểm tra trên ứng dụng ngân hàng.</div>
<div class="card shadow-sm border-0 mb-4"><div class="card-body">
<h2 class="h5">Nhập sao kê CSV</h2>
<p class="small">Định dạng UTF-8, dấu phân tách phẩy, có đúng 6 cột theo thứ tự:</p>
<code>transaction_id,bank_bin,account_number,amount,description,transaction_at</code>
<p class="small mt-2 mb-2">Ví dụ: <code>TX123456,970422,0123456789,50000,BHP12O3,2026-11-04 10:15:00</code> (dữ liệu minh họa). Số tiền không có dấu phân cách hàng nghìn, ngày theo YYYY-MM-DD HH:MM:SS. Tối đa 500 dòng/2 MB mỗi lượt.</p>
<form method="POST" action="{{ route('admin.bank-statements.upload') }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">@csrf
<input type="file" class="form-control" name="statement" accept=".csv,text/csv" required style="max-width:450px">
<button class="btn btn-primary">Nhập dữ liệu đối soát</button></form>
</div></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-striped align-middle mb-0"><thead><tr><th>Mã ngân hàng</th><th>Ngân hàng / STK nhận</th><th>Số tiền</th><th>Nội dung</th><th>Thời điểm ghi có</th><th>Đối soát</th></tr></thead><tbody>
@forelse($transactions as $row)<tr><td>{{ $row->transaction_id }}</td><td>{{ $row->bank_bin }} / {{ $row->account_number }}</td><td>{{ number_format($row->amount,0,',','.') }} đ</td><td>{{ $row->description }}</td><td>{{ $row->transaction_at->format('d/m/Y H:i:s') }}</td><td>@if($row->matched_payment_id)<span class="badge text-bg-success">Đã dùng cho #{{ $row->matched_payment_id }}</span>@else<span class="badge text-bg-secondary">Chưa ghép khoản thu</span>@endif</td></tr>
@empty<tr><td colspan="6" class="text-center py-4">Chưa nhập giao dịch ngân hàng.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-3">{{ $transactions->links() }}</div>
@endsection
