<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterOtpController extends Controller
{
    public function create(): RedirectResponse|View
    {
        if (! session()->has('register_otp_email')) {
            return redirect()->route('register');
        }

        return view('auth.register-otp', [
            'email' => session('register_otp_email'),
        ]);
    }

    public function store(Request $request, OtpChallengeService $otp): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $otp->verifyRegistrationAndLogin($request->code);

        return redirect()->intended(route($user->role->redirectRouteAfterAuthentication(), absolute: false));
    }

    public function resend(OtpChallengeService $otp): RedirectResponse
    {
        $otp->resendRegistrationOtp();

        return back()->with('status', __('Đã gửi lại mã OTP.'));
    }
}
