@extends('admin.layout')

@section('title', 'スタッフ管理')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <h1 class="page-title">スタッフ管理</h1>
    <div class="header-actions">
      <a href="{{ route('admin.staffs.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        新規スタッフ
      </a>
    </div>
  </div>

  {{-- Stats Summary --}}
  <div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card reveal reveal-delay-1">
      <div class="stat-icon indigo">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      <div class="stat-label">総スタッフ数</div>
      <div class="stat-value">{{ $staffs->total() }}</div>
    </div>
    <div class="stat-card reveal reveal-delay-2">
      <div class="stat-icon emerald">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="stat-label">アクティブ</div>
      <div class="stat-value">{{ $staffs->where('status', 1)->count() }}</div>
    </div>
  </div>

  {{-- Data Table --}}
  <div class="glass-card reveal reveal-delay-3">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>スタッフ名</th>
          <th>役割</th>
          <th>ステータス</th>
          <th style="text-align: right;">アクション</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($staffs as $staff)
          <tr>
            <td>
              <span style="font-family: var(--font-numbers); color: #64748B;">{{ $staff->staff_id }}</span>
            </td>
            <td>
              <span style="font-weight: 500; color: #F8FAFC;">{{ $staff->staff_name }}</span>
            </td>
            <td>
              @if($staff->staff_role)
                <span class="badge badge-success">{{ $staff->staff_role }}</span>
              @else
                <span style="color: #64748B;">-</span>
              @endif
            </td>
            <td>
              @if ($staff->status === 1)
                <span class="badge badge-success">有効</span>
              @else
                <span class="badge badge-danger">無効</span>
              @endif
            </td>
            <td style="text-align: right;">
              <div class="action-buttons" style="justify-content: flex-end;">
                <a href="{{ route('admin.staffs.edit', $staff) }}" class="btn btn-secondary btn-sm">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  編集
                </a>
                <a href="{{ route('admin.staffs.images.index', $staff) }}" class="btn btn-secondary btn-sm">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  画像
                </a>
                @if ($staff->status === 1)
                  <form method="post" action="{{ route('admin.staffs.destroy', $staff) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('このスタッフを無効化しますか？')">
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
            <td colspan="5" style="text-align: center; padding: 3rem; color: #64748B;">
              <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 1rem; opacity: 0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <div>スタッフがまだ登録されていません</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if ($staffs->hasPages())
    <div style="margin-top: 2rem; display: flex; justify-content: center;">
      {{ $staffs->links() }}
    </div>
  @endif
@endsection
