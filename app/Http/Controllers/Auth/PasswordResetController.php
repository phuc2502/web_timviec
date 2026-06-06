<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════
    // BƯỚC 1: NHẬP EMAIL
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * GET /forgot-password
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * POST /forgot-password — Gửi link reset về email
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Địa chỉ email không hợp lệ.',
        ]);

        // Luôn trả về thông báo thành công để tránh user enumeration
        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi link đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.'
        );
    }

    // ═══════════════════════════════════════════════════════════════════════
    // BƯỚC 2: ĐẶT LẠI MẬT KHẨU
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * GET /reset-password/{token}
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * POST /reset-password — Đặt lại mật khẩu
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'password.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.letters'   => 'Mật khẩu phải chứa ít nhất 1 chữ cái.',
            'password.numbers'   => 'Mật khẩu phải chứa ít nhất 1 chữ số.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập lại.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
