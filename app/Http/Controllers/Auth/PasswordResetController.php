<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi link đặt lại mật khẩu. Link có hiệu lực trong 5 phút. Vui lòng kiểm tra hộp thư.'
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

        // ── Kiểm tra mật khẩu mới không được trùng mật khẩu cũ ──────────────
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['password' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại. Vui lòng chọn mật khẩu khác.']);
        }
        // ─────────────────────────────────────────────────────────────────────

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

        // Xử lý lỗi token hết hạn (5 phút)
        if ($status === Password::EXPIRED_TOKEN) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Link đặt lại mật khẩu đã hết hạn (quá 5 phút). Vui lòng yêu cầu link mới.']);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
