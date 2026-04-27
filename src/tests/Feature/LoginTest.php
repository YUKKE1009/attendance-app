<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    /**
     * ID2-1: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        // メッセージ内容はLoginRequest（またはFortifyの言語ファイル）に合わせてください
        $response->assertJsonFragment(['メールアドレスを入力してください']);
    }

    /**
     * ID2-2: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $response->assertJsonFragment(['パスワードを入力してください']);
    }

    /**
     * ID2-3: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_with_invalid_credentials()
    {
        // 1. ユーザーを一人登録しておく（テスト用データ）
        $user = User::factory()->create([
            'email' => 'correct@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. 誤ったメールアドレスでログインを試みる
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ], ['Accept' => 'application/json']);

        // 3. 期待する結果：バリデーションエラー（または認証エラー）が返ること
        $response->assertStatus(422);
        // 通常、認証失敗は email または password キーにエラーが入ります
        $response->assertJsonValidationErrors(['email']);
        $response->assertJsonFragment(['ログイン情報が登録されていません']);
    }
}
