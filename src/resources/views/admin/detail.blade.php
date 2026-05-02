@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-detail">
    {{-- タイトルの出し分け --}}
    <h1 class="attendance-detail__title">
        {{ $mode === 'approve' ? '勤怠詳細' : '勤怠詳細（管理者）' }}
    </h1>

    @php
    // 1. 読み取り専用にする条件
    // ・承認モード(approve) の時
    // ・または 通常修正(edit) かつ すでに承認待ち/承認済みステータスの時
    $isReadOnly = ($mode === 'approve') || ($mode === 'edit' && ($attendance->status == '承認待ち' || $attendance->status == '承認済み'));

    // 2. 送信先の切り替え
    $formAction = ($mode === 'approve')
    ? route('admin.attendance.approve', ['id' => $correction->id])
    : route('admin.attendance.update', ['id' => $attendance->id]);

    // 3. 承認待ちフラグ（コントローラーから渡された値を使用。念のためデフォルトtrue）
    $isPending = $isPending ?? true;
    @endphp

    <form action="{{ $formAction }}" method="POST">
        @csrf
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
                    <span>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    @if($isReadOnly)
                    {{ substr($attendance->clock_in, 0, 5) }} <span class="time-separator">〜</span> {{ substr($attendance->clock_out, 0, 5) }}
                    @else
                    <input type="text" name="clock_in" class="time-input" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="clock_out" class="time-input" value="{{ old('clock_out', substr($attendance->clock_out, 0, 5)) }}">
                    @error('clock_out') <p class="error-item">{{ $message }}</p> @enderror
                    @endif
                </td>
            </tr>

            @foreach($attendance->rests as $index => $rest)
            <tr>
                <th>休憩{{ $loop->iteration }}</th>
                <td>
                    @if($isReadOnly)
                    {{ substr($rest->break_in, 0, 5) }} <span class="time-separator">〜</span> {{ substr($rest->break_out, 0, 5) }}
                    @else
                    <input type="text" name="rests[{{ $rest->id }}][break_in]" class="time-input" value="{{ old("rests.{$rest->id}.break_in", substr($rest->break_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="rests[{{ $rest->id }}][break_out]" class="time-input" value="{{ old("rests.{$rest->id}.break_out", substr($rest->break_out, 0, 5)) }}">
                    @endif
                </td>
            </tr>
            @endforeach

            {{-- 修正モードで、かつ読み取り専用でない場合のみ「新規休憩」を表示 --}}
            @if($mode === 'edit' && !$isReadOnly)
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
                    @error('remarks') <p class="error-item">{{ $message }}</p> @enderror
                    @endif
                </td>
            </tr>
        </table>

        <div class="form-actions">
            @if($mode === 'approve')
            @if($isPending)
            {{-- 承認待ち：承認ボタンを表示 --}}
            <button type="submit" class="approve-btn">承認</button>
            @else
            {{-- 承認済み：グレーの承認済みボタンを表示 --}}
            <button type="button" class="approve-btn approved" disabled>承認済み</button>
            @endif

            @elseif($attendance->status === '承認待ち')
            {{-- 管理者一覧から来た詳細画面で、すでに申請中の場合 --}}
            <p class="pending-message">*承認待ちのため修正はできません。</p>

            @elseif($attendance->status === '承認済み')
            {{-- 管理者一覧から来た詳細画面で、すでに承認済みの場合 --}}
            <button type="button" class="approve-btn approved" disabled>承認済み</button>

            @else
            {{-- 修正可能な通常状態 --}}
            <button type="submit" class="update-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection