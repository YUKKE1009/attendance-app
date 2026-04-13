@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__header">
        <h2 class="attendance-list__title">勤怠一覧</h2>

        <div class="attendance-list__nav">
            <a href="?month={{ \Carbon\Carbon::parse($month)->subMonth()->format('Y-m') }}" class="nav-btn">← 前月</a>
            <span class="current-month">📅 {{ \Carbon\Carbon::parse($month)->format('Y/m') }}</span>
            <a href="?month={{ \Carbon\Carbon::parse($month)->addMonth()->format('Y-m') }}" class="nav-btn">翌月 →</a>
        </div>
    </div>

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
            $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
            $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth();
            $weeks = ['日', '月', '火', '水', '木', '金', '土'];

            // 【新設】"26:00" などの文字列を「分」に変換する関数
            $timeToMinutes = function($time) {
            if (!$time) return 0;
            $parts = explode(':', $time);
            return ((int)$parts[0] * 60) + (int)($parts[1] ?? 0);
            };
            @endphp

            @for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay())
            @php
            $attendance = $attendances->firstWhere('date', $date->format('Y-m-d'));
            $dayOfWeek = $weeks[$date->dayOfWeek];

            $rowClass = '';
            if ($date->dayOfWeek == 0) $rowClass = 'is-sunday';
            if ($date->dayOfWeek == 6) $rowClass = 'is-saturday';
            @endphp

            <tr class="{{ $rowClass }}">
                <td>{{ $date->format('m/d') }}({{ $dayOfWeek }})</td>

                @if ($attendance)
                @php
                // 1. 休憩時間の合計計算（Carbonを使わず分単位で計算）
                $totalRestMinutes = 0;
                foreach ($attendance->rests as $rest) {
                if ($rest->break_in && $rest->break_out) {
                $inMin = $timeToMinutes($rest->break_in);
                $outMin = $timeToMinutes($rest->break_out);
                $totalRestMinutes += ($outMin - $inMin);
                }
                }
                $restTimeDisplay = sprintf('%d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

                // 2. 勤務合計の計算（日跨ぎ26:00等に対応）
                $workTimeDisplay = '';
                if ($attendance->clock_in && $attendance->clock_out) {
                $startMin = $timeToMinutes($attendance->clock_in);
                $endMin = $timeToMinutes($attendance->clock_out);

                // 実労働時間 = (退勤分 - 出勤分) - 休憩分
                $workingMinutes = ($endMin - $startMin) - $totalRestMinutes;

                // マイナスにならないようにガード
                if ($workingMinutes < 0) $workingMinutes=0;

                    $workTimeDisplay=sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
                    }
                    @endphp

                    <td>{{ substr($attendance->clock_in, 0, 5) }}</td>
                    <td>{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>
                    <td>{{ $restTimeDisplay }}</td>
                    <td>{{ $workTimeDisplay }}</td>
                    <td>
                        <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                    @else
                    <td></td>
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