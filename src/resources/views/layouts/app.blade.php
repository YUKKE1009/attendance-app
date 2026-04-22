<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH 勤怠管理システム</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                @if(Request::is('admin/login'))
                <img src="{{ asset('img/header-logo.png') }}" alt="COACHTECH">
                @else
                <a href="/">
                    <img src="{{ asset('img/header-logo.png') }}" alt="COACHTECH">
                </a>
                @endif
            </div>

            <nav class="header__nav">
                <ul class="header__nav-list">
                    {{-- ★ここを修正：管理者ログイン画面以外、かつ管理者として認証済みの場合のみ表示 --}}
                    @if(!Request::is('admin/login') && Auth::guard('admin')->check())
                    <li class="header__nav-item">
                        <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('admin.correction.list') }}">申請一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <form action="{{ route('admin.logout') }}" method="post" class="header__form">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    </li>

                    {{-- ■ 一般ユーザーとしてログインしている場合 --}}
                    @elseif(Auth::check())
                    @if(Auth::user()->hasVerifiedEmail())
                    <li class="header__nav-item">
                        <a href="{{ route('attendance.index') }}">勤怠</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('admin.correction.list') }}">申請</a>
                    </li>
                    <li class="header__nav-item">
                        <form action="/logout" method="post" class="header__form">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    </li>
                    @endif
                    @endif
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>

</html>