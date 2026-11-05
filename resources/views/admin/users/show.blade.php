@extends('layouts.admin')
@section('admin-content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><a href="{{ route('admin.users.index') }}" class="text-decoration-none small">← Quay lại danh sách</a><h2 class="fw-bold mt-2 mb-1">Thông tin người dùng</h2><div class="text-muted">Tài khoản #{{ $user->id }}</div></div>
    <span class="badge fs-6 {{ $user->status==='active'?'text-bg-success':'text-bg-danger' }}">{{ $user->status==='active'?'Đang hoạt động':'Đã khóa' }}</span>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
        <h5 class="fw-bold mb-3">Thông tin tài khoản</h5>
        <dl class="row mb-0"><dt class="col-sm-4">Họ tên</dt><dd class="col-sm-8">{{ $user->name }}</dd><dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $user->email }}</dd><dt class="col-sm-4">Điện thoại</dt><dd class="col-sm-8">{{ $user->phone ?: 'Chưa cập nhật' }}</dd><dt class="col-sm-4">Địa chỉ</dt><dd class="col-sm-8">{{ $user->address ?: 'Chưa cập nhật' }}</dd><dt class="col-sm-4">Ngày tham gia</dt><dd class="col-sm-8">{{ $user->created_at?->format('d/m/Y H:i') }}</dd></dl>
    </div></div></div>
    <div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
        <h5 class="fw-bold mb-3">Quản lý tài khoản</h5>
        <form method="POST" action="{{ route('admin.users.role', $user) }}" class="mb-3">@csrf @method('PATCH')<label class="form-label">Phân quyền</label><div class="input-group"><select name="role" class="form-select"><option value="user" @selected($user->role==='user')>Người dùng</option><option value="admin" @selected($user->role==='admin')>Quản trị viên</option></select><button class="btn btn-outline-primary">Cập nhật quyền</button></div></form>
        @if(auth()->id() !== $user->id)
        <form method="POST" action="{{ route('admin.users.status', $user) }}" onsubmit="return confirm('Bạn có chắc muốn {{ $user->status==='active'?'khóa':'mở khóa' }} tài khoản này?')">@csrf @method('PATCH')<button class="btn {{ $user->status==='active'?'btn-outline-danger':'btn-outline-success' }}">{{ $user->status==='active'?'Khóa tài khoản':'Mở khóa tài khoản' }}</button></form>
        @else <div class="small text-muted">Bạn không thể tự khóa tài khoản hoặc tự hạ quyền.</div>@endif
        <div class="small text-muted mt-3">Tài khoản bị khóa không thể đăng nhập.</div>
    </div></div></div>
</div>
<div class="row g-3 mb-4">
    @foreach([['Tổng số đơn','total'],['Chờ duyệt','pending'],['Đang mượn / quá hạn','borrowing'],['Đã trả','returned']] as [$label,$key])
    <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">{{ $label }}</div><div class="fs-3 fw-bold">{{ $orderStats[$key] }}</div></div></div></div>
    @endforeach
</div>
<div class="card border-0 shadow-sm"><div class="card-body"><h5 class="fw-bold mb-3">10 đơn mượn / thuê gần nhất</h5>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Mã đơn</th><th>Loại</th><th>Ngày đặt</th><th>Sách</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    @forelse($user->borrowOrders as $order)
        <tr><td class="fw-semibold">{{ $order->order_code }}</td><td>{{ $order->transaction_type==='borrow'?'Mượn':'Thuê' }}</td><td>{{ $order->order_date?->format('d/m/Y') }}</td><td>{{ $order->items->count() }} đầu sách</td><td>{{ ucfirst($order->status) }}</td><td><a class="btn btn-sm btn-outline-dark" href="{{ route('admin.orders.show',$order) }}">Xem đơn</a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">Người dùng chưa có đơn mượn / thuê.</td></tr>@endforelse
    </tbody></table></div>
</div></div>
@endsection
