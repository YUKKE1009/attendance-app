<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. 管理者（Admin）の作成
        Admin::create([
            'email' => 'admin@coachtech.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. UI画像にあるスタッフ（User）のリスト
        $users = [
            ['name' => '西 伶奈', 'email' => 'reina.n@coachtech.com'],
            ['name' => '山田 太郎', 'email' => 'taro.y@coachtech.com'],
            ['name' => '増田 一世', 'email' => 'issei.m@coachtech.com'],
            ['name' => '山本 敬吉', 'email' => 'keikichi.y@coachtech.com'],
            ['name' => '秋田 朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西 教夫', 'email' => 'norio.n@coachtech.com'],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
            ]);

            // 💡 実行した瞬間の「今日」から「30日前」を設定
            $now = Carbon::now();
            $startDate = $now->copy()->subDays(30);
            $endDate = $now->copy();

            // 1週間（7日）単位でループ
            for ($weekStart = $startDate->copy(); $weekStart->lte($endDate); $weekStart->addDays(7)) {

                $currentWeekDays = [];
                for ($j = 0; $j < 7; $j++) {
                    $day = $weekStart->copy()->addDays($j);
                    // 未来の日付は作成しない
                    if ($day->gt($endDate)) break;
                    $currentWeekDays[] = $day->format('Y-m-d');
                }

                // 週の中からランダムに2日を「休み」に選ぶ
                $dayCount = count($currentWeekDays);
                if ($dayCount > 0) {
                    $offLimit = $dayCount >= 2 ? 2 : 1;
                    $offDayKeys = (array) array_rand($currentWeekDays, $offLimit);

                    $offDays = [];
                    foreach ($offDayKeys as $key) {
                        $offDays[] = $currentWeekDays[$key];
                    }

                    // 勤怠データの生成
                    foreach ($currentWeekDays as $dayStr) {
                        if (in_array($dayStr, $offDays)) {
                            continue;
                        }

                        $attendance = Attendance::create([
                            'user_id' => $user->id,
                            'date' => $dayStr,
                            'clock_in' => '09:00:00',
                            'clock_out' => '18:00:00',
                            'status' => '退勤済',
                        ]);

                        Rest::create([
                            'attendance_id' => $attendance->id,
                            'break_in' => '12:00:00',
                            'break_out' => '13:00:00',
                        ]);
                    }
                }
            }
        }
    }
}
