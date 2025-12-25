@extends('admin.layout')

@section('title', '管理ダッシュボード')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <h1 class="page-title">Dashboard</h1>
    <div class="header-actions">
      <a href="{{ route('admin.sessions.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        新規セッション
      </a>
    </div>
  </div>

  {{-- Stats Grid --}}
  <div class="stats-grid">
    <div class="stat-card reveal reveal-delay-1">
      <div class="stat-icon indigo">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      <div class="stat-label">総会員数</div>
      <div class="stat-value">{{ \App\Models\Member::count() }}</div>
      <div class="stat-trend up">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
        +12.5%
      </div>
    </div>

    <div class="stat-card reveal reveal-delay-2">
      <div class="stat-icon emerald">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div class="stat-label">本日のセッション</div>
      <div class="stat-value">{{ \App\Models\Session::whereDate('start_at', today())->count() }}</div>
      <div class="stat-trend up">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
        +8.3%
      </div>
    </div>

    <div class="stat-card reveal reveal-delay-3">
      <div class="stat-icon indigo">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div class="stat-label">予約数（今月）</div>
      <div class="stat-value">{{ \App\Models\Reservation::whereMonth('crt_time', now()->month)->count() }}</div>
      <div class="stat-trend up">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
        +24.1%
      </div>
    </div>

    <div class="stat-card reveal reveal-delay-4">
      <div class="stat-icon emerald">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="stat-label">有効プラン数</div>
      <div class="stat-value">{{ \App\Models\Plan::count() }}</div>
      <div class="stat-trend up">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
        Stable
      </div>
    </div>
  </div>

  {{-- Quick Links Section --}}
  <div class="content-section reveal">
    <h2 class="section-title">クイックアクセス</h2>
    <div class="quick-links">
      <a href="{{ route('admin.programs.index') }}" class="quick-link">
        <div class="quick-link-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
        </div>
        <div class="quick-link-title">プログラム管理</div>
        <div class="quick-link-desc">{{ \App\Models\Program::count() }} プログラム</div>
      </a>

      <a href="{{ route('admin.courses.index') }}" class="quick-link">
        <div class="quick-link-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <div class="quick-link-title">コース管理</div>
        <div class="quick-link-desc">{{ \App\Models\Course::count() }} コース</div>
      </a>

      <a href="{{ route('admin.staffs.index') }}" class="quick-link">
        <div class="quick-link-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div class="quick-link-title">スタッフ管理</div>
        <div class="quick-link-desc">{{ \App\Models\Staff::count() }} 名</div>
      </a>

      <a href="{{ route('admin.locations.index') }}" class="quick-link">
        <div class="quick-link-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div class="quick-link-title">ロケーション</div>
        <div class="quick-link-desc">{{ \App\Models\Location::count() }} 拠点</div>
      </a>

      <a href="{{ route('admin.sessions.index') }}" class="quick-link">
        <div class="quick-link-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="quick-link-title">セッション枠</div>
        <div class="quick-link-desc">{{ \App\Models\Session::count() }} 枠</div>
      </a>

      <a href="{{ route('admin.plans.index') }}" class="quick-link">
        <div class="quick-link-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <div class="quick-link-title">プラン管理</div>
        <div class="quick-link-desc">{{ \App\Models\Plan::count() }} プラン</div>
      </a>
    </div>
  </div>

  {{-- Recent Activity (placeholder) --}}
  <div class="content-section reveal">
    <h2 class="section-title">システム情報</h2>
    <div class="glass-card">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
        <div>
          <div class="stat-label">Laravel Version</div>
          <div style="font-family: var(--font-numbers); font-size: 1.5rem; color: #F8FAFC;">{{ app()->version() }}</div>
        </div>
        <div>
          <div class="stat-label">PHP Version</div>
          <div style="font-family: var(--font-numbers); font-size: 1.5rem; color: #F8FAFC;">{{ phpversion() }}</div>
        </div>
        <div>
          <div class="stat-label">Environment</div>
          <div style="font-family: var(--font-numbers); font-size: 1.5rem; color: #F8FAFC;">{{ config('app.env') }}</div>
        </div>
        <div>
          <div class="stat-label">Timezone</div>
          <div style="font-family: var(--font-numbers); font-size: 1.5rem; color: #F8FAFC;">{{ config('app.timezone') }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
