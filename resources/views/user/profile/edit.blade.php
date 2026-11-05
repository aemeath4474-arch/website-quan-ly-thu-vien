@extends('layouts.app')
@section('title','Hồ sơ cá nhân | Bookhub')
@section('content')
<div class="page-heading"><div class="container"><span class="eyebrow">MY ACCOUNT</span><h1>Hồ sơ <em>cá nhân</em></h1><p>Quản lý tài khoản và bảo mật đăng nhập.</p></div></div>
<div class="container section-space pt-4"><div class="row g-4">
<div class="col-lg-7"><div class="card border-0 shadow-sm p-4"><h4 class="mb-4">Thông tin cá nhân</h4><form method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')
<div class="mb-3"><label class="form-label" for="name">Họ và tên</label><input id="name" name="name" class="form-control" required maxlength="100" value="{{ old('name',$user->name) }}"></div>
<div class="mb-3"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="150" value="{{ old('email',$user->email) }}"></div>
<div class="mb-3"><label class="form-label" for="phone">Số điện thoại</label><input id="phone" name="phone" class="form-control" maxlength="20" value="{{ old('phone',$user->phone) }}"></div>
<div class="mb-3"><label class="form-label" for="address">Địa chỉ</label><textarea id="address" name="address" class="form-control" rows="2" maxlength="255">{{ old('address',$user->address) }}</textarea></div>
<button class="btn btn-primary px-4">Lưu thay đổi</button></form></div></div>
<div class="col-lg-5"><div class="card border-0 shadow-sm p-4"><h4 class="mb-4">Đổi mật khẩu</h4><form method="POST" action="{{ route('profile.password') }}">@csrf @method('PATCH')
<div class="mb-3"><label for="current_password" class="form-label">Mật khẩu hiện tại</label><input id="current_password" type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
<div class="mb-3"><label for="password" class="form-label">Mật khẩu mới (ít nhất 8 ký tự)</label><input id="password" type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password"></div>
<div class="mb-3"><label for="password_confirmation" class="form-label">Nhập lại mật khẩu mới</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
<button class="btn btn-outline-dark">Đổi mật khẩu</button></form></div></div>
</div></div>
@endsection
