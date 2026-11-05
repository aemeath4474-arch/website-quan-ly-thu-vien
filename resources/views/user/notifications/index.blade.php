@extends('layouts.app')
@section('title', 'Thông báo | Bookhub')
@section('content')
<div class="page-heading"><div class="container"><span class="eyebrow">BOOKHUB UPDATES</span><h1>Thông báo</h1><p>Cập nhật về đơn mượn, thuê và thời hạn trả sách của bạn.</p></div></div>
<div class="container section-space">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div class="text-muted small">{{ $notifications->total() }} thông báo</div>
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="btn btn-outline-dark btn-sm" type="submit">Đánh dấu tất cả đã đọc</button></form>
    </div>
    <div class="notification-list">
    @forelse($notifications as $notification)
        <article class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
            <div class="notification-symbol">@if($notification->type === 'overdue') ⚠ @elseif($notification->type === 'due_soon') ◷ @else ✓ @endif</div>
            <div class="flex-grow-1"><div class="d-flex flex-wrap align-items-center gap-2"><h2 class="h6 mb-0">{{ $notification->title }}</h2>@if(!$notification->read_at)<span class="badge text-bg-success">Mới</span>@endif</div><p class="mb-1 mt-2 text-muted">{{ $notification->message }}</p><small class="text-muted">{{ \Carbon\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}</small></div>
            @if(!$notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success" type="submit">Đã đọc</button></form>@endif
        </article>
    @empty
        <div class="empty-state"><div>♧</div><h3>Bạn chưa có thông báo</h3><p class="text-muted">Thông báo về đơn hàng và hạn trả sách sẽ xuất hiện tại đây.</p></div>
    @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
