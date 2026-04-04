@extends('layouts.app') {{-- レイアウトファイルが resources/views/layouts/app.blade.php にある場合 --}}

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-card">
    <h2 class="auth-card__title">会員登録</h2>

    <form action="/register" method="POST" novalidate>
        @csrf
        <div class="form__group">
            <label class="form__label">名前</label>
            <input type="text" name="name" class="form__input" value="{{ old('name') }}">
            @error('name')
            <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

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

        <div class="form__group">
            <label class="form__label">パスワード確認</label>
            <input type="password" name="password_confirmation" class="form__input">
        </div>

        <button type="submit" class="form__button">登録する</button>
    </form>

    <div class="auth-footer">
        <a href="/login" class="auth-footer__link">ログインはこちら</a>
    </div>
</div>
@endsection