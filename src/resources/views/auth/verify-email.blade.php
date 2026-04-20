@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="verify-email">
    <div class="verify-email__card">
        <p class="verify-email__text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        {{-- 再送完了メッセージ (FN012用) --}}
        @if (session('status') == 'verification-link-sent')
        <p class="verify-email__alert">認証メールを再送しました。</p>
        @endif

        <div class="verify-email__actions">
            {{-- 「認証はこちらから」ボタン --}}
            <a href="http://localhost:8025" target="_blank" class="verify-email__btn">
                認証はこちらから
            </a>

            {{-- 「認証メールを再送する」リンク (FN012用) --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-email__resend-link">
                    認証メールを再送する
                </button>
            </form>
        </div>
    </div>
</div>
@endsection