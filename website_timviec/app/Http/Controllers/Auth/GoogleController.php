<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Redirect to Google OAuth với role đã được chọn trước
     */
    public function redirectWithRole(string $role): RedirectResponse
    {
        Session::put('social_preset_role', $role === 'employer' ? 'employer' : 'employee');
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['email' => 'Đăng nhập Google thất bại. Vui lòng thử lại.']);
        }

        // Tìm user đã tồn tại
        $user = User::where('google_id', $googleUser->getId())->first()
             ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // User đã tồn tại — đăng nhập luôn
            if ($user->is_banned) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tài khoản đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
            }
            $user->update(array_filter([
                'google_id'         => $user->google_id ?: $googleUser->getId(),
                'profile_pic'       => $user->profile_pic ?: $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ]));

            Auth::login($user, true);
            return $this->redirectAfterLogin($user);
        }

        // User mới — lưu thông tin vào session
        Session::put('social_pending', [
            'provider'   => 'google',
            'id'         => $googleUser->getId(),
            'name'       => $googleUser->getName(),
            'email'      => $googleUser->getEmail(),
            'avatar'     => $googleUser->getAvatar(),
        ]);

        // Nếu role đã được chọn trước (từ trang register-employee/employer), dùng luôn
        $presetRole = Session::pull('social_preset_role');
        if ($presetRole) {
            return app(GoogleController::class)->completeRegistration($presetRole);
        }

        return redirect()->route('auth.social.choose-role', ['provider' => 'google']);
    }

    /**
     * Xử lý sau khi user chọn role
     */
    public function completeRegistration(string $role): RedirectResponse
    {
        $pending = Session::get('social_pending');

        if (!$pending || $pending['provider'] !== 'google') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Phiên đăng ký đã hết hạn. Vui lòng thử lại.']);
        }

        $userType = $role === 'employer' ? 'employer' : 'employee';

        $user = User::create([
            'name'              => $pending['name'],
            'email'             => $pending['email'],
            'google_id'         => $pending['id'],
            'profile_pic'       => $pending['avatar'],
            'password'          => bcrypt(Str::random(32)),
            'user_type'         => $userType,
            'email_verified_at' => now(),
        ]);

        Session::forget('social_pending');

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            Log::error('Gửi mail chào mừng Google thất bại cho ' . $user->email . ': ' . $e->getMessage());
        }

        Auth::login($user, true);
        return $this->redirectAfterLogin($user);
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        $greeting = 'Chào mừng, ' . $user->name . '!';

        if ($user->user_type === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', $greeting);
        }

        return redirect()->intended(url('/'))->with('success', $greeting);
    }
}
