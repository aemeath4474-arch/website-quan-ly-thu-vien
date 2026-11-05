@extends('layouts.admin')
@section('title','Thể loại | Bookhub')
@section('admin-content')
<div class="mb-4"><a class="text-muted text-decoration-none" href="{{ route('admin.categories.index') }}">← Quay lại</a><h1 class="admin-title mt-2">{{ $category->exists?'Sửa thể loại':'Thêm thể loại' }}</h1></div>
<form class="card border-0 shadow-sm rounded-4" method="POST" action="{{ $category->exists?route('admin.categories.update',$category):route('admin.categories.store') }}"><div class="card-body p-4">@csrf @if($category->exists) @method('PUT') @endif<label class="form-label">Tên thể loại *</label><input class="form-control mb-3" name="name" value="{{ old('name',$category->name) }}" required><label class="form-label">Mô tả</label><textarea class="form-control" name="description" rows="4">{{ old('description',$category->description) }}</textarea><div class="text-end mt-4"><button class="btn btn-primary px-4">Lưu</button></div></div></form>
@endsection
