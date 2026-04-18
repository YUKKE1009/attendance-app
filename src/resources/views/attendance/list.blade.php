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
                // 1. 休憩時間の合計（分）を計算
                $totalRestMinutes = 0;
                foreach ($attendance->rests as $rest) {
                if ($rest->break_in && $rest->break_out) {
                $in = \Carbon\Carbon::parse($rest->break_in);
                $out = \Carbon\Carbon::parse($rest->break_out);
                $totalRestMinutes += $in->diffInMinutes($out);
                }
                }
                // 休憩時間を H:i 形式に変換
                $restTimeDisplay = sprintf('%d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

                // 2. 勤務合計（実労働時間）の計算
                $workTimeDisplay = '';
                if ($attendance->clock_in && $attendance->clock_out) {
                $start = \Carbon\Carbon::parse($attendance->clock_in);
                $end = \Carbon\Carbon::parse($attendance->clock_out);

                // (退勤 - 出勤) の総分数から休憩分数を引く
                $workingMinutes = $start->diffInMinutes($end) - $totalRestMinutes;
                $workTimeDisplay = sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
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
                <td></td> {{-- 出勤 --}}
                <td></td> {{-- 退勤 --}}
                <td></td> {{-- 休憩 --}}
                <td></td> {{-- 合計 --}}
                <td></td> {{-- 詳細 --}}
                {{-- 合計で6列（日付の列は最初にあるので、空欄は5つ追加） --}}
                @endif
            </tr>
            @endfor
        </tbody>
    </table>
</div>
@endsection