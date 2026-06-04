<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterCandidateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showRegisterCandidate()
    {
        return view('user.tim-register');
    }

    public function registerCandidate(RegisterCandidateRequest $request)
    {
        try {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'user_type' => 'employee',  // ← Dùng 'employee' để khớp DashboardController & toàn bộ hệ thống
            ]);

            $user->email_verified_at = now();
            $user->save();

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Đăng ký thành công! Bạn đã được tặng 5 lượt ứng tuyển miễn phí.');

        } catch (\Throwable $e) {
            Log::error('Register candidate failed: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi. Vui lòng thử lại.')->withInput();
        }
    }

    public function showRegisterEmployer()
    {
        return view('user.employer-register');
    }

    public function registerEmployer(Request $request)
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['required', 'string', 'max:255'],
        ], [
            'name.required'         => 'Vui lòng nhập họ tên.',
            'email.unique'          => 'Email này đã được sử dụng.',
            'password.confirmed'    => 'Xác nhận mật khẩu không khớp.',
            'password.min'          => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'company_name.required' => 'Vui lòng nhập tên công ty.',
        ]);

        try {
            $user = User::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'password'     => Hash::make($request->password),
                'user_type'    => 'employer',
                'company_name' => $request->company_name,
            ]);

            $user->email_verified_at = now();
            $user->save();

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Đăng ký tài khoản nhà tuyển dụng thành công!');

        } catch (\Throwable $e) {
            Log::error('Register employer failed: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi. Vui lòng thử lại.')->withInput();
        }
    }

    public function showLogin()
    {
        return view('user.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->is_banned) {
                Auth::logout();
                return back()->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email hoặc mật khẩu không đúng.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Bạn đã đăng xuất thành công.');
    }
}