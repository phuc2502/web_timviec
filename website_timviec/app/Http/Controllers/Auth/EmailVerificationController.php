<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * GET /email/verify — Trang thông báo "chưa xác thực"
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(url('/'));
        }

        return view('auth.verify-email', [
            'user' => $request->user(),
        ]);
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(url('/') . '?verified=1');
        }

        $request->fulfill();

        return redirect(url('/'))
            ->with('success', '🎉 Email đã được xác thực thành công! Chào mừng bạn đến với ITWorks.');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(url('/'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Email xác thực đã được gửi lại. Vui lòng kiểm tra hộp thư (kể cả Spam).');
    }
}
