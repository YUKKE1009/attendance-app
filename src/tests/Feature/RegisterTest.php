<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    /**
     * ID1-1: 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        $response->assertJsonFragment(['お名前を入力してください']);
    }

    /**
     * ID1-2: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $response->assertJsonFragment(['メールアドレスを入力してください']);
    }

    /**
     * ID1-3: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $response->assertJsonFragment(['パスワードを入力してください']);
    }

    /**
     * ID1-4: パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_too_short()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '1234567', // 7文字
            'password_confirmation' => '1234567',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $response->assertJsonFragment(['パスワードは8文字以上で入力してください']);
    }

    /**
     * ID1-5: パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_confirmation_fails()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $response->assertJsonFragment(['パスワードと一致しません']);
    }

    /**
     * ID1-6: フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => 'テスト太郎',
            'email' => 'valid@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // 成功時はリダイレクト（302）が発生する
        $response->assertStatus(302);

        // データベースにユーザーが保存されているか確認
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'valid@example.com',
        ]);
    }
}
