@auth
@if(auth()->user()->role !== 'admin')
<div class="card border-0 shadow-sm mt-4" id="order-form"><div class="card-body"><h5 class="mb-3">Mượn hoặc thuê cuốn sách này</h5>
<form method="POST" action="{{ route('orders.store', $book) }}">@csrf
<div class="mb-3"><label class="form-label">Hình thức</label><select class="form-select" name="transaction_type" required><option value="borrow">Mượn sách (14 ngày, miễn phí)</option><option value="rent">Thuê sách (7 ngày, {{ number_format($book->rental_price,0,',','.') }} đ/cuốn)</option></select></div>
<div class="mb-3"><label class="form-label">Số lượng</label><input class="form-control" type="number" name="quantity" min="1" max="5" value="1" required><div class="form-text">Còn {{ $book->available_copies }} cuốn. Tối đa 5 cuốn mỗi đơn.</div></div>
<div class="mb-3"><label class="form-label">Ghi chú (không bắt buộc)</label><textarea class="form-control" name="note" rows="2" maxlength="1000"></textarea></div>
<button class="btn btn-primary w-100" @disabled($book->available_copies < 1)>Gửi yêu cầu</button>
</form></div></div>
@endif
@else
<a class="btn btn-primary mt-3" href="{{ route('login') }}">Đăng nhập để mượn / thuê</a>
@endauth
