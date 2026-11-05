<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        abort_unless($book->status === 'active', 404);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.between' => 'Số sao phải từ 1 đến 5.',
            'comment.max' => 'Nội dung đánh giá không được vượt quá 2.000 ký tự.',
        ]);

        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'book_id' => $book->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        return back()->with('success', 'Đã lưu đánh giá của bạn.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless((int) $review->user_id === (int) $request->user()->id, 403);
        $review->delete();
        return back()->with('success', 'Đã xóa đánh giá của bạn.');
    }
}
