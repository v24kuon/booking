<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>管理者ログイン | Booking Admin</title>

  {{-- Google Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

  {{-- Admin Stylesheet --}}
  @php
    $adminCssPath = public_path('css/admin.css');
    $adminCssVersion = null;

    if (is_file($adminCssPath)) {
        $mtime = @filemtime($adminCssPath);
        if ($mtime !== false) {
            $adminCssVersion = (string) $mtime;
        } else {
            $hash = @hash_file('crc32b', $adminCssPath);
            if ($hash !== false) {
                $adminCssVersion = $hash;
            }
        }
    }
  @endphp
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}@if($adminCssVersion)?v={{ $adminCssVersion }}@endif">
</head>
<body class="admin-body">
  {{-- Aurora Background --}}
  <div class="aurora-bg"></div>

  {{-- Grainy Texture Overlay --}}
  <div class="grain-overlay"></div>

  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">B</div>
        <h1 class="login-title">Welcome Back</h1>
        <p class="login-subtitle">管理画面にログインしてください</p>
      </div>

      @if ($errors->any())
        <div class="validation-errors">
          <div class="validation-errors-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; vertical-align: middle; margin-right: 0.5rem;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            認証エラー
          </div>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="post" action="{{ route('admin.login.store') }}">
        @csrf

        <div class="form-group">
          <label for="email" class="form-label">メールアドレス</label>
          <input
            id="email"
            name="email"
            type="email"
            class="form-input"
            value="{{ old('email') }}"
            placeholder="admin@example.com"
            required
            autofocus
          >
        </div>

        <div class="form-group">
          <label for="password" class="form-label">パスワード</label>
          <input
            id="password"
            name="password"
            type="password"
            class="form-input"
            placeholder="••••••••"
            required
          >
        </div>

        <div class="form-group">
          <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; color: #94A3B8;">
            <input
              type="checkbox"
              name="remember"
              value="1"
              style="width: 18px; height: 18px; accent-color: #818CF8;"
            >
            <span>ログイン状態を保持</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
          <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
          </svg>
          ログイン
        </button>
      </form>

      <div class="login-footer">
        <a href="{{ url('/') }}">
          ← トップページへ戻る
        </a>
      </div>
    </div>
  </div>
</body>
</html>
