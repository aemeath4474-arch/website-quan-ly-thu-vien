<?php
namespace App\Services;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookRecommendationService
{
    public function forUser(?int $userId, int $limit = 8): Collection
    {
        $query = Book::with(['category','authors'])->where('status','active')
            ->where('available_copies','>',0);
        if (!$userId) return $query->withAvg('reviews','rating')->orderByDesc('reviews_avg_rating')->take($limit)->get();

        $fav = DB::table('favorites')->join('books','favorites.book_id','=','books.id')
            ->where('favorites.user_id',$userId)->pluck('books.category_id');
        $borrowed = DB::table('borrow_order_items')
            ->join('borrow_orders','borrow_order_items.order_id','=','borrow_orders.id')
            ->join('books','borrow_order_items.book_id','=','books.id')
            ->where('borrow_orders.user_id',$userId)->pluck('books.category_id');
        $categories = $fav->merge($borrowed)->filter()->unique()->values()->all();
        $excluded = DB::table('favorites')->where('user_id',$userId)->pluck('book_id')
            ->merge(DB::table('borrow_order_items')->join('borrow_orders','borrow_order_items.order_id','=','borrow_orders.id')
                ->where('borrow_orders.user_id',$userId)->pluck('book_id'))->unique()->values()->all();
        if ($excluded) $query->whereNotIn('id',$excluded);
        if ($categories) $query->orderByRaw('CASE WHEN category_id IN ('.implode(',',array_fill(0,count($categories),'?')).') THEN 0 ELSE 1 END',$categories);
        return $query->withAvg('reviews','rating')->orderByDesc('reviews_avg_rating')->orderByDesc('id')->take($limit)->get();
    }
}
