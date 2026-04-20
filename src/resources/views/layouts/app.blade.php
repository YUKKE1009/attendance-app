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
                {{-- 管理者ログイン画面ならリンクなし、それ以外はトップへ --}}
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
                    {{-- ■ 管理者としてログインしている場合 --}}
                    @if(Auth::guard('admin')->check())
                    <li class="header__nav-item">
                        <a href="/admin/attendance/list">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/admin/staff/list">スタッフ一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/admin/stamp_correction_request/list">申請一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <form action="{{ route('admin.logout') }}" method="post" class="header__form">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    </li>

                    {{-- ■ 一般ユーザーとしてログインしている場合 --}}
                    @elseif(Auth::check())
                    {{-- ★ここがポイント：認証済みの時だけ全メニューを表示 --}}
                    @if(Auth::user()->hasVerifiedEmail())
                    <li class="header__nav-item">
                        <a href="/attendance">勤怠</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/attendance/list">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="/stamp_correction_request/list">申請</a>
                    </li>
                    <li class="header__nav-item">
                        <form action="/logout" method="post" class="header__form">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    </li>
                    @endif
                    {{-- 未認証（メール誘導画面）の時は、この else ブロックを通るが何も書かないので表示されない --}}
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