<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Gửi OTP để xác nhận đổi mật khẩu (áp dụng sau khi nhập mật khẩu mới trên trang profile).
     */
    public function update(Request $request, OtpChallengeService $otp): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $otp->sendPasswordChangeChallenge($request->user(), $validated['password']);

        return redirect()->route('password.otp.show')
            ->with('status', __('Mã OTP đã được gửi tới email của bạn. Nhập mã để hoàn tất đổi mật khẩu.'));
    }
}
