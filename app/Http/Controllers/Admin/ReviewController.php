<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with(['user', 'book'])->latest();
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('comment', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                ->orWhereHas('book', fn ($b) => $b->where('title', 'like', "%{$term}%")));
        }
        if ($request->filled('rating') && in_array((int) $request->rating, [1,2,3,4,5], true)) {
            $query->where('rating', (int) $request->rating);
        }
        $reviews = $query->paginate(15)->withQueryString();
        $stats = ['all'=>Review::count(), 'average'=>round((float) Review::avg('rating'), 1), 'low'=>Review::where('rating','<=',2)->count()];
        return view('admin.reviews.index', compact('reviews','stats'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();
        return back()->with('success', 'Đã xóa đánh giá khỏi hệ thống.');
    }
}
