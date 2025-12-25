@extends('admin.layout')

@section('title', 'セッション管理')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <h1 class="page-title">セッション管理</h1>
    <div class="header-actions">
      <a href="{{ route('admin.sessions.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        新規セッション
      </a>
    </div>
  </div>

  @php
    $statusLabels = [1 => '予約受付中', 2 => '終了', 9 => '中止'];
    $statusBadges = [1 => 'badge-success', 2 => 'badge-warning', 9 => 'badge-danger'];
  @endphp

  {{-- Stats Summary --}}
  <div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card reveal reveal-delay-1">
      <div class="stat-icon indigo">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div class="stat-label">総セッション数</div>
      <div class="stat-value">{{ $sessions->total() }}</div>
    </div>
    <div class="stat-card reveal reveal-delay-2">
      <div class="stat-icon emerald">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="stat-label">予約受付中</div>
      <div class="stat-value">{{ $sessions->where('status', 1)->count() }}</div>
    </div>
  </div>

  {{-- Data Table --}}
  <div class="glass-card reveal reveal-delay-3">
    <div style="overflow-x: auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>開始</th>
            <th>終了</th>
            <th>プログラム</th>
            <th>ロケーション</th>
            <th>スタッフ</th>
            <th>通常枠</th>
            <th>体験枠</th>
            <th>状態</th>
            <th style="text-align: right;">アクション</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($sessions as $session)
            <tr>
              <td>
                <span style="font-family: var(--font-numbers); color: #64748B;">{{ $session->session_id }}</span>
              </td>
              <td>
                <span style="font-family: var(--font-numbers); color: #F8FAFC;">{{ optional($session->start_at)->format('Y-m-d') }}</span>
                <br>
                <span style="font-family: var(--font-numbers); color: #64748B; font-size: 0.75rem;">{{ optional($session->start_at)->format('H:i') }}</span>
              </td>
              <td>
                <span style="font-family: var(--font-numbers); color: #F8FAFC;">{{ optional($session->end_at)->format('Y-m-d') }}</span>
                <br>
                <span style="font-family: var(--font-numbers); color: #64748B; font-size: 0.75rem;">{{ optional($session->end_at)->format('H:i') }}</span>
              </td>
              <td>
                <span style="font-weight: 500; color: #F8FAFC;">{{ $session->program?->program_name }}</span>
              </td>
              <td>
                <span style="color: #94A3B8;">{{ $session->location?->location_name }}</span>
              </td>
              <td>
                <span style="color: #94A3B8;">{{ $session->staff?->staff_name }}</span>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-family: var(--font-numbers);">{{ $session->reserved_count }} / {{ $session->capacity }}</span>
                  @if($session->capacity > 0)
                    <div style="width: 40px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                      <div style="height: 100%; background: linear-gradient(135deg, var(--color-indigo), var(--color-emerald)); width: {{ min(100, ($session->reserved_count / $session->capacity) * 100) }}%;"></div>
                    </div>
                  @endif
                </div>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-family: var(--font-numbers);">{{ $session->reserved_exp_count }} / {{ $session->exp_capacity }}</span>
                  @if($session->exp_capacity > 0)
                    <div style="width: 40px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                      <div style="height: 100%; background: linear-gradient(135deg, var(--color-emerald), #34D399); width: {{ min(100, ($session->reserved_exp_count / $session->exp_capacity) * 100) }}%;"></div>
                    </div>
                  @endif
                </div>
              </td>
              <td>
                <span class="badge {{ $statusBadges[$session->status] ?? 'badge-warning' }}">{{ $statusLabels[$session->status] ?? $session->status }}</span>
              </td>
              <td style="text-align: right;">
                <div class="action-buttons" style="justify-content: flex-end;">
                  <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    編集
                  </a>
                  @if ($session->status !== 9)
                    <form method="post" action="{{ route('admin.sessions.destroy', $session) }}" style="display: inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('このセッションを中止にしますか？')">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        中止
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" style="text-align: center; padding: 3rem; color: #64748B;">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 1rem; opacity: 0.5;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div>セッションがまだ登録されていません</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  @if ($sessions->hasPages())
    <div style="margin-top: 2rem; display: flex; justify-content: center;">
      {{ $sessions->links() }}
    </div>
  @endif
@endsection
