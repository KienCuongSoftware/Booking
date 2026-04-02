<?php

namespace Tests\Feature\Auth;

use App\Mail\OtpCodeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_after_otp_verification(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertRedirect(route('register.verify'));

        $this->assertGuest();

        $code = null;
        Mail::assertSent(OtpCodeMail::class, function (OtpCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->post('/register/verify', ['code' => $code])
            ->assertRedirect(route('customer.dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
