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

class GithubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Redirect to GitHub OAuth với role đã được chọn trước
     */
    public function redirectWithRole(string $role): RedirectResponse
    {
        Session::put('social_preset_role', $role === 'employer' ? 'employer' : 'employee');
        return Socialite::driver('github')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            Log::error('GitHub OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['email' => 'Đăng nhập GitHub thất bại. Vui lòng thử lại.']);
        }

        // GitHub có thể không trả về email (private email setting)
        $email = $githubUser->getEmail();
        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Không thể lấy email từ GitHub. Hãy bật public email trong cài đặt GitHub.']);
        }

        // Tìm user đã tồn tại
        $user = User::where('github_id', $githubUser->getId())->first()
             ?? User::where('email', $email)->first();

        if ($user) {
            // User đã tồn tại — đăng nhập luôn
            if ($user->is_banned) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tài khoản đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
            }
            $user->update(array_filter([
                'github_id'         => $user->github_id ?: $githubUser->getId(),
                'profile_pic'       => $user->profile_pic ?: $githubUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ]));

            Auth::login($user, true);
            return $this->redirectAfterLogin($user);
        }

        // User mới — lưu thông tin vào session
        Session::put('social_pending', [
            'provider'   => 'github',
            'id'         => $githubUser->getId(),
            'name'       => $githubUser->getName() ?? $githubUser->getNickname(),
            'email'      => $email,
            'avatar'     => $githubUser->getAvatar(),
        ]);

        // Nếu role đã được chọn trước (từ trang register-employee/employer), dùng luôn
        $presetRole = Session::pull('social_preset_role');
        if ($presetRole) {
            return app(GithubController::class)->completeRegistration($presetRole);
        }

        return redirect()->route('auth.social.choose-role', ['provider' => 'github']);
    }

    /**
     * Xử lý sau khi user chọn role
     */
    public function completeRegistration(string $role): RedirectResponse
    {
        $pending = Session::get('social_pending');

        if (!$pending || $pending['provider'] !== 'github') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Phiên đăng ký đã hết hạn. Vui lòng thử lại.']);
        }

        $userType = $role === 'employer' ? 'employer' : 'employee';

        $user = User::create([
            'name'              => $pending['name'],
            'email'             => $pending['email'],
            'github_id'         => $pending['id'],
            'profile_pic'       => $pending['avatar'],
            'password'          => bcrypt(Str::random(32)),
            'user_type'         => $userType,
            'email_verified_at' => now(),
        ]);

        Session::forget('social_pending');

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            Log::error('Gửi mail chào mừng GitHub thất bại: ' . $e->getMessage());
        }

        Auth::login($user, true);
        return $this->redirectAfterLogin($user);
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        return redirect()->intended(url('/'))
            ->with('success', 'Chào mừng, ' . $user->name . '!');
    }
}
