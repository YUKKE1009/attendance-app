@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">申請一覧(管理者)</h1>

    <div class="request-tabs">
        <a href="{{ route('admin.correction.list', ['status' => 'pending']) }}"
            class="tab-item {{ $status === 'pending' ? 'is-active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('admin.correction.list', ['status' => 'approved']) }}"
            class="tab-item {{ $status === 'approved' ? 'is-active' : '' }}">
            承認済み
        </a>
    </div>

    <div class="table-wrapper">
        <table class="attendance-table">
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
                @forelse($requests as $req)
                <tr>
                    <td>{{ $status === 'approved' ? '承認済み' : '承認待ち' }}</td>
                    <td>{{ $req->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}</td>
                    <td>{{ $req->remarks }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('admin.correction.detail', ['id' => $req->id]) }}" class="detail-link">詳細</a>
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
</div>
@endsection