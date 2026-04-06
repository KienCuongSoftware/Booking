<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => __('Đăng nhập Google bị gián đoạn. Vui lòng thử lại.'),
            ]);
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            abort(422, __('Tài khoản Google không cung cấp email.'));
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();
            if ($user) {
                if ($user->google_id !== null && $user->google_id !== $googleUser->getId()) {
                    return redirect()->route('login')->withErrors([
                        'email' => __('Email này đã liên kết tài khoản Google khác.'),
                    ]);
                }
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'name' => $user->name ?: ($googleUser->getName() ?? $email),
                    'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        if (! $user) {
            $user = User::query()->create([
                'name' => $googleUser->getName() ?? strstr($email, '@', true),
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Str::password(32),
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended(route($user->role->redirectRouteAfterAuthentication(), absolute: false));
    }
}
