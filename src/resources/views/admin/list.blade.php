@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection

@section('content')
<div class="container">
    <h2 class="page-title">{{ $displayDate->format('Y年n月j日の勤怠') }}</h2>

    <div class="date-nav">
        <a href="?date={{ $displayDate->copy()->subDay()->format('Y-m-d') }}" class="nav-btn">← 前日</a>
        <div class="date-display">
            <span class="calendar-icon">📅</span>
            <span class="date-text">{{ $displayDate->format('Y/m/d') }}</span>
        </div>
        <a href="?date={{ $displayDate->copy()->addDay()->format('Y-m-d') }}" class="nav-btn">翌日 →</a>
    </div>

    <div class="table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @php
                // スタッフ側の知恵を拝借：時間を分に変換する関数
                $timeToMinutes = function($time) {
                if (!$time) return 0;
                $parts = explode(':', $time);
                return ((int)$parts[0] * 60) + (int)($parts[1] ?? 0);
                };
                @endphp

                @foreach($attendances as $attendance)
                @php
                // 1. 休憩合計の計算
                $totalRestMinutes = 0;
                foreach ($attendance->rests as $rest) {
                if ($rest->break_in && $rest->break_out) {
                $totalRestMinutes += ($timeToMinutes($rest->break_out) - $timeToMinutes($rest->break_in));
                }
                }
                $restTimeDisplay = sprintf('%d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

                // 2. 勤務合計の計算（25:00等の日またぎ対応）
                $workTimeDisplay = '';
                if ($attendance->clock_in && $attendance->clock_out) {
                $startMin = $timeToMinutes($attendance->clock_in);
                $endMin = $timeToMinutes($attendance->clock_out);
                $workingMinutes = ($endMin - $startMin) - $totalRestMinutes;
                if ($workingMinutes < 0) $workingMinutes=0;
                    $workTimeDisplay=sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
                    }
                    @endphp
                    <tr>
                    <td>{{ $attendance->user->name }}</td>

                    <td>{{ $attendance->clock_in ? substr($attendance->clock_in, 0, 5) : '' }}</td>
                    <td>{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>

                    <td>{{ $restTimeDisplay }}</td>
                    <td>{{ $workTimeDisplay }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                    </tr>
                    @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection