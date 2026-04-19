@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">{{ $user->name }}さんの勤怠</h1>

    <div class="attendance-list__nav">
        <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $prevMonth]) }}" class="nav-btn">← 前月</a>
        <div class="current-month">
            <span>📅</span> {{ $displayDate->format('Y/m') }}
        </div>
        <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $nextMonth]) }}" class="nav-btn">翌月 →</a>
    </div>

    <div class="table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @php
                // その月の開始日と終了日を取得
                $startOfMonth = $displayDate->copy()->startOfMonth();
                $endOfMonth = $displayDate->copy()->endOfMonth();
                $weeks = ['日', '月', '火', '水', '木', '金', '土'];

                // 24時超え対応の計算用関数
                $parseTime = function($timeString) {
                if (!$timeString) return null;
                $parts = explode(':', $timeString);
                $hour = (int)$parts[0];
                $minute = $parts[1];
                if ($hour >= 24) {
                return \Carbon\Carbon::today()->addDay()->setTime($hour - 24, $minute);
                }
                return \Carbon\Carbon::today()->setTime($hour, $minute);
                };
                @endphp

                @for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay())
                @php
                // この日の勤怠データを検索
                $attendance = $attendances->firstWhere('date', $date->format('Y-m-d'));
                $dayOfWeek = $weeks[$date->dayOfWeek];

                $totalRestMinutes = 0;
                $workTimeDisplay = '';
                $restTimeDisplay = '';

                if ($attendance) {
                // 1. 休憩合計の計算
                foreach ($attendance->rests as $rest) {
                if ($rest->break_in && $rest->break_out) {
                $in = $parseTime($rest->break_in);
                $out = $parseTime($rest->break_out);
                $totalRestMinutes += $in->diffInMinutes($out);
                }
                }
                $restTimeDisplay = sprintf('%d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

                // 2. 勤務合計（実労働）の計算
                if ($attendance->clock_in && $attendance->clock_out) {
                $start = $parseTime($attendance->clock_in);
                $end = $parseTime($attendance->clock_out);
                $workingMinutes = $start->diffInMinutes($end) - $totalRestMinutes;
                $workTimeDisplay = sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
                }
                }
                @endphp

                <tr>
                    {{-- 日付 --}}
                    <td>{{ $date->format('m/d') }}({{ $dayOfWeek }})</td>

                    @if ($attendance)
                    {{-- 出勤・退勤（Carbonを通さずsubstrで26時台も表示） --}}
                    <td>{{ substr($attendance->clock_in, 0, 5) }}</td>
                    <td>{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>
                    <td>{{ $restTimeDisplay }}</td>
                    <td>{{ $workTimeDisplay }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                    @else
                    {{-- データがない日は空欄 --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    @endif
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection