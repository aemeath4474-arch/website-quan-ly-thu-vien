<?php
namespace App\Http\Controllers;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class FavoriteController extends Controller {
    public function index(): View {
        $books = Auth::user()->favorites()->with(['category','authors'])->paginate(12);
        return view('user.favorites', compact('books'));
    }
    public function toggle(Book $book): RedirectResponse {
        $user = Auth::user();
        $attached = $user->favorites()->toggle($book->id);
        return back()->with('success', count($attached['attached']) ? 'Đã thêm sách vào yêu thích.' : 'Đã bỏ sách khỏi yêu thích.');
    }
}
