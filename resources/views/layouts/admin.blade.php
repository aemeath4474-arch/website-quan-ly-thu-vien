@extends('layouts.app')
@push('styles')<link href="{{ asset('css/admin.css').'?v=7' }}" rel="stylesheet">@endpush
@section('content')
<div class="container-fluid px-lg-4 py-4 admin-layout">
    <div class="row g-4">
        <aside class="col-12 col-lg-3 col-xl-2"><nav class="admin-sidebar" aria-label="Menu quản trị">
            <div class="small text-uppercase fw-bold text-muted mb-3">Quản lý Bookhub</div>
            <a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}"><span>▦</span> Tổng quan</a>
            <a class="{{ request()->routeIs('admin.books.*')?'active':'' }}" href="{{ route('admin.books.index') }}"><span>▤</span> Quản lý sách</a>
            <a class="{{ request()->routeIs('admin.categories.*')?'active':'' }}" href="{{ route('admin.categories.index') }}"><span>▧</span> Thể loại</a>
            <a class="{{ request()->routeIs('admin.authors.*')?'active':'' }}" href="{{ route('admin.authors.index') }}"><span>✎</span> Tác giả</a>
            <a class="{{ request()->routeIs('admin.orders.*')?'active':'' }}" href="{{ route('admin.orders.index') }}"><span>▣</span> Đơn mượn / thuê</a>
            <a class="{{ request()->routeIs('admin.payments.*')?'active':'' }}" href="{{ route('admin.payments.index') }}"><span>₫</span> Thanh toán / phí phạt</a>
            <a class="{{ request()->routeIs('admin.users.*')?'active':'' }}" href="{{ route('admin.users.index') }}"><span>♙</span> Quản lý người dùng</a>
            <a class="{{ request()->routeIs('admin.reviews.*')?'active':'' }}" href="{{ route('admin.reviews.index') }}"><span>★</span> Đánh giá sách</a>
            <div class="admin-sidebar-divider"></div>
            <a href="{{ route('home') }}"><span>↗</span> Xem website</a>
        </nav></aside>
        <section class="col-12 col-lg-9 col-xl-10 admin-main">
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @yield('admin-content')
        </section>
    </div>
</div>
@endsection
