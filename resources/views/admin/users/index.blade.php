@extends('layouts.admin')
@section('admin-content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><h2 class="fw-bold mb-1">Quản lý người dùng</h2><div class="text-muted">Theo dõi tài khoản, trạng thái và quyền truy cập.</div></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Tổng tài khoản</div><div class="fs-3 fw-bold">{{ number_format($stats['all']) }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Đang hoạt động</div><div class="fs-3 fw-bold text-success">{{ number_format($stats['active']) }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Đã khóa</div><div class="fs-3 fw-bold text-danger">{{ number_format($stats['locked']) }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Quản trị viên</div><div class="fs-3 fw-bold">{{ number_format($stats['admins']) }}</div></div></div></div>
</div>
<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-lg-5"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Tìm tên, email hoặc số điện thoại..."></div>
        <div class="col-sm-4 col-lg-2"><select class="form-select" name="role"><option value="">Tất cả quyền</option><option value="user" @selected(request('role')==='user')>Người dùng</option><option value="admin" @selected(request('role')==='admin')>Admin</option></select></div>
        <div class="col-sm-4 col-lg-2"><select class="form-select" name="status"><option value="">Tất cả trạng thái</option><option value="active" @selected(request('status')==='active')>Hoạt động</option><option value="locked" @selected(request('status')==='locked')>Đã khóa</option></select></div>
        <div class="col-sm-4 col-lg-3 d-flex gap-2"><button class="btn btn-dark flex-grow-1">Lọc</button><a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Đặt lại</a></div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Người dùng</th><th>Liên hệ</th><th>Quyền</th><th>Trạng thái</th><th class="text-center">Số đơn</th><th>Ngày tạo</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td><div class="fw-semibold">{{ $user->name }}</div><div class="small text-muted">#{{ $user->id }}</div></td>
                <td><div>{{ $user->email }}</div><div class="small text-muted">{{ $user->phone ?: 'Chưa có SĐT' }}</div></td>
                <td><span class="badge {{ $user->role==='admin'?'text-bg-primary':'text-bg-secondary' }}">{{ $user->role==='admin'?'Admin':'Người dùng' }}</span></td>
                <td><span class="badge {{ $user->status==='active'?'text-bg-success':'text-bg-danger' }}">{{ $user->status==='active'?'Hoạt động':'Đã khóa' }}</span></td>
                <td class="text-center">{{ $user->borrow_orders_count }}</td>
                <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                <td class="text-end"><a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-dark">Chi tiết</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Không tìm thấy người dùng.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $users->links() }}
</div></div>
@endsection
