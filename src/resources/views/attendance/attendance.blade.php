@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance__container">
    {{-- 1. ステータスバッジ --}}
    <div class="attendance__status">
        <span class="attendance__status-badge">
            {{ !$attendance ? '勤務外' : $attendance->status }}
        </span>
    </div>

    {{-- 2. 日時表示エリア --}}
    <div class="attendance__header">
        <p class="attendance__date">
            {{ date('Y年n月j日') }}({{ ['日','月','火','水','木','金','土'][date('w')] }})
        </p>
        <p class="attendance__time" id="current-time">
            {{ date('H:i') }}
        </p>
    </div>

    {{-- 3. 打刻ボタンエリア --}}
    <div class="attendance__button-group">
        @if(!$attendance)
        {{-- 【勤務外】 --}}
        <form action="/attendance/clock-in" method="post">
            @csrf
            <button type="submit" class="attendance__button attendance__button--black">出勤</button>
        </form>

        @elseif($attendance->status === '出勤中')
        {{-- 【出勤中】 --}}
        <form action="/attendance/clock-out" method="post">
            @csrf
            <button type="submit" class="attendance__button attendance__button--black">退勤</button>
        </form>
        <form action="/attendance/break-in" method="post">
            @csrf
            <button type="submit" class="attendance__button attendance__button--white">休憩入</button>
        </form>

        @elseif($attendance->status === '休憩中')
        {{-- 【休憩中】 --}}
        <form action="/attendance/break-out" method="post">
            @csrf
            <button type="submit" class="attendance__button attendance__button--white">休憩戻</button>
        </form>

        @elseif($attendance->status === '退勤済')
        {{-- 【退勤済】 --}}
        <p class="attendance__thanks">お疲れ様でした。</p>
        @endif
    </div>
</div>

{{-- リアルタイム時計スクリプト --}}
<script>
    function updateTime() {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = `${hh}:${mm}`;
        }
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>
@endsection