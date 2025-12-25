@extends('admin.layout')

@section('title', $staff->exists ? 'スタッフ編集' : 'スタッフ作成')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">{{ $staff->exists ? 'スタッフ編集' : 'スタッフ作成' }}</h1>
      @if($staff->exists)
        <p style="margin: 0; color: #64748B; font-size: 0.875rem;">ID: {{ $staff->staff_id }}</p>
      @endif
    </div>
    <div class="header-actions">
      <a href="{{ route('admin.staffs.index') }}" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        一覧へ戻る
      </a>
    </div>
  </div>

  {{-- Form Card --}}
  <div class="glass-card reveal reveal-delay-1">
    <form method="post" action="{{ $staff->exists ? route('admin.staffs.update', $staff) : route('admin.staffs.store') }}">
      @csrf
      @if ($staff->exists)
        @method('PUT')
      @endif

      <div style="display: grid; gap: 1.5rem;">
        {{-- Basic Info Section --}}
        <div class="content-section">
          <h3 class="section-title">基本情報</h3>

          <div class="form-group">
            <label for="staff_name" class="form-label">スタッフ名</label>
            <input
              id="staff_name"
              name="staff_name"
              class="form-input"
              value="{{ old('staff_name', $staff->staff_name) }}"
              maxlength="100"
              placeholder="例: 山田 太郎"
              required
            >
          </div>

          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="staff_gender" class="form-label">性別</label>
              <input
                id="staff_gender"
                name="staff_gender"
                class="form-input"
                value="{{ old('staff_gender', $staff->staff_gender) }}"
                maxlength="10"
                placeholder="例: 男性"
              >
            </div>

            <div class="form-group">
              <label for="staff_age" class="form-label">年齢</label>
              <input
                id="staff_age"
                name="staff_age"
                type="number"
                min="0"
                max="255"
                class="form-input"
                value="{{ old('staff_age', $staff->staff_age) }}"
                placeholder="例: 30"
              >
            </div>
          </div>

          <div class="form-group">
            <label for="staff_role" class="form-label">役割</label>
            <input
              id="staff_role"
              name="staff_role"
              class="form-input"
              value="{{ old('staff_role', $staff->staff_role) }}"
              maxlength="100"
              placeholder="例: インストラクター"
            >
          </div>
        </div>

        {{-- Skills Section --}}
        <div class="content-section">
          <h3 class="section-title">スキル・専門</h3>

          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="licence_skill" class="form-label">資格/スキル</label>
              <input
                id="licence_skill"
                name="licence_skill"
                class="form-input"
                value="{{ old('licence_skill', $staff->licence_skill) }}"
                maxlength="200"
                placeholder="例: ヨガインストラクター資格"
              >
            </div>

            <div class="form-group">
              <label for="main_expertise" class="form-label">得意分野</label>
              <input
                id="main_expertise"
                name="main_expertise"
                class="form-input"
                value="{{ old('main_expertise', $staff->main_expertise) }}"
                maxlength="200"
                placeholder="例: ハタヨガ、瞑想"
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
              placeholder="スタッフの詳細な説明を入力..."
            >{{ old('description', $staff->description) }}</textarea>
          </div>
        </div>

        {{-- Status Section --}}
        <div class="content-section">
          <h3 class="section-title">ステータス</h3>

          <div class="form-group" style="max-width: 300px;">
            <label for="status" class="form-label">公開状態</label>
            <select id="status" name="status" class="form-select" required>
              <option value="1" @selected((int) old('status', $staff->status ?? 1) === 1)>✓ 有効</option>
              <option value="9" @selected((int) old('status', $staff->status ?? 1) === 9)>✗ 無効</option>
            </select>
          </div>
        </div>

        {{-- Form Actions --}}
        <div style="display: flex; gap: 1rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
          <button type="submit" class="btn btn-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $staff->exists ? '更新する' : '作成する' }}
          </button>

          <a href="{{ route('admin.staffs.index') }}" class="btn btn-secondary">
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
