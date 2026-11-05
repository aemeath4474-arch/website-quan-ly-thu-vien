@extends('layouts.admin')
@section('title','Tác giả | Bookhub')
@section('admin-content')
<div class="mb-4"><a class="text-muted text-decoration-none" href="{{ route('admin.authors.index') }}">← Quay lại</a><h1 class="admin-title mt-2">{{ $author->exists?'Sửa tác giả':'Thêm tác giả' }}</h1></div>
<form class="card border-0 shadow-sm rounded-4" method="POST" action="{{ $author->exists?route('admin.authors.update',$author):route('admin.authors.store') }}"><div class="card-body p-4">@csrf @if($author->exists) @method('PUT') @endif<label class="form-label">Tên tác giả *</label><input class="form-control mb-3" name="name" value="{{ old('name',$author->name) }}" required><label class="form-label">Tiểu sử</label><textarea class="form-control" name="biography" rows="5">{{ old('biography',$author->biography) }}</textarea><div class="text-end mt-4"><button class="btn btn-primary px-4">Lưu</button></div></div></form>
@endsection
