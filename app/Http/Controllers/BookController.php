<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with(['category','authors'])->where('status','active');
        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(fn($q) => $q->where('title','like',"%{$term}%")
                ->orWhere('isbn','like',"%{$term}%")
                ->orWhereHas('authors', fn($a) => $a->where('name','like',"%{$term}%")));
        }
        if ($request->filled('category')) $query->where('category_id',$request->category);
        if ($request->filled('author')) $query->whereHas('authors', fn($a) => $a->whereKey($request->integer('author')));
        if ($request->filled('year')) $query->where('publication_year',$request->integer('year'));
        if ($request->filled('min_price') && is_numeric($request->min_price)) $query->where('rental_price','>=',max(0,(float)$request->min_price));
        if ($request->filled('max_price') && is_numeric($request->max_price)) $query->where('rental_price','<=',max(0,(float)$request->max_price));
        if ($request->boolean('available')) $query->where('available_copies','>',0);
        if ($request->sort === 'price_asc') $query->orderBy('rental_price');
        elseif ($request->sort === 'price_desc') $query->orderByDesc('rental_price');
        else $query->latest();
        $books = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $authors = Author::orderBy('name')->get();
        return view('user.books.index', compact('books','categories','authors'));
    }

    public function show(Book $book): View
    {
        abort_unless($book->status === 'active', 404);
        $book->load(['category','authors','reviews.user']);
        $book->loadAvg('reviews', 'rating');
        $relatedBooks = Book::with(['authors','category'])->where('status','active')
            ->where('category_id',$book->category_id)->whereKeyNot($book->id)->take(4)->get();
        return view('user.books.show', compact('book','relatedBooks'));
    }
}
