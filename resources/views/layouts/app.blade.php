<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bookhub - Không gian tri thức')</title>
    <meta name="description" content="Bookhub - Khám phá, mượn và thuê sách trực tuyến.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/bookhub.css').'?v=7' }}" rel="stylesheet">@stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom"><div class="container">
    <a class="navbar-brand brand" href="{{ route('home') }}"><span class="brand-mark">B</span> bookhub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav mx-auto gap-lg-3">
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Trang chủ</a></li><li class="nav-item"><a class="nav-link" href="{{ route('books.index') }}">Khám phá sách</a></li>
        @auth<li class="nav-item"><a class="nav-link" href="{{ route('favorites.index') }}">Yêu thích</a></li><li class="nav-item"><a class="nav-link" href="{{ route('my-orders.index') }}">Đơn của tôi</a></li><li class="nav-item"><a class="nav-link" href="{{ route('notifications.index') }}">Thông báo @php($unreadCount=auth()->user()->bookNotifications()->whereNull('read_at')->count()) @if($unreadCount)<span class="badge rounded-pill text-bg-danger">{{ $unreadCount }}</span>@endif</a></li>@endauth
    </ul><div class="d-flex flex-wrap align-items-center gap-2">
        @auth @if(auth()->user()->role === 'admin')<a class="btn btn-outline-dark btn-sm" href="{{ route('admin.dashboard') }}">Quản trị</a>@endif<a class="small fw-semibold text-dark text-decoration-none" href="{{ route('profile.edit') }}" title="Chỉnh sửa hồ sơ">Xin chào, {{ auth()->user()->name }}</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-dark btn-sm">Đăng xuất</button></form>
        @else<a class="btn btn-link text-dark text-decoration-none" href="{{ route('login') }}">Đăng nhập</a><a class="btn btn-primary rounded-pill px-4" href="{{ route('register') }}">Đăng ký</a>@endauth
    </div></div>
</div></nav>
@if(session('success'))<div class="container mt-3"><div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button></div></div>@endif
@if(session('error'))<div class="container mt-3"><div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button></div></div>@endif
@if($errors->any())<div class="container mt-3"><div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
<main>@yield('content')</main>
<footer class="site-footer mt-5"><div class="container py-5"><div class="row g-4"><div class="col-md-5"><a class="navbar-brand brand text-white" href="{{ route('home') }}"><span class="brand-mark">B</span> bookhub</a><p class="text-white-50 mt-3 mb-0">Mở ra thế giới tri thức, từng trang sách một.</p></div><div class="col-md-3"><h6>Khám phá</h6><a href="{{ route('books.index') }}">Tất cả sách</a>@auth<a href="{{ route('favorites.index') }}">Sách yêu thích</a>@endauth</div><div class="col-md-4"><h6>Bookhub</h6><p class="text-white-50">Nền tảng quản lý và kết nối bạn đọc với những cuốn sách hay.</p></div></div><hr class="border-secondary mt-4"><div class="small text-white-50">© {{ date('Y') }} Bookhub. All rights reserved.</div></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>@stack('scripts')
</body></html>
