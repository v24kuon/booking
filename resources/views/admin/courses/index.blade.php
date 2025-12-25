@extends('admin.layout')

@section('title', 'コース管理')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <h1 class="page-title">コース管理</h1>
    <div class="header-actions">
      <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        新規コース
      </a>
    </div>
  </div>

  {{-- Stats Summary --}}
  <div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card reveal reveal-delay-1">
      <div class="stat-icon indigo">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
      </div>
      <div class="stat-label">総コース数</div>
      <div class="stat-value">{{ $courses->total() }}</div>
    </div>
    <div class="stat-card reveal reveal-delay-2">
      <div class="stat-icon emerald">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="stat-label">有効コース</div>
      <div class="stat-value">{{ $courses->where('status', 1)->count() }}</div>
    </div>
  </div>

  {{-- Data Table --}}
  <div class="glass-card reveal reveal-delay-3">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>コース名</th>
          <th>種別</th>
          <th>レベル</th>
          <th>ステータス</th>
          <th style="text-align: right;">アクション</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($courses as $course)
          <tr>
            <td>
              <span style="font-family: var(--font-numbers); color: #64748B;">{{ $course->cource_id }}</span>
            </td>
            <td>
              <span style="font-weight: 500; color: #F8FAFC;">{{ $course->cource_name }}</span>
            </td>
            <td>
              @if($course->cource_category)
                <span class="badge badge-success">{{ $course->cource_category == 1 ? 'グループ' : 'プライベート' }}</span>
              @else
                <span style="color: #64748B;">-</span>
              @endif
            </td>
            <td>{{ $course->cource_level ?: '-' }}</td>
            <td>
              @if ($course->status === 1)
                <span class="badge badge-success">有効</span>
              @else
                <span class="badge badge-danger">無効</span>
              @endif
            </td>
            <td style="text-align: right;">
              <div class="action-buttons" style="justify-content: flex-end;">
                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-secondary btn-sm">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  編集
                </a>
                <a href="{{ route('admin.courses.categories.index', $course) }}" class="btn btn-secondary btn-sm">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                  カテゴリ
                </a>
                @if ($course->status === 1)
                  <form method="post" action="{{ route('admin.courses.destroy', $course) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('このコースを無効化しますか？')">
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
            <td colspan="6" style="text-align: center; padding: 3rem; color: #64748B;">
              <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 1rem; opacity: 0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              <div>コースがまだ登録されていません</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if ($courses->hasPages())
    <div style="margin-top: 2rem; display: flex; justify-content: center;">
      {{ $courses->links() }}
    </div>
  @endif
@endsection
