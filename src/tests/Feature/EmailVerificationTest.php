<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID16: 会員登録後、認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * ID16: メール認証誘導画面の表示確認
     */
    public function test_can_view_verification_notice_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 実際の挙動（/email/verify）に合わせて修正
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertRedirect('/email/verify');

        $response = $this->get('/email/verify');
        $response->assertStatus(200);
    }

    /**
     * ID16: メール認証完了後のリダイレクト確認
     */
    public function test_can_verify_email_and_redirect_to_attendance()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // クエリパラメータ（?verified=1）を含めたリダイレクト先を検証
        $response->assertRedirect('/attendance?verified=1');
        
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}