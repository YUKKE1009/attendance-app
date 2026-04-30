<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * ID: 現在の日付が正しく表示されるか
     */
    public function test_current_date_is_displayed_correctly()
    {
        // 1. 固定ではなく「実行時の現在時刻」を取得
        $now = Carbon::now();

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        
        $content = $response->getContent();

        // 2. 現在の「年」「月」「日」が画面に含まれているか検証
        $this->assertStringContainsString($now->format('Y'), $content, "年が表示されていません");
        
        // 月のチェック（4月なら "4" または "04"）
        $month = $now->format('n');
        $monthZero = $now->format('m');
        $hasMonth = str_contains($content, $month) || str_contains($content, $monthZero);
        $this->assertTrue($hasMonth, "月が表示されていません");

        // 日のチェック（30日なら "30"）
        $day = $now->format('j');
        $dayZero = $now->format('d');
        $hasDay = str_contains($content, $day) || str_contains($content, $dayZero);
        $this->assertTrue($hasDay, "日が表示されていません");
    }

    public function test_clock_in_functional_and_status_changes()
    {
        $response = $this->actingAs($this->user)->post('/attendance/clock-in');
        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_break_in_and_out_functional()
    {
        $this->actingAs($this->user)->post('/attendance/clock-in');
        $this->actingAs($this->user)->post('/attendance/break-in');
        $response = $this->actingAs($this->user)->post('/attendance/break-out');
        $response->assertRedirect();
    }

    public function test_clock_out_functional()
    {
        $this->actingAs($this->user)->post('/attendance/clock-in');
        $response = $this->actingAs($this->user)->post('/attendance/clock-out');
        $response->assertRedirect();
    }
}
