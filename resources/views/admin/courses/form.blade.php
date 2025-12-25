@extends('admin.layout')

@section('title', $course->exists ? 'コース編集' : 'コース作成')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">{{ $course->exists ? 'コース編集' : 'コース作成' }}</h1>
      @if($course->exists)
        <p style="margin: 0; color: #64748B; font-size: 0.875rem;">ID: {{ $course->cource_id }}</p>
      @endif
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

  {{-- Form Card --}}
  <div class="glass-card reveal reveal-delay-1">
    <form method="post" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}">
      @csrf
      @if ($course->exists)
        @method('PUT')
      @endif

      <div style="display: grid; gap: 1.5rem;">
        {{-- Basic Info Section --}}
        <div class="content-section">
          <h3 class="section-title">基本情報</h3>

          <div class="form-group">
            <label for="cource_name" class="form-label">コース名</label>
            <input
              id="cource_name"
              name="cource_name"
              class="form-input"
              value="{{ old('cource_name', $course->cource_name) }}"
              maxlength="100"
              placeholder="例: グループヨガ初級"
              required
            >
          </div>

          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="cource_category" class="form-label">種別</label>
              <select id="cource_category" name="cource_category" class="form-select">
                <option value="" @selected(old('cource_category', $course->cource_category) === null || old('cource_category', $course->cource_category) === '')>-</option>
                <option value="1" @selected((string) old('cource_category', $course->cource_category) === '1')>グループ</option>
                <option value="2" @selected((string) old('cource_category', $course->cource_category) === '2')>プライベート</option>
              </select>
            </div>

            <div class="form-group">
              <label for="cource_level" class="form-label">レベル</label>
              <input
                id="cource_level"
                name="cource_level"
                class="form-input"
                value="{{ old('cource_level', $course->cource_level) }}"
                maxlength="50"
                placeholder="例: 初級"
              >
            </div>
          </div>
        </div>

        {{-- Details Section --}}
        <div class="content-section">
          <h3 class="section-title">詳細情報</h3>

          <div class="form-group">
            <label for="description" class="form-label">詳細説明</label>
            <textarea
              id="description"
              name="description"
              class="form-textarea"
              maxlength="500"
              placeholder="コースの詳細な説明を入力..."
            >{{ old('description', $course->description) }}</textarea>
          </div>
        </div>

        {{-- Status Section --}}
        <div class="content-section">
          <h3 class="section-title">ステータス</h3>

          <div class="form-group" style="max-width: 300px;">
            <label for="status" class="form-label">公開状態</label>
            <select id="status" name="status" class="form-select" required>
              <option value="1" @selected((int) old('status', $course->status ?? 1) === 1)>✓ 有効</option>
              <option value="9" @selected((int) old('status', $course->status ?? 1) === 9)>✗ 無効</option>
            </select>
          </div>
        </div>

        {{-- Form Actions --}}
        <div style="display: flex; gap: 1rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
          <button type="submit" class="btn btn-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $course->exists ? '更新する' : '作成する' }}
          </button>

          <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            キャンセル
          </a>
        </div>
      </div>
    </form>
  </div>
@endsection
