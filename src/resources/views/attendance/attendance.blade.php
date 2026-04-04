@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance__container">
    {{-- ステータスバッジ（勤務外） --}}
    <div class="attendance__status">
        <span class="attendance__status-badge">勤務外</span>
    </div>

    {{-- 日時表示エリア --}}
    <div class="attendance__header">
        <p class="attendance__date">
            {{ date('Y年n月j日') }}({{ ['日','月','火','水','木','金','土'][date('w')] }})
        </p>
        <p class="attendance__time" id="current-time">
            {{ date('H:i') }}
        </p>
    </div>

    {{-- 打刻ボタンエリア --}}
    <div class="attendance__button-group">
        {{-- 出勤前：黒い「出勤」ボタンのみを表示 --}}
        <form action="/attendance/clock-in" method="post" class="attendance__form">
            @csrf
            <button type="submit" class="attendance__button attendance__button--black">出勤</button>
        </form>
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
    updateTime(); // 初回実行
</script>
@endsection