<!doctype html>
<html lang="ja" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', '予約システム')</title>
  <link rel="stylesheet" href="{{ asset('css/pico.min.css') }}">
</head>
<body>
  <nav class="container-fluid">
    <ul>
      <li><strong>予約システム</strong></li>
      @auth
        <li><a href="{{ route('mypage') }}">マイページ</a></li>
        <li><a href="{{ route('member.sessions.index') }}">枠一覧</a></li>
      @endauth
    </ul>
    <ul>
      @guest
        <li><a href="{{ route('login') }}">ログイン</a></li>
      @endguest

      @auth
        <li>
          <form method="post" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="secondary">ログアウト</button>
          </form>
        </li>
      @endauth

      <li><a href="{{ route('admin.login') }}">管理者</a></li>
    </ul>
  </nav>

  <main class="container">
    @include('partials.flash')
    @yield('content')
  </main>
</body>
</html>
