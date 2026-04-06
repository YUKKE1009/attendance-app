@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
{{-- 1. ここが絶対に出るかチェック --}}
<div style="background: yellow; color: red; padding: 20px; font-weight: bold;">
    検証用：Blade内のcontentセクションは読み込まれています！
</div>

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
            @foreach($attendances as $attendance)
            @php
            $weeks = ['日', '月', '火', '水', '木', '金', '土'];
            $date = \Carbon\Carbon::parse($attendance->date);
            $dayOfWeek = $weeks[$date->dayOfWeek];
            @endphp
            <tr>
                <td>{{ $date->format('m/d') }}({{ $dayOfWeek }})</td>
                <td>{{ $attendance->clock_in ? substr($attendance->clock_in, 0, 5) : '' }}</td>
                <td>{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>
                <td>1:00</td>
                <td>8:00</td>
                <td>
                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection