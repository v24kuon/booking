@extends('admin.layout')

@section('title', 'プログラム管理')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <h1 class="page-title">プログラム管理</h1>
    <div class="header-actions">
      <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        新規プログラム
      </a>
    </div>
  </div>

  {{-- Stats Summary --}}
  <div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card reveal reveal-delay-1">
      <div class="stat-icon indigo">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
      </div>
      <div class="stat-label">総プログラム数</div>
      <div class="stat-value">{{ $programs->total() }}</div>
    </div>
    <div class="stat-card reveal reveal-delay-2">
      <div class="stat-icon emerald">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="stat-label">有効プログラム</div>
      <div class="stat-value">{{ $programs->where('status', 1)->count() }}</div>
    </div>
  </div>

  {{-- Data Table --}}
  <div class="glass-card reveal reveal-delay-3">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>カテゴリ</th>
          <th>プログラム名</th>
          <th>ポイント</th>
          <th>回数券</th>
          <th>ステータス</th>
          <th style="text-align: right;">アクション</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($programs as $program)
          <tr>
            <td>
              <span style="font-family: var(--font-numbers); color: #64748B;">{{ $program->program_id }}</span>
            </td>
            <td>
              <span class="badge badge-success">{{ $program->program_category }}</span>
            </td>
            <td>
              <span style="font-weight: 500; color: #F8FAFC;">{{ $program->program_name }}</span>
            </td>
            <td>
              <span style="font-family: var(--font-numbers);">{{ $program->program_point }}</span>
            </td>
            <td>
              <span style="font-family: var(--font-numbers);">{{ $program->program_ticket }}</span>
            </td>
            <td>
              @if ($program->status === 1)
                <span class="badge badge-success">有効</span>
              @else
                <span class="badge badge-danger">無効</span>
              @endif
            </td>
            <td style="text-align: right;">
              <div class="action-buttons" style="justify-content: flex-end;">
                <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-secondary btn-sm">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  編集
                </a>
                @if ($program->status === 1)
                  <form method="post" action="{{ route('admin.programs.destroy', $program) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('このプログラムを無効化しますか？')">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                      </svg>
                      無効化
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem; color: #64748B;">
              <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 1rem; opacity: 0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
              </svg>
              <div>プログラムがまだ登録されていません</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if ($programs->hasPages())
    <div style="margin-top: 2rem; display: flex; justify-content: center;">
      {{ $programs->links() }}
    </div>
  @endif
@endsection
