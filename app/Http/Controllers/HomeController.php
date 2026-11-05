<?php
namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\Category;
use Illuminate\View\View;
class HomeController extends Controller {
    public function index(): View {
        $featuredBooks = Book::with(['category','authors'])->where('status','active')->latest()->take(8)->get();
        $categories = Category::withCount(['books' => fn($q) => $q->where('status','active')])->orderBy('name')->get();
        $newBooks = Book::with(['category','authors'])->where('status','active')->latest()->take(4)->get();
        $recommendedBooks = app(\App\Services\BookRecommendationService::class)->forUser(auth()->id(), 8);
        return view('user.home', compact('featuredBooks','categories','newBooks','recommendedBooks'));
    }
}
