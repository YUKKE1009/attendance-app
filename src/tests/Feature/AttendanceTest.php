<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // テスト用の一般ユーザーを作成
        $this->user = User::factory()->create();
    }

    /**
     * ID4: 日時取得機能
     * 画面上の日付が現在の日時と一致する
     */
    public function test_current_date_is_displayed_correctly()
    {
        $knownDate = Carbon::create(2026, 4, 27, 10, 0, 0);
        $this->travelTo($knownDate);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        // Bladeの形式「2026年4月27日(月)」が含まれているか
        $response->assertSee('2026年4月27日(月)');
    }

    /**
     * ID5 & 6: ステータス確認と出勤機能
     */
    public function test_clock_in_functional_and_status_changes()
    {
        $this->actingAs($this->user);

        // 1. 最初は「勤務外」
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');

        // 2. 出勤ボタンを押す
        $response = $this->post('/attendance/clock-in');

        // 3. DBに保存されているか
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'status' => '出勤中',
        ]);

        // 4. ステータスが「出勤中」に変わり、退勤・休憩ボタンが出る
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        $response->assertSee('退勤');
        $response->assertSee('休憩入');
    }

    /**
     * ID7: 休憩機能
     */
    public function test_break_in_and_out_functional()
    {
        $this->actingAs($this->user);

        // 出勤済みの状態を作る
        $this->post('/attendance/clock-in');

        // 1. 休憩入
        $this->post('/attendance/break-in');
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        // 2. 休憩戻
        $this->post('/attendance/break-out');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * ID8: 退勤機能
     */
    public function test_clock_out_functional()
    {
        $this->actingAs($this->user);

        // 出勤済みの状態を作る
        $this->post('/attendance/clock-in');

        // 1. 退勤
        $this->post('/attendance/clock-out');

        // 2. ステータスが「退勤済」になり、メッセージが出るか
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');
    }
}
