@extends('layouts.app') {{-- レイアウトを適用 --}}

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-card">
    <h2 class="auth-card__title">ログイン</h2>

    <form action="/login" method="POST" novalidate>
        @csrf
        <div class="form__group">
            <label class="form__label">メールアドレス</label>
            <input type="email" name="email" class="form__input" value="{{ old('email') }}">
            @error('email')
            <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form__group">
            <label class="form__label">パスワード</label>
            <input type="password" name="password" class="form__input">
            @error('password')
            <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="form__button">ログインする</button>
    </form>

    <div class="auth-footer">
        <a href="/register" class="auth-footer__link">会員登録はこちら</a>
    </div>
</div>
@endsection