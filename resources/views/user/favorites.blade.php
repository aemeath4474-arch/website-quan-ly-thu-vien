@extends('layouts.app')
@section('title','Sách yêu thích | Bookhub')
@section('content')
<div class="page-heading"><div class="container"><span class="eyebrow">YOUR READING LIST</span><h1>Sách <em>yêu thích</em></h1><p>Những cuốn sách bạn đã lưu lại.</p></div></div>
<div class="container section-space pt-4"><div class="row g-4">@forelse($books as $book)<div class="col-6 col-md-4 col-lg-3">@include('partials.book-card',['book'=>$book])<form method="POST" action="{{ route('favorites.toggle',$book) }}" class="mt-2">@csrf<button class="btn btn-sm btn-outline-secondary w-100">Bỏ yêu thích</button></form></div>@empty<div class="col-12 empty-state"><div>♡</div><h3>Danh sách còn trống</h3><p>Hãy lưu những cuốn sách bạn muốn đọc.</p><a class="btn btn-primary" href="{{ route('books.index') }}">Khám phá sách</a></div>@endforelse</div><div class="mt-5">{{ $books->links() }}</div></div>
@endsection
