<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /** GET /user/profile */
    public function show()
    {
        $user         = auth()->user();
        $completeness = $user->profileCompleteness();

        return view('user.profile', compact('user', 'completeness'));
    }

    /** POST /user/profile/employee */
    public function updateEmployee(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'about'            => 'nullable|string|max:2000',
            'profile_pic'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'skills'           => 'nullable|array',
            'skills.*'         => 'string|max:50',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'desired_salary'   => 'nullable|integer|min:0',
            'location'         => 'nullable|string|max:255',
            'job_type_pref'    => 'nullable|in:full-time,part-time,remote,freelance',
        ]);

        // Handle avatar upload
        if ($request->hasFile('profile_pic')) {
            // Delete old
            if ($user->profile_pic && Storage::disk('public')->exists('images/' . $user->profile_pic)) {
                Storage::disk('public')->delete('images/' . $user->profile_pic);
            }
            $file     = $request->file('profile_pic');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('images', $filename, 'public');
            $validated['profile_pic'] = $filename;
        }

        $user->update($validated);

        return back()->with('success', 'Cập nhật hồ sơ thành công!');
    }

    /** POST /user/profile/employer */
    public function updateEmployer(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'about'            => 'nullable|string|max:2000',
            'profile_pic'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'company_name'     => 'nullable|string|max:255',
            'company_logo'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'company_website'  => 'nullable|url|max:255',
            'company_size'     => 'nullable|in:1-10,11-50,51-200,201-500,500+',
        ]);

        // Handle avatar upload
        if ($request->hasFile('profile_pic')) {
            if ($user->profile_pic && Storage::disk('public')->exists('images/' . $user->profile_pic)) {
                Storage::disk('public')->delete('images/' . $user->profile_pic);
            }
            $file     = $request->file('profile_pic');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('images', $filename, 'public');
            $validated['profile_pic'] = $filename;
        }

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            if ($user->company_logo && Storage::disk('public')->exists('images/' . $user->company_logo)) {
                Storage::disk('public')->delete('images/' . $user->company_logo);
            }
            $file     = $request->file('company_logo');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('images', $filename, 'public');
            $validated['company_logo'] = $filename;
        }

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin công ty thành công!');
    }

    /** POST /user/profile/password */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        // Tài khoản OAuth chưa có password → cho đặt mới không cần current_password
        if (is_null($user->password)) {
            $request->validate([
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);
            $user->update(['password' => Hash::make($request->password)]);
            return back()->with('success', 'Đặt mật khẩu thành công!');
        }

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    /** POST /user/mail — Toggle email notifications */
    public function toggleMail(Request $request)
    {
        $user = auth()->user();
        $user->update(['mail' => $request->has('mail')]);

        return back()->with('success', 'Cài đặt thông báo đã được lưu.');
    }

    /** POST /user/notification-settings */
    public function updateNotificationSettings(Request $request)
    {
        $user = auth()->user();
        $user->update([
            'mail'               => $request->boolean('mail'),
            'notify_shortlist'   => $request->boolean('notify_shortlist'),
            'notify_app_status'  => $request->boolean('notify_app_status'),
            'notify_job_alert'   => $request->boolean('notify_job_alert'),
        ]);

        return back()->with('success', 'Cài đặt thông báo đã được lưu.');
    }
}
