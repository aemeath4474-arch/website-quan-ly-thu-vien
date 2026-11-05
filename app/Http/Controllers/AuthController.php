<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\User;
class AuthController extends Controller {
    public function showLogin(): View { return view('auth.login'); }
    public function login(Request $request): RedirectResponse {
        $credentials = $request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email'=>'Email hoặc mật khẩu không chính xác.']);
        }
        $request->session()->regenerate();
        if (Auth::user()->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email'=>'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.']);
        }
        return redirect()->intended(route('home'));
    }
    public function showRegister(): View { return view('auth.register'); }
    public function register(Request $request): RedirectResponse {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'email'=>['required','email','max:255','unique:users,email'],
            'phone'=>['nullable','string','max:20'],
            'password'=>['required','string','min:8','confirmed'],
        ]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('home')->with('success','Tạo tài khoản thành công. Chào mừng bạn đến với Bookhub!');
    }
    public function logout(Request $request): RedirectResponse {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success','Bạn đã đăng xuất.');
    }
}
