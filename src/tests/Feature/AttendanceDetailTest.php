<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'テスト太郎']);
    }

    /**
     * ID9: 勤怠一覧情報取得機能（一般ユーザー）
     */
    public function test_user_can_see_own_attendance_list()
    {
        // 1. 今月の勤怠データを作成
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        // 2. 勤怠一覧ページへアクセス
        $response = $this->actingAs($this->user)->get('/attendance/list');

        // 3. 自分の名前や勤怠データが表示されているか
        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m')); // 現在の月
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * ID10: 勤怠詳細情報取得機能
     */
    public function test_attendance_detail_page_displays_correct_data()
    {
        // Viewに空のエラーバッグを注入して $errors 未定義エラーを防ぐ
        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag);

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-05-01',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => '退勤済',
            'remarks' => 'テスト備考',
        ]);

        \App\Models\Rest::create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->actingAs($this->user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('2026');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /**
     * ID11: 勤怠詳細情報修正機能（バリデーション & 申請）
     */
    public function test_attendance_update_request_validation_and_storage()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-05-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $this->actingAs($this->user);

        // 1. バリデーション：備考欄が未入力の場合
        $response = $this->post("/attendance/detail/{$attendance->id}", [
            'remarks' => '', // 未入力
        ], ['Accept' => 'application/json']);

        $response->assertJsonValidationErrors(['remarks']);
        $response->assertJsonFragment(['備考を記入してください']);

        // 2. 正常な修正申請の送信
        $response = $this->post("/attendance/detail/{$attendance->id}", [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '電車遅延のため',
        ]);

        // 3. 修正申請テーブル（correction_requests）に保存されたか
        $this->assertDatabaseHas('correction_requests', [
            'user_id' => $this->user->id,
            'remark' => '電車遅延のため',
        ]);

        // 4. 勤怠本体のステータスが「承認待ち」になったか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '承認待ち',
        ]);
    }
}
