@extends('admin.layout')

@section('title', $program->exists ? 'プログラム編集' : 'プログラム作成')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">{{ $program->exists ? 'プログラム編集' : 'プログラム作成' }}</h1>
      @if($program->exists)
        <p style="margin: 0; color: #64748B; font-size: 0.875rem;">ID: {{ $program->program_id }}</p>
      @endif
    </div>
    <div class="header-actions">
      <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        一覧へ戻る
      </a>
    </div>
  </div>

  {{-- Form Card --}}
  <div class="glass-card reveal reveal-delay-1">
    <form method="post" action="{{ $program->exists ? route('admin.programs.update', $program) : route('admin.programs.store') }}">
      @csrf
      @if ($program->exists)
        @method('PUT')
      @endif

      <div style="display: grid; gap: 1.5rem;">
        {{-- Basic Info Section --}}
        <div class="content-section">
          <h3 class="section-title">基本情報</h3>

          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
            <div class="form-group">
              <label for="program_category" class="form-label">カテゴリ</label>
              <input
                id="program_category"
                name="program_category"
                class="form-input"
                value="{{ old('program_category', $program->program_category) }}"
                maxlength="50"
                placeholder="例: ヨガ"
                required
              >
            </div>

            <div class="form-group">
              <label for="program_name" class="form-label">プログラム名</label>
              <input
                id="program_name"
                name="program_name"
                class="form-input"
                value="{{ old('program_name', $program->program_name) }}"
                maxlength="100"
                placeholder="例: 朝ヨガ60分"
                required
              >
            </div>
          </div>
        </div>

        {{-- Pricing Section --}}
        <div class="content-section">
          <h3 class="section-title">消費ポイント・料金</h3>

          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="program_point" class="form-label">ポイント消費</label>
              <input
                id="program_point"
                name="program_point"
                type="number"
                min="0"
                class="form-input"
                value="{{ old('program_point', $program->program_point) }}"
                placeholder="0"
              >
            </div>

            <div class="form-group">
              <label for="program_ticket" class="form-label">回数券消費</label>
              <input
                id="program_ticket"
                name="program_ticket"
                type="number"
                min="0"
                class="form-input"
                value="{{ old('program_ticket', $program->program_ticket) }}"
                placeholder="0"
              >
            </div>

            <div class="form-group">
              <label for="program_price" class="form-label">料金（円）</label>
              <input
                id="program_price"
                name="program_price"
                type="number"
                min="0"
                class="form-input"
                value="{{ old('program_price', $program->program_price) }}"
                placeholder="0"
              >
            </div>
          </div>
        </div>

        {{-- Details Section --}}
        <div class="content-section">
          <h3 class="section-title">詳細情報</h3>

          <div class="form-group">
            <label for="program_overview" class="form-label">概要</label>
            <textarea
              id="program_overview"
              name="program_overview"
              class="form-textarea"
              maxlength="500"
              placeholder="プログラムの概要を入力..."
            >{{ old('program_overview', $program->program_overview) }}</textarea>
          </div>

          <div class="form-group">
            <label for="program_detail" class="form-label">詳細説明</label>
            <textarea
              id="program_detail"
              name="program_detail"
              class="form-textarea"
              maxlength="500"
              placeholder="プログラムの詳細な説明を入力..."
            >{{ old('program_detail', $program->program_detail) }}</textarea>
          </div>
        </div>

        {{-- Status Section --}}
        <div class="content-section">
          <h3 class="section-title">ステータス</h3>

          <div class="form-group" style="max-width: 300px;">
            <label for="status" class="form-label">公開状態</label>
            <select id="status" name="status" class="form-select" required>
              <option value="1" @selected((int) old('status', $program->status ?? 1) === 1)>✓ 有効</option>
              <option value="9" @selected((int) old('status', $program->status ?? 1) === 9)>✗ 無効</option>
            </select>
          </div>
        </div>

        {{-- Form Actions --}}
        <div style="display: flex; gap: 1rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
          <button type="submit" class="btn btn-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $program->exists ? '更新する' : '作成する' }}
          </button>

          <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
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
