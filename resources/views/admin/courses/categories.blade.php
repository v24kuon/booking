@extends('admin.layout')

@section('title', 'コースカテゴリ')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">コースカテゴリ</h1>
      <p style="margin: 0; color: #64748B; font-size: 0.875rem;">{{ $course->cource_id }} / {{ $course->cource_name }}</p>
    </div>
    <div class="header-actions">
      <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        一覧へ戻る
      </a>
    </div>
  </div>

  {{-- Add Category Form --}}
  <div class="glass-card reveal reveal-delay-1" style="margin-bottom: 2rem;">
    <h3 class="section-title">カテゴリを追加</h3>

    <form method="post" action="{{ route('admin.courses.categories.store', $course) }}">
      @csrf

      <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
          <label for="program_category" class="form-label">カテゴリ</label>
          <select id="program_category" name="program_category" class="form-select" required>
            @foreach ($availableCategories as $category)
              <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn btn-primary">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          追加
        </button>
      </div>
    </form>
  </div>

  {{-- Current Categories --}}
  <div class="glass-card reveal reveal-delay-2">
    <h3 class="section-title">設定済みカテゴリ</h3>

    @if(count($selectedCategories) > 0)
      <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
        @foreach ($selectedCategories as $category)
          <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(129, 140, 248, 0.1); border: 1px solid rgba(129, 140, 248, 0.2); border-radius: 8px; padding: 0.5rem 1rem;">
            <span style="color: #F8FAFC;">{{ $category }}</span>
            <form method="post" action="{{ route('admin.courses.categories.destroy', $course) }}" style="display: inline;">
              @csrf
              @method('DELETE')
              <input type="hidden" name="program_category" value="{{ $category }}">
              <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.5rem;" onclick="return confirm('削除しますか？')">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </form>
          </div>
        @endforeach
      </div>
    @else
      <p style="color: #64748B; text-align: center; padding: 2rem;">カテゴリが設定されていません</p>
    @endif
  </div>
@endsection
