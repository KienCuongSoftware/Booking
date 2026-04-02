<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordOtpVerificationController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        if (! session()->has('password_otp_challenge_id')) {
            return redirect()->route('profile.edit');
        }

        return view('auth.password-otp', [
            'email' => $request->user()->email,
        ]);
    }

    public function store(Request $request, OtpChallengeService $otp): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $otp->verifyPasswordChange($request->user(), $request->code);

        return redirect()->route('profile.edit')->with('status', 'password-updated');
    }

    public function resend(Request $request, OtpChallengeService $otp): RedirectResponse
    {
        $otp->resendPasswordChangeOtp($request->user());

        return back()->with('status', __('Đã gửi lại mã OTP.'));
    }
}
