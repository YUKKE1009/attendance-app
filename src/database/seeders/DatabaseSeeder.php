<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

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

            // 実行した瞬間の「現在時刻」を基準にする
            $now = \Carbon\Carbon::now();

            // その月の1日 00:00:00
            $startDate = $now->copy()->startOfMonth();

            // その月の末日 23:59:59
            $endDate = $now->copy()->endOfMonth();

            // $date を 7日ずつ進める
            for ($weekStart = $startDate->copy(); $weekStart->lte($endDate); $weekStart->addDays(7)) {

                // 1. この1週間（最大7日間）の日付リストを作成
                $currentWeekDays = [];
                for ($j = 0; $j < 7; $j++) {
                    $day = $weekStart->copy()->addDays($j);
                    if ($day->gt($endDate)) break;
                    $currentWeekDays[] = $day->format('Y-m-d');
                }

                // 2. この中からランダムに2日を「休み」に選ぶ
                $dayCount = count($currentWeekDays);
                $offLimit = $dayCount >= 2 ? 2 : 1;
                $offDayKeys = (array) array_rand($currentWeekDays, $offLimit);

                $offDays = [];
                foreach ($offDayKeys as $key) {
                    $offDays[] = $currentWeekDays[$key];
                }

                // 3. 実際にデータを作成
                foreach ($currentWeekDays as $dayStr) {
                    if (in_array($dayStr, $offDays)) {
                        continue; // 選ばれた休みの日ならスキップ
                    }

                    // 勤怠データ作成
                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'date' => $dayStr,
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00',
                        'status' => 'finished',
                    ]);

                    // 休憩データもセットで作成（画像に合わせて12:00-13:00）
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
