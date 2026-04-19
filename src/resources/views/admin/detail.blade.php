@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細（管理者）</h1>

    {{-- 管理者用の修正フォーム --}}
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="date-display">
                    <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                    <span class="unit">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    @if($attendance->status == '承認待ち' || $attendance->status == '承認済み')
                    {{ substr($attendance->clock_in, 0, 5) }}
                    <span class="time-separator">〜</span>
                    {{ substr($attendance->clock_out, 0, 5) }}
                    @else
                    <input type="text" name="clock_in" class="time-input" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="clock_out" class="time-input" value="{{ old('clock_out', substr($attendance->clock_out, 0, 5)) }}">
                    {{-- FN039-1 --}}
                    @error('clock_out')
                    <p class="error-item">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>

            @foreach($attendance->rests as $index => $rest)
            <tr>
                <th>休憩{{ $loop->iteration }}</th>
                <td>
                    @if($attendance->status == '承認待ち' || $attendance->status == '承認済み')
                    {{ substr($rest->break_in, 0, 5) }}
                    <span class="time-separator">〜</span>
                    {{ substr($rest->break_out, 0, 5) }}
                    @else
                    <input type="text" name="rests[{{ $rest->id }}][break_in]" class="time-input" value="{{ old("rests.{$rest->id}.break_in", substr($rest->break_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="rests[{{ $rest->id }}][break_out]" class="time-input" value="{{ old("rests.{$rest->id}.break_out", substr($rest->break_out, 0, 5)) }}">
                    @endif
                </td>
            </tr>
            @endforeach

            @if($attendance->status != '承認待ち' && $attendance->status != '承認済み')
            <tr>
                <th>休憩{{ count($attendance->rests) + 1 }}</th>
                <td>
                    <input type="text" name="new_rest_in" class="time-input" placeholder="00:00" value="{{ old('new_rest_in') }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="new_rest_out" class="time-input" placeholder="00:00" value="{{ old('new_rest_out') }}">
                    {{-- FN039-2, 3 休憩に関するエラーをまとめて表示 --}}
                    @if ($errors->has('rests'))
                    @foreach ($errors->get('rests') as $message)
                    <p class="error-item">{{ $message }}</p>
                    @endforeach
                    @endif
                </td>
            </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    @if($attendance->status == '承認待ち' || $attendance->status == '承認済み')
                    <div class="note-text">{{ $attendance->remarks }}</div>
                    @else
                    <textarea name="remarks" class="textarea-field">{{ old('remarks', $attendance->remarks) }}</textarea>
                    {{-- FN039-4 --}}
                    @error('remarks')
                    <p class="error-item">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>
        </table>

        {{-- ステータスが「修正可能」な時だけ「修正」ボタンを表示 --}}
        @if($attendance->status != '承認待ち' && $attendance->status != '承認済み')
        <div class="form-actions">
            <button type="submit" class="update-btn">修正</button>
        </div>
        @endif
    </form>

    {{-- 承認用アクション（修正フォームの外に配置） --}}
    <div class="form-actions">
        @if($attendance->status == '承認待ち')
        <form action="{{ route('admin.attendance.approve', ['id' => $attendance->id]) }}" method="POST">
            @csrf
            <button type="submit" class="approve-btn">承認</button>
        </form>
        @elseif($attendance->status == '承認済み')
        <button type="button" class="approve-btn approved" disabled style="background-color: #ccc;">承認済み</button>
        @endif
    </div>
</div>
@endsection