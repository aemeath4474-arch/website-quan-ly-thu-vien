@extends('layouts.app')
@section('title', $book->title.' | Bookhub')
@section('content')
<div class="container section-space">
    <div class="mb-4 small text-muted"><a href="{{ route('home') }}">Trang chủ</a> / <a href="{{ route('books.index') }}">Sách</a> / {{ $book->title }}</div>
    <div class="row g-5">
        <div class="col-md-4"><div class="detail-cover">@if($book->cover_image)<img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}">@else<div class="cover-placeholder"><span>BOOKHUB</span><strong>{{ $book->title }}</strong><small>{{ $book->authors->pluck('name')->join(', ') }}</small></div>@endif</div></div>
        <div class="col-md-8">
            <span class="eyebrow">{{ $book->category?->name }}</span><h1 class="detail-title">{{ $book->title }}</h1><p class="lead text-muted">{{ $book->authors->pluck('name')->join(', ') }}</p>
            <div class="d-flex flex-wrap gap-4 my-4"><div><small class="text-muted d-block">Giá bìa</small><strong>{{ number_format($book->price,0,',','.') }}đ</strong></div><div><small class="text-muted d-block">Giá thuê</small><strong class="text-primary fs-5">{{ number_format($book->rental_price,0,',','.') }}đ</strong></div><div><small class="text-muted d-block">Tình trạng</small><strong>{{ $book->available_copies > 0 ? 'Còn '.$book->available_copies.' cuốn' : 'Tạm hết' }}</strong></div><div><small class="text-muted d-block">Đánh giá</small><strong class="text-warning">★ {{ number_format((float)($book->reviews_avg_rating ?? 0),1) }}/5</strong> <small class="text-muted">({{ $book->reviews->count() }})</small></div></div>
            <div class="detail-actions">
                @auth<form method="POST" action="{{ route('favorites.toggle',$book) }}">@csrf<button class="btn btn-outline-dark rounded-pill px-4">♡ Yêu thích</button></form>@else<a class="btn btn-outline-dark rounded-pill px-4" href="{{ route('login') }}">♡ Đăng nhập để lưu</a>@endauth
                <a class="btn btn-primary rounded-pill px-4" href="#order-form">Mượn / thuê sách</a>
            </div>
            @include('user.books._order-form')
            <hr class="my-4"><h5>Thông tin sách</h5><div class="row g-3 small"><div class="col-sm-6"><span class="text-muted">ISBN:</span> {{ $book->isbn }}</div><div class="col-sm-6"><span class="text-muted">Nhà xuất bản:</span> {{ $book->publisher ?: 'Chưa cập nhật' }}</div><div class="col-sm-6"><span class="text-muted">Năm xuất bản:</span> {{ $book->publication_year ?: 'Chưa cập nhật' }}</div><div class="col-sm-6"><span class="text-muted">Thể loại:</span> {{ $book->category?->name }}</div></div>
            <h5 class="mt-4">Giới thiệu</h5><p class="text-muted lh-lg">{{ $book->description ?: 'Thông tin giới thiệu đang được cập nhật.' }}</p>
        </div>
    </div>

    <section class="review-section mt-5" id="reviews">
        <div class="section-heading"><div><span class="eyebrow">READER REVIEWS</span><h2>Đánh giá từ bạn đọc</h2><p>Điểm trung bình {{ number_format((float)($book->reviews_avg_rating ?? 0),1) }}/5 · {{ $book->reviews->count() }} lượt đánh giá</p></div></div>
        @auth
            @if(auth()->user()->role !== 'admin')
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><h3 class="h6 mb-3">Chia sẻ cảm nhận của bạn</h3>
                <form method="POST" action="{{ route('reviews.store',$book) }}">@csrf
                    <div class="row g-3"><div class="col-md-3"><label class="form-label" for="rating">Số sao</label><select class="form-select" name="rating" id="rating" required><option value="">Chọn đánh giá</option>@for($i=5;$i>=1;$i--)<option value="{{ $i }}" @selected((string)old('rating')===(string)$i)>{{ $i }} sao</option>@endfor</select></div><div class="col-md-9"><label class="form-label" for="comment">Nhận xét</label><textarea class="form-control" id="comment" name="comment" rows="2" maxlength="2000" placeholder="Cuốn sách này có gì thú vị?">{{ old('comment') }}</textarea></div></div>
                    <button class="btn btn-primary mt-3">Gửi đánh giá</button><div class="form-text">Mỗi tài khoản có một đánh giá cho mỗi cuốn sách; gửi lại sẽ cập nhật đánh giá cũ.</div>
                </form></div></div>
            @endif
        @else
            <p class="text-muted">Vui lòng <a href="{{ route('login') }}">đăng nhập</a> để gửi đánh giá.</p>
        @endauth
        <div class="row g-3">
        @forelse($book->reviews->sortByDesc('created_at') as $review)
            <div class="col-12"><article class="review-card"><div class="d-flex flex-wrap justify-content-between gap-2"><div><strong>{{ $review->user?->name ?? 'Bạn đọc Bookhub' }}</strong><div class="text-warning">{{ str_repeat('★',(int)$review->rating) }}<span class="text-muted">{{ str_repeat('☆',5-(int)$review->rating) }}</span></div></div><small class="text-muted">{{ $review->created_at?->format('d/m/Y') }}</small></div>@if($review->comment)<p class="mb-0 mt-2 text-muted">{{ $review->comment }}</p>@endif
                @auth @if(auth()->id()===$review->user_id)<form method="POST" action="{{ route('reviews.destroy',$review) }}" class="mt-2" onsubmit="return confirm('Xóa đánh giá này?')">@csrf @method('DELETE')<button class="btn btn-link btn-sm text-danger p-0">Xóa đánh giá của tôi</button></form>@endif @endauth
            </article></div>
        @empty<div class="col-12"><div class="empty-state py-4"><div>☆</div><h3>Chưa có đánh giá</h3><p class="text-muted">Hãy là người đầu tiên chia sẻ cảm nhận về cuốn sách này.</p></div></div>@endforelse
        </div>
    </section>

    @if($relatedBooks->isNotEmpty())<div class="section-heading mt-5"><div><span class="eyebrow">YOU MAY ALSO LIKE</span><h2>Sách cùng thể loại</h2></div></div><div class="row g-4">@foreach($relatedBooks as $related)<div class="col-6 col-md-3">@include('partials.book-card',['book'=>$related])</div>@endforeach</div>@endif
</div>
@endsection
