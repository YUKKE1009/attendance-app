@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<div class="request-list">
    <h1 class="request-list__title">申請一覧</h1>

    <div class="request-list__tabs">
        <a href="#" class="tab-item active">承認待ち</a>
        <a href="#" class="tab-item">承認済み</a>
    </div>

    <table class="request-list__table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>
                    <span class="status-badge {{ $attendance->status === '承認待ち' ? 'pending' : 'approved' }}">
                        {{ $attendance->status }}
                    </span>
                </td>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}</td>
                <td>{{ $attendance->remarks }}</td>
                <td>{{ $attendance->updated_at->format('Y/m/d') }}</td>
                <td>
                    {{-- 修正申請時の詳細画面へ戻るリンク --}}
                    <a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection