@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細（管理者）</h1>

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
                    @if($attendance->status == '承認待ち')
                    {{ substr($attendance->clock_in, 0, 5) }}
                    <span class="time-separator">〜</span>
                    {{ substr($attendance->clock_out, 0, 5) }}
                    @else
                    <input type="text" name="clock_in" class="time-input" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="text" name="clock_out" class="time-input" value="{{ old('clock_out', substr($attendance->clock_out, 0, 5)) }}">
                    @error('clock_time')
                    <p class="error-item">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>

            {{-- 既存の休憩リスト --}}
            @foreach($attendance->rests as $index => $rest)
            <tr>
                <th>休憩{{ $loop->iteration }}</th>
                <td>
                    @if($attendance->status == '承認待ち')
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

            {{-- ★新規休憩の追加：承認待ちでも承認済みでもない時だけ表示 --}}
            @if($attendance->status != '承認待ち' && $attendance->status != '承認済み')
            <tr>
                <th>休憩{{ count($attendance->rests) + 1 }}</th>
                <td>
                    <input type="text" name="new_rest_in" class="time-input" placeholder="00:00">
                    <span class="time-separator">〜</span>
                    <input type="text" name="new_rest_out" class="time-input" placeholder="00:00">
                    @error('break_time')
                    <p class="error-item">{{ $message }}</p>
                    @enderror
                </td>
            </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    @if($attendance->status == '承認待ち')
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

        <div class="form-actions">
            @if($attendance->status == '承認待ち')
            {{-- スタッフからの申請中 --}}
            <p class="pending-message">*承認待ちのため修正はできません。</p>
            @elseif($attendance->status == '承認済み')
            {{-- 管理者修正反映後 --}}
            <button type="button" class="approve-btn approved" disabled>承認済み</button>
            @else
            {{-- 通常：修正可能 --}}
            <button type="submit" class="update-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection