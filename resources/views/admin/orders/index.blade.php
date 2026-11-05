@extends('layouts.admin')
@section('title', 'Quản lý đơn mượn / thuê')
@section('admin-content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><div class="text-muted small">VẬN HÀNH BOOKHUB</div><h1 class="h3 mb-0">Quản lý đơn mượn / thuê</h1></div>
</div>
<div class="row g-3 mb-4">
    @foreach(['pending'=>'Chờ duyệt','borrowing'=>'Đang mượn','overdue'=>'Quá hạn','returned'=>'Đã trả'] as $key=>$label)
    <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted">{{ $label }}</div><div class="fs-3 fw-bold">{{ $stats[$key] ?? 0 }}</div></div></div></div>
    @endforeach
</div>
<form method="GET" class="card border-0 shadow-sm mb-3"><div class="card-body"><div class="row g-2 align-items-end">
    <div class="col-lg-3"><label class="form-label">Tìm kiếm</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Mã đơn, tên hoặc email"></div>
    <div class="col-md-4 col-lg-2"><label class="form-label">Trạng thái</label><select class="form-select" name="status"><option value="">Tất cả</option>@foreach(['pending'=>'Chờ duyệt','borrowing'=>'Đang mượn','overdue'=>'Quá hạn','returned'=>'Đã trả','rejected'=>'Từ chối','cancelled'=>'Đã hủy'] as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 col-lg-2"><label class="form-label">Hình thức</label><select class="form-select" name="transaction_type"><option value="">Tất cả</option><option value="borrow" @selected(request('transaction_type')==='borrow')>Mượn</option><option value="rent" @selected(request('transaction_type')==='rent')>Thuê</option></select></div>
    <div class="col-md-4 col-lg-2"><label class="form-label">Từ ngày</label><input type="date" class="form-control" name="from" value="{{ request('from') }}"></div>
    <div class="col-md-4 col-lg-2"><label class="form-label">Đến ngày</label><input type="date" class="form-control" name="to" value="{{ request('to') }}"></div>
    <div class="col-lg-1 d-grid"><button class="btn btn-dark">Lọc</button></div>
    <div class="col-12"><a href="{{ route('admin.orders.index') }}" class="small text-decoration-none">Xóa bộ lọc</a></div>
</div></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Mã đơn</th><th>Người dùng</th><th>Sách</th><th>Loại</th><th>Ngày tạo</th><th>Trạng thái</th><th></th></tr></thead><tbody>
@forelse($orders as $order)
@php($item=$order->items->first())
<tr><td class="fw-semibold">{{ $order->order_code }}</td><td>{{ $order->user->name }}<div class="small text-muted">{{ $order->user->email }}</div></td><td>{{ $item?->book?->title ?? '—' }} <span class="small text-muted">×{{ $item?->quantity }} @if($order->items->count()>1) (+{{ $order->items->count()-1 }} sách) @endif</span></td><td>{{ $order->transaction_type==='rent'?'Thuê':'Mượn' }}</td><td>{{ $order->order_date?->format('d/m/Y H:i') }}</td><td><span class="badge text-bg-{{ ['pending'=>'warning','borrowing'=>'primary','overdue'=>'danger','returned'=>'success','rejected'=>'secondary','cancelled'=>'secondary'][$order->status] ?? 'light' }}">{{ ['pending'=>'Chờ duyệt','borrowing'=>'Đang mượn','overdue'=>'Quá hạn','returned'=>'Đã trả','rejected'=>'Từ chối','cancelled'=>'Đã hủy'][$order->status] ?? $order->status }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-dark" href="{{ route('admin.orders.show',$order) }}">Chi tiết</a></td></tr>
@empty<tr><td colspan="7" class="text-center py-5 text-muted">Không tìm thấy đơn phù hợp.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-3">{{ $orders->links() }}</div>
@endsection
