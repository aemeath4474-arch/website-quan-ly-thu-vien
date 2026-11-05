@extends('layouts.app')
@section('title','Khám phá sách | Bookhub')
@section('content')
<div class="page-heading"><div class="container"><span class="eyebrow">BOOK COLLECTION</span><h1>Khám phá <em>kho sách</em></h1><p>Tìm kiếm cuốn sách phù hợp với bạn.</p></div></div>
<div class="container section-space pt-4">
<form method="GET" action="{{ route('books.index') }}" class="filter-bar mb-4 p-3">
<div class="row g-3 align-items-end">
<div class="col-lg-4 col-md-6"><label for="q" class="form-label small">Tìm sách</label><input id="q" class="form-control" name="q" value="{{ request('q') }}" placeholder="Tên sách, ISBN, tác giả..."></div>
<div class="col-lg-2 col-md-6"><label for="category" class="form-label small">Thể loại</label><select id="category" class="form-select" name="category"><option value="">Tất cả</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category')==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div class="col-lg-2 col-md-6"><label for="author" class="form-label small">Tác giả</label><select id="author" class="form-select" name="author"><option value="">Tất cả</option>@foreach($authors as $author)<option value="{{ $author->id }}" @selected(request('author')==$author->id)>{{ $author->name }}</option>@endforeach</select></div>
<div class="col-lg-2 col-md-6"><label for="year" class="form-label small">Năm xuất bản</label><input id="year" type="number" min="1000" max="{{ date('Y')+1 }}" class="form-control" name="year" value="{{ request('year') }}" placeholder="Ví dụ: 2024"></div>
<div class="col-lg-2 col-md-6"><label for="sort" class="form-label small">Sắp xếp</label><select id="sort" class="form-select" name="sort"><option value="">Mới nhất</option><option value="price_asc" @selected(request('sort')==='price_asc')>Giá thuê tăng</option><option value="price_desc" @selected(request('sort')==='price_desc')>Giá thuê giảm</option></select></div>
<div class="col-lg-2 col-md-6"><label for="min_price" class="form-label small">Giá thuê từ (đ)</label><input id="min_price" type="number" min="0" step="1000" class="form-control" name="min_price" value="{{ request('min_price') }}"></div>
<div class="col-lg-2 col-md-6"><label for="max_price" class="form-label small">Giá thuê đến (đ)</label><input id="max_price" type="number" min="0" step="1000" class="form-control" name="max_price" value="{{ request('max_price') }}"></div>
<div class="col-lg-3 col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="available" value="1" @checked(request('available'))><span class="form-check-label">Chỉ hiển thị sách còn</span></label></div>
<div class="col-lg-5 col-md-12 d-flex gap-2 justify-content-lg-end"><a href="{{ route('books.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a><button class="btn btn-primary px-4" type="submit">Áp dụng bộ lọc</button></div>
</div></form>
<div class="d-flex justify-content-between align-items-center mb-3"><span class="text-muted small">Tìm thấy {{ $books->total() }} cuốn sách</span></div>
<div class="row g-4">@forelse($books as $book)<div class="col-6 col-md-4 col-lg-3">@include('partials.book-card',['book'=>$book])</div>@empty<div class="col-12 empty-state"><div>⌕</div><h3>Không tìm thấy sách</h3><p>Thử thay đổi từ khóa hoặc bộ lọc.</p><a href="{{ route('books.index') }}" class="btn btn-outline-dark">Xóa bộ lọc</a></div>@endforelse</div>
<div class="mt-5">{{ $books->links() }}</div></div>
@endsection
