<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\BorrowOrder;
use App\Models\User;
use Illuminate\View\View;
class DashboardController extends Controller {
    public function index(): View {
        $stats = [
            'books'=>Book::count(),
            'users'=>User::where('role','user')->count(),
            'orders'=>BorrowOrder::count(),
            'pending'=>BorrowOrder::where('status','pending')->count(),
        ];
        $recentOrders = BorrowOrder::with('user')->latest()->take(8)->get();
        return view('admin.dashboard', compact('stats','recentOrders'));
    }
}
