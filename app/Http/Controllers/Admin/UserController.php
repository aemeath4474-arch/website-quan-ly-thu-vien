<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowOrder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->withCount('borrowOrders')->latest();

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role') && in_array($request->role, ['user', 'admin'], true)) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status') && in_array($request->status, ['active', 'locked'], true)) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(15)->withQueryString();
        $stats = [
            'all' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'locked' => User::where('status', 'locked')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show(User $user): View
    {
        $user->load(['borrowOrders' => function ($query) {
            $query->with('items.book')->latest()->take(10);
        }]);

        $orderStats = [
            'total' => BorrowOrder::where('user_id', $user->id)->count(),
            'pending' => BorrowOrder::where('user_id', $user->id)->where('status', 'pending')->count(),
            'borrowing' => BorrowOrder::where('user_id', $user->id)->whereIn('status', ['borrowing', 'overdue'])->count(),
            'returned' => BorrowOrder::where('user_id', $user->id)->where('status', 'returned')->count(),
        ];

        return view('admin.users.show', compact('user', 'orderStats'));
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản đang đăng nhập.');
        }

        $newStatus = $user->status === 'active' ? 'locked' : 'active';
        if ($newStatus === 'locked' && $user->role === 'admin') {
            $activeAdmins = User::where('role', 'admin')->where('status', 'active')->count();
            if ($activeAdmins <= 1) {
                return back()->with('error', 'Không thể khóa quản trị viên hoạt động cuối cùng.');
            }
        }

        $user->status = $newStatus;
        $user->save();

        return back()->with('success', $newStatus === 'locked'
            ? 'Đã khóa tài khoản người dùng.'
            : 'Đã mở khóa tài khoản người dùng.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', 'in:user,admin']]);

        if ($request->user()->is($user) && $data['role'] !== 'admin') {
            return back()->with('error', 'Bạn không thể tự hạ quyền quản trị viên của mình.');
        }

        if ($user->role === 'admin' && $data['role'] === 'user') {
            $activeAdmins = User::where('role', 'admin')->where('status', 'active')->count();
            if ($user->status === 'active' && $activeAdmins <= 1) {
                return back()->with('error', 'Không thể hạ quyền quản trị viên hoạt động cuối cùng.');
            }
        }

        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'Đã cập nhật quyền tài khoản.');
    }
}
