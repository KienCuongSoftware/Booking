<?php

namespace App\Services;

use App\Enums\OtpChallengeType;
use App\Enums\UserRole;
use App\Mail\OtpCodeMail;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OtpChallengeService
{
    public const EXPIRY_MINUTES = 15;

    public const MAX_ATTEMPTS = 5;

    public function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    public function purposeLabel(OtpChallengeType $type): string
    {
        return match ($type) {
            OtpChallengeType::Register => __('Đăng ký tài khoản'),
            OtpChallengeType::PasswordChange => __('Đổi mật khẩu'),
        };
    }

    public function sendRegistrationChallenge(string $email, string $name, string $plainPassword): OtpChallenge
    {
        OtpChallenge::query()
            ->where('email', $email)
            ->where('type', OtpChallengeType::Register)
            ->delete();

        $code = $this->generateCode();
        $payload = Crypt::encryptString(json_encode([
            'name' => $name,
            'password' => $plainPassword,
        ], JSON_THROW_ON_ERROR));

        $challenge = OtpChallenge::query()->create([
            'email' => $email,
            'user_id' => null,
            'type' => OtpChallengeType::Register,
            'payload' => $payload,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        Mail::to($email)->send(new OtpCodeMail($code, $this->purposeLabel(OtpChallengeType::Register)));

        session([
            'register_otp_email' => $email,
            'register_otp_challenge_id' => $challenge->id,
        ]);

        return $challenge;
    }

    public function verifyRegistrationAndLogin(string $code): User
    {
        $email = session('register_otp_email');
        $challengeId = session('register_otp_challenge_id');

        if (! $email || ! $challengeId) {
            throw ValidationException::withMessages([
                'code' => [__('Phiên đăng ký không hợp lệ. Vui lòng đăng ký lại.')],
            ]);
        }

        $challenge = OtpChallenge::query()
            ->whereKey($challengeId)
            ->where('email', $email)
            ->where('type', OtpChallengeType::Register)
            ->first();

        $this->assertChallengeValid($challenge, $code);

        $data = json_decode(Crypt::decryptString($challenge->payload), true, 512, JSON_THROW_ON_ERROR);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $challenge->email,
            'password' => $data['password'],
            'role' => UserRole::Customer,
            'email_verified_at' => now(),
        ]);

        $challenge->delete();
        session()->forget(['register_otp_email', 'register_otp_challenge_id']);

        event(new Registered($user));
        Auth::login($user);

        return $user;
    }

    public function resendRegistrationOtp(): void
    {
        $email = session('register_otp_email');
        $challengeId = session('register_otp_challenge_id');

        if (! $email || ! $challengeId) {
            throw ValidationException::withMessages([
                'code' => [__('Phiên không hợp lệ.')],
            ]);
        }

        $old = OtpChallenge::query()->find($challengeId);
        if (! $old || $old->type !== OtpChallengeType::Register || $old->email !== $email) {
            throw ValidationException::withMessages([
                'code' => [__('Phiên không hợp lệ.')],
            ]);
        }

        $data = json_decode(Crypt::decryptString($old->payload), true, 512, JSON_THROW_ON_ERROR);
        $old->delete();

        $this->sendRegistrationChallenge($email, $data['name'], $data['password']);
    }

    public function sendPasswordChangeChallenge(User $user, string $plainNewPassword): OtpChallenge
    {
        OtpChallenge::query()
            ->where('user_id', $user->id)
            ->where('type', OtpChallengeType::PasswordChange)
            ->delete();

        $code = $this->generateCode();
        $payload = Crypt::encryptString(json_encode([
            'password' => $plainNewPassword,
        ], JSON_THROW_ON_ERROR));

        $challenge = OtpChallenge::query()->create([
            'email' => $user->email,
            'user_id' => $user->id,
            'type' => OtpChallengeType::PasswordChange,
            'payload' => $payload,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        Mail::to($user->email)->send(new OtpCodeMail($code, $this->purposeLabel(OtpChallengeType::PasswordChange)));

        session([
            'password_otp_challenge_id' => $challenge->id,
        ]);

        return $challenge;
    }

    public function verifyPasswordChange(User $user, string $code): void
    {
        $challengeId = session('password_otp_challenge_id');
        if (! $challengeId) {
            throw ValidationException::withMessages([
                'code' => [__('Không tìm thấy yêu cầu đổi mật khẩu. Vui lòng thử lại.')],
            ]);
        }

        $challenge = OtpChallenge::query()
            ->whereKey($challengeId)
            ->where('user_id', $user->id)
            ->where('type', OtpChallengeType::PasswordChange)
            ->first();

        $this->assertChallengeValid($challenge, $code);

        $data = json_decode(Crypt::decryptString($challenge->payload), true, 512, JSON_THROW_ON_ERROR);

        $user->password = $data['password'];
        $user->save();

        $challenge->delete();
        session()->forget('password_otp_challenge_id');
    }

    public function resendPasswordChangeOtp(User $user): void
    {
        $challengeId = session('password_otp_challenge_id');
        if (! $challengeId) {
            throw ValidationException::withMessages([
                'code' => [__('Không tìm thấy phiên xác minh.')],
            ]);
        }

        $old = OtpChallenge::query()
            ->whereKey($challengeId)
            ->where('user_id', $user->id)
            ->where('type', OtpChallengeType::PasswordChange)
            ->first();

        if (! $old) {
            throw ValidationException::withMessages([
                'code' => [__('Không tìm thấy phiên xác minh.')],
            ]);
        }

        $data = json_decode(Crypt::decryptString($old->payload), true, 512, JSON_THROW_ON_ERROR);
        $old->delete();

        $this->sendPasswordChangeChallenge($user, $data['password']);
    }

    protected function assertChallengeValid(?OtpChallenge $challenge, string $code): void
    {
        if (! $challenge) {
            throw ValidationException::withMessages([
                'code' => [__('Mã không hợp lệ hoặc đã hết hạn.')],
            ]);
        }

        if ($challenge->isExpired()) {
            $challenge->delete();
            throw ValidationException::withMessages([
                'code' => [__('Mã OTP đã hết hạn. Vui lòng gửi lại.')],
            ]);
        }

        if ($challenge->attempts >= self::MAX_ATTEMPTS) {
            $challenge->delete();
            throw ValidationException::withMessages([
                'code' => [__('Đã vượt quá số lần thử. Vui lòng bắt đầu lại.')],
            ]);
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');
            throw ValidationException::withMessages([
                'code' => [__('Mã OTP không đúng.')],
            ]);
        }
    }
}
