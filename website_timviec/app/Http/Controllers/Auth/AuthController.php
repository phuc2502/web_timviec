<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterEmployeeRequest;
use App\Http\Requests\Auth\RegisterEmployerRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    // LOGIN
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->is_banned) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
        }

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email hoặc mật khẩu không chính xác.']);
        }

        $request->session()->regenerate();

        return $this->redirectAfterLogin(Auth::user());
    }

    // REGISTER
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.register');
    }

    public function showRegisterEmployee(): View
    {
        return view('auth.register-employee');
    }

    public function showRegisterEmployer(): View
    {
        return view('auth.register-employer');
    }

    public function registerEmployee(RegisterEmployeeRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'user_type' => 'employee',
        ]);

        $user->sendEmailVerificationNotification();
        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    public function registerEmployer(RegisterEmployerRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'user_type'    => 'employer',
            'company_name' => $request->company_name,
            'user_trial'   => now()->addDays(7),
        ]);

        $user->sendEmailVerificationNotification();
        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    // LOGOUT
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Bạn đã đăng xuất thành công.');
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        if ($user->user_type === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Chào mừng quay lại, ' . $user->name . '!');
        }

        return redirect()->intended(url('/'))
            ->with('success', 'Đăng nhập thành công! Chào mừng, ' . $user->name . '.');
    }
}
