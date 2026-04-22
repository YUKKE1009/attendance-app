@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__header">
        {{-- 一般ユーザー側に合わせてh2に変更 --}}
        <h2 class="attendance-list__title">{{ $user->name }}さんの勤怠</h2>

        <div class="attendance-list__nav">
            <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $prevMonth]) }}" class="nav-btn">← 前月</a>
            <span class="current-month">📅 {{ $displayDate->format('Y/m') }}</span>
            <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $nextMonth]) }}" class="nav-btn">翌月 →</a>
        </div>
    </div>

    {{-- table-wrapperを削除し、一般ユーザー側と構造を統一 --}}
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
            $startOfMonth = $displayDate->copy()->startOfMonth();
            $endOfMonth = $displayDate->copy()->endOfMonth();
            $weeks = ['日', '月', '火', '水', '木', '金', '土'];

            $parseTime = function($timeString) {
            if (!$timeString) return null;
            $parts = explode(':', $timeString);
            $hour = (int)$parts[0];
            $minute = $parts[1];
            $second = $parts[2] ?? '00';

            if ($hour >= 24) {
            $hour = $hour - 24;
            return \Carbon\Carbon::today()->addDay()->setTime($hour, $minute, $second);
            }
            return \Carbon\Carbon::today()->setTime($hour, $minute, $second);
            };
            @endphp

            @for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay())
            @php
            $attendance = $attendances->firstWhere('date', $date->format('Y-m-d'));
            $dayOfWeek = $weeks[$date->dayOfWeek];

            // 土日のクラス判定を追加
            $rowClass = '';
            if ($date->dayOfWeek == 0) $rowClass = 'is-sunday';
            if ($date->dayOfWeek == 6) $rowClass = 'is-saturday';

            $totalRestMinutes = 0;
            $workTimeDisplay = '';
            $restTimeDisplay = '';

            if ($attendance) {
            foreach ($attendance->rests as $rest) {
            if ($rest->break_in && $rest->break_out) {
            $in = $parseTime($rest->break_in);
            $out = $parseTime($rest->break_out);
            $totalRestMinutes += $in->diffInMinutes($out);
            }
            }
            $restTimeDisplay = sprintf('%d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

            if ($attendance->clock_in && $attendance->clock_out) {
            $start = $parseTime($attendance->clock_in);
            $end = $parseTime($attendance->clock_out);
            $workingMinutes = $start->diffInMinutes($end) - $totalRestMinutes;
            $workTimeDisplay = sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
            }
            }
            @endphp

            <tr class="{{ $rowClass }}">
                <td>{{ $date->format('m/d') }}({{ $dayOfWeek }})</td>

                @if ($attendance)
                <td>{{ substr($attendance->clock_in, 0, 5) }}</td>
                <td>{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>
                <td>{{ $restTimeDisplay }}</td>
                <td>{{ $workTimeDisplay }}</td>
                <td>
                    {{-- 管理者用の詳細ルートを指定 --}}
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                </td>
                @else
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
@endsection