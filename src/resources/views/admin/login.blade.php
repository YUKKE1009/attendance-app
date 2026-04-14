@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="main">
    <div class="auth-card">
        <h2 class="auth-card__title">管理者ログイン</h2>

        <form class="auth-form" action="{{ url('/admin/login') }}" method="POST">
            @csrf
            <div class="form__group">
                <label class="form__label" for="email">メールアドレス</label>
                <input class="form__input" type="email" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                <p class="form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form__group">
                <label class="form__label" for="password">パスワード</label>
                <input class="form__input" type="password" name="password" id="password">
                @error('password')
                <p class="form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="form__button">管理者ログインする</button>
        </form>
    </div>
</div>
@endsection