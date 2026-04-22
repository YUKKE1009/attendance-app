@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<div class="request-list">
    <h1 class="request-list__title">申請一覧</h1>

    <div class="request-list__tabs">
        {{-- ★修正：名前を admin.correction.list に統一 --}}
        <a href="{{ route('admin.correction.list', ['status' => 'pending']) }}"
            class="tab-item {{ $status === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('admin.correction.list', ['status' => 'approved']) }}"
            class="tab-item {{ $status === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
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
            {{-- ★修正：Controllerから渡される変数 $requests を回す --}}
            @forelse($requests as $attendance)
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
                    <a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="no-data">該当する申請はありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection