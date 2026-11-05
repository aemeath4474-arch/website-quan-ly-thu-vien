<div class="book-card h-100">
    <a class="book-cover" href="{{ route('books.show', $book) }}">
        @if($book->cover_image)
            <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}">
        @else
            <div class="cover-placeholder"><span>BOOKHUB</span><strong>{{ $book->title }}</strong><small>{{ $book->authors->pluck('name')->join(', ') }}</small></div>
        @endif
        @if($book->available_copies > 0)<span class="stock-badge">Còn sách</span>@else<span class="stock-badge out">Tạm hết</span>@endif
    </a>
    <div class="pt-3">
        <div class="book-category">{{ $book->category?->name }}</div>
        <h3 class="book-title"><a href="{{ route('books.show', $book) }}">{{ $book->title }}</a></h3>
        <p class="book-author">{{ $book->authors->pluck('name')->join(', ') ?: 'Chưa cập nhật tác giả' }}</p>
        <div class="d-flex justify-content-between align-items-center mt-3"><span class="book-price">{{ number_format($book->rental_price,0,',','.') }}đ <small>/ lượt</small></span><a class="round-arrow" href="{{ route('books.show',$book) }}">↗</a></div>
    </div>
</div>
