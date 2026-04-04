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
                <a href="/">
                    {{-- ロゴ画像が確実に出るように --}}
                    <img src="{{ asset('img/header-logo.png') }}" alt="COACHTECH">
                </a>
            </div>

            {{--
            navタグ自体は常に置いておくことで、flexの左右配置(justify-content)を維持します。
            中身のリストだけを @auth で制御します。
        --}}
            <nav class="header__nav">
                <ul class="header__nav-list">

                    @auth
                    <li class="header__nav-item"><a href="/attendance">勤怠</a></li>
                    <li class="header__nav-item"><a href="/attendance/list">勤怠一覧</a></li>
                    <li class="header__nav-item"><a href="/request">申請</a></li>
                    <li class="header__nav-item">
                        <form action="/logout" method="post" class="header__form">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    </li>
                    @endauth
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>

</html>