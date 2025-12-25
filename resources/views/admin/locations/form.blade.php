@extends('admin.layout')

@section('title', $location->exists ? 'ロケーション編集' : 'ロケーション作成')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">{{ $location->exists ? 'ロケーション編集' : 'ロケーション作成' }}</h1>
      @if($location->exists)
        <p style="margin: 0; color: #64748B; font-size: 0.875rem;">ID: {{ $location->location_id }}</p>
      @endif
    </div>
    <div class="header-actions">
      <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        一覧へ戻る
      </a>
    </div>
  </div>

  {{-- Form Card --}}
  <div class="glass-card reveal reveal-delay-1">
    <form method="post" action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}">
      @csrf
      @if ($location->exists)
        @method('PUT')
      @endif

      <div style="display: grid; gap: 1.5rem;">
        {{-- Basic Info Section --}}
        <div class="content-section">
          <h3 class="section-title">基本情報</h3>

          <div class="form-group">
            <label for="location_name" class="form-label">ロケーション名</label>
            <input
              id="location_name"
              name="location_name"
              class="form-input"
              value="{{ old('location_name', $location->location_name) }}"
              maxlength="100"
              placeholder="例: 渋谷スタジオ"
              required
            >
          </div>

          <div class="form-group">
            <label for="location_address" class="form-label">住所</label>
            <input
              id="location_address"
              name="location_address"
              class="form-input"
              value="{{ old('location_address', $location->location_address) }}"
              maxlength="200"
              placeholder="例: 東京都渋谷区○○1-2-3"
            >
          </div>
        </div>

        {{-- Contact Section --}}
        <div class="content-section">
          <h3 class="section-title">連絡先</h3>

          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="location_tel" class="form-label">電話番号</label>
              <input
                id="location_tel"
                name="location_tel"
                class="form-input"
                value="{{ old('location_tel', $location->location_tel) }}"
                maxlength="20"
                placeholder="例: 03-1234-5678"
              >
            </div>

            <div class="form-group">
              <label for="location_mail" class="form-label">メールアドレス</label>
              <input
                id="location_mail"
                name="location_mail"
                type="email"
                class="form-input"
                value="{{ old('location_mail', $location->location_mail) }}"
                maxlength="50"
                placeholder="例: shibuya@example.com"
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
              placeholder="ロケーションの詳細な説明を入力..."
            >{{ old('description', $location->description) }}</textarea>
          </div>
        </div>

        {{-- Status Section --}}
        <div class="content-section">
          <h3 class="section-title">ステータス</h3>

          <div class="form-group" style="max-width: 300px;">
            <label for="status" class="form-label">公開状態</label>
            <select id="status" name="status" class="form-select" required>
              <option value="1" @selected((int) old('status', $location->status ?? 1) === 1)>✓ 有効</option>
              <option value="9" @selected((int) old('status', $location->status ?? 1) === 9)>✗ 無効</option>
            </select>
          </div>
        </div>

        {{-- Form Actions --}}
        <div style="display: flex; gap: 1rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
          <button type="submit" class="btn btn-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $location->exists ? '更新する' : '作成する' }}
          </button>

          <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
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
