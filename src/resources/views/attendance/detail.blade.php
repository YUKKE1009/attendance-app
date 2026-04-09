@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>

    <form action="{{ route('attendance.update_request', $attendance->id) }}" method="POST">
        @csrf

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
                    @if($isPending)
                    {{ substr($attendance->clock_in, 0, 5) }}
                    <span class="time-separator">〜</span>
                    {{ substr($attendance->clock_out, 0, 5) }}
                    @else
                    <input type="time" name="clock_in" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="time" name="clock_out" value="{{ old('clock_out', substr($attendance->clock_out, 0, 5)) }}">
                    {{-- FN029-1 --}}
                    @error('clock_out')
                    <p class="error-item">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>

            @foreach($attendance->rests as $index => $rest)
            <tr>
                <th>休憩</th>
                <td>
                    @if($isPending)
                    {{ substr($rest->break_in, 0, 5) }}
                    <span class="time-separator">〜</span>
                    {{ substr($rest->break_out, 0, 5) }}
                    @else
                    <input type="time" name="rests[{{ $rest->id }}][break_in]" value="{{ old("rests.{$rest->id}.break_in", substr($rest->break_in, 0, 5)) }}">
                    <span class="time-separator">〜</span>
                    <input type="time" name="rests[{{ $rest->id }}][break_out]" value="{{ old("rests.{$rest->id}.break_out", substr($rest->break_out, 0, 5)) }}">
                    @endif
                </td>
            </tr>
            @endforeach

            @if(!$isPending)
            <tr>
                <th>休憩{{ count($attendance->rests) + 1 }}</th>
                <td>
                    <input type="text" name="new_rest_in" onfocus="(this.type='time')" onblur="if(!this.value) this.type='text'" class="time-input">
                    <span class="time-separator">〜</span>
                    <input type="text" name="new_rest_out" onfocus="(this.type='time')" onblur="if(!this.value) this.type='text'" class="time-input">
                    {{-- FN029-2, 3 休憩に関するすべてのエラーを表示 --}}
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
                    @if($isPending)
                    <div class="note-text">{{ $attendance->note }}</div>
                    @else
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                    {{-- FN029-4 --}}
                    @error('note')
                    <p class="error-item">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>
        </table>

        <div class="form-actions">
            @if($isPending)
            {{-- ステータスが「承認待ち」の時 --}}
            <p class="pending-message">*承認待ちのため修正はできません。</p>
            @else
            {{-- それ以外の時 --}}
            <button type="submit" class="update-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection