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
            // ユーザー作成
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
            ]);

            // 3. 各ユーザーに2023年6月の1ヶ月分のデータを作成
            for ($i = 1; $i <= 30; $i++) {
                $date = sprintf('2023-06-%02d', $i);
                $dayOfWeek = date('w', strtotime($date));

                // 土日（0:日, 6:土）はデータを作らない
                if ($dayOfWeek == 0 || $dayOfWeek == 6) continue;

                // 勤怠データ作成
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'clock_in' => '09:00:00',
                    'clock_out' => '18:00:00',
                    'status' => 'finished',
                ]);

                // 休憩データ作成（12:00〜13:00）
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'break_in' => '12:00:00',
                    'break_out' => '13:00:00',
                ]);
            }
        }
    }
}
