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
            {{-- $requests を $req として回します --}}
            @forelse($requests as $req)
            <tr>
                <td>
                    {{-- 数字の1・2を文字に変換して表示 --}}
                    {{ $status === 'approved' ? '承認済み' : '承認待ち' }}
                </td>
                <td>{{ $req->user->name }}</td>
                {{-- date ではなく target_date に変更 --}}
                <td>{{ \Carbon\Carbon::parse($req->target_date)->format('Y/m/d') }}</td>
                {{-- remarks ではなく remark に変更 --}}
                <td>{{ $req->remark }}</td>
                {{-- created_at（申請した日）を表示 --}}
                <td>{{ \Carbon\Carbon::parse($req->created_at)->format('Y/m/d') }}</td>
                <td>
                    {{-- 詳細へのリンク。一般ユーザー用の詳細画面ルートを指定 --}}
                    <a href="{{ route('attendance.detail', ['id' => $req->attendance_id]) }}" class="detail-link">詳細</a>
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