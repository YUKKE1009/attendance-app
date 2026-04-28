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
        $this->user = User::factory()->create();
    }

    /**
     * ID4: 現在の日付の表示
     */
    public function test_current_date_is_displayed_correctly()
    {
        // 実行時の今日の日付（2026-04-29）に固定
        $knownDate = Carbon::create(2026, 4, 29, 10, 0, 0);
        $this->travelTo($knownDate);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        // 表示が「2026年4月29日(水)」であることを確認
        $response->assertSee(now()->isoFormat('YYYY年M月D日(dd)'));
    }

    /**
     * ID5 & 6: 出勤機能およびステータス変更の確認
     */
    public function test_clock_in_functional_and_status_changes()
    {
        $this->actingAs($this->user);

        $response = $this->get('/attendance');
        $response->assertSee('勤務外');

        $this->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'status' => '出勤中',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * ID7: 休憩入・休憩戻機能の確認
     */
    public function test_break_in_and_out_functional()
    {
        $this->actingAs($this->user);
        $this->post('/attendance/clock-in');

        $this->post('/attendance/break-in');
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');

        $this->post('/attendance/break-out');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * ID8: 退勤機能の確認
     */
    public function test_clock_out_functional()
    {
        $this->actingAs($this->user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}
