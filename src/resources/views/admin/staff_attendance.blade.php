@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    {{-- PG11: タイトル（左側に黒い棒が表示されるクラス） --}}
    <h1 class="attendance-list__title">{{ $user->name }}さんの勤怠</h1>

    {{-- 月次ナビゲーション（左右に前月・翌月が配置されるクラス） --}}
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
                @foreach($attendances as $attendance)
                <tr>
                    {{-- 日付と曜日：例 04/01(水) --}}
                    <td>
                        {{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}({{ \Carbon\Carbon::parse($attendance->date)->isoFormat('dd') }})
                    </td>

                    {{-- 出退勤時間 --}}
                    <td>
                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                    </td>
                    <td>
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                    </td>

                    {{-- 休憩合計（モデルの getTotalBreakAttribute を呼び出し） --}}
                    <td>{{ $attendance->total_break }}</td>

                    {{-- 勤務合計（モデルの getTotalWorkAttribute を呼び出し） --}}
                    <td>{{ $attendance->total_work }}</td>

                    <td>
                        {{-- 管理者用の「勤怠詳細（修正）」画面へ遷移 --}}
                        <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection