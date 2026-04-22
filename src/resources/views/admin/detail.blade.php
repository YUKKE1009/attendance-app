@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細（管理者）</h1>

    @php
    // 1. 修正不可（読み取り専用）にする条件：
    // 通常詳細モード(edit) かつ ステータスが「承認待ち」または「承認済み」の場合
    $isReadOnly = ($mode === 'edit' && ($attendance->status == '承認待ち' || $attendance->status == '承認済み'));

    // 2. 送信先の切り替え
    // 承認モードなら承認用ルーティングへ、それ以外は更新用ルーティングへ
    $formAction = ($mode === 'approve')
    ? route('admin.attendance.approve', ['id' => $attendance->id])
    : route('admin.attendance.update', ['id' => $attendance->id]);
    @endphp

    {{-- 管理者用の修正・承認フォーム --}}
    <form action="{{ $formAction }}" method="POST">
        @csrf
        {{-- 更新モード（edit）の時だけPATCHメソッドを使う --}}
        @if($mode === 'edit')
        @method('PATCH')
        @endif

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
                    @if($isReadOnly)
                    {{ substr($attendance->clock_in, 0, 5) }}
                    <span class="time-separator">〜</span>
                    {{ substr($attendance->clock_out, 0, 5) }}
                    @else
                    <input type="text" name="clock_in" class="time-input" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="clock_out" class="time-input" value="{{ old('clock_out', substr($attendance->clock_out, 0, 5)) }}">
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
                    @if($isReadOnly)
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

            {{-- 新規休憩追加（修正可能な時のみ表示） --}}
            @if(!$isReadOnly)
            <tr>
                <th>休憩{{ count($attendance->rests) + 1 }}</th>
                <td>
                    <input type="text" name="new_rest_in" class="time-input" placeholder="00:00" value="{{ old('new_rest_in') }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="new_rest_out" class="time-input" placeholder="00:00" value="{{ old('new_rest_out') }}">
                </td>
            </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    @if($isReadOnly)
                    <div class="note-text">{{ $attendance->remarks }}</div>
                    @else
                    <textarea name="remarks" class="textarea-field">{{ old('remarks', $attendance->remarks) }}</textarea>
                    @error('remarks')
                    <p class="error-item">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>
        </table>

        {{-- ボタン・メッセージエリア --}}
        <div class="form-actions">
            @if($mode === 'approve')
            {{-- PG13: 承認画面ルート --}}
            <button type="submit" class="approve-btn">承認</button>

            @elseif($attendance->status === '承認待ち')
            {{-- PG09: 詳細ルート（承認待ち時） --}}
            <p class="error-message" style="color: red; font-weight: bold;">承認待ちのため修正はできません。</p>

            @elseif($attendance->status === '承認済み')
            {{-- PG09: 詳細ルート（承認済み時） --}}
            <button type="button" class="approve-btn approved" disabled style="background-color: #ccc;">承認済み</button>

            @else
            {{-- PG09: 通常修正時 --}}
            <button type="submit" class="update-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection