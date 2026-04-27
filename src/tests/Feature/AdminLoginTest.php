<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    // ミドルウェアを無効化してCSRFチェックなどをスキップ
    use WithoutMiddleware;

    /**
     * ID3-1: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_email_is_required()
    {
        // ルートに合わせて /admin/login に POST
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpass',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $response->assertJsonFragment(['メールアドレスを入力してください']);
    }

    /**
     * ID3-2: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_password_is_required()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $response->assertJsonFragment(['パスワードを入力してください']);
    }

    /**
     * ID3-3: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        // 実際の管理者を想定したデータ作成（もしroleカラムがある場合は追加してください）
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong-admin@example.com',
            'password' => 'admin123',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        // ログイン画面の日本語メッセージに合わせて調整してください
        $response->assertJsonFragment(['ログイン情報が登録されていません']);
    }
}
