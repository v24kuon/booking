@extends('admin.layout')

@section('title', $session->exists ? 'セッション編集' : 'セッション作成')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">{{ $session->exists ? 'セッション編集' : 'セッション作成' }}</h1>
      @if($session->exists)
        <p style="margin: 0; color: #64748B; font-size: 0.875rem;">ID: {{ $session->session_id }}</p>
      @endif
    </div>
    <div class="header-actions">
      <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        一覧へ戻る
      </a>
    </div>
  </div>

  @php
    $startValue = old('start_at', $session->start_at?->format('Y-m-d\\TH:i'));
    $endValue = old('end_at', $session->end_at?->format('Y-m-d\\TH:i'));
  @endphp

  {{-- Form Card --}}
  <div class="glass-card reveal reveal-delay-1">
    <form method="post" action="{{ $session->exists ? route('admin.sessions.update', $session) : route('admin.sessions.store') }}">
      @csrf
      @if ($session->exists)
        @method('PUT')
      @endif

      <div style="display: grid; gap: 1.5rem;">
        {{-- Assignment Section --}}
        <div class="content-section">
          <h3 class="section-title">割り当て</h3>

          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="program_id" class="form-label">プログラム</label>
              <select id="program_id" name="program_id" class="form-select" required>
                @foreach ($programs as $program)
                  <option value="{{ $program->program_id }}" @selected(old('program_id', $session->program_id) === $program->program_id)>
                    {{ $program->program_name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="location_id" class="form-label">ロケーション</label>
              <select id="location_id" name="location_id" class="form-select" required>
                @foreach ($locations as $location)
                  <option value="{{ $location->location_id }}" @selected(old('location_id', $session->location_id) === $location->location_id)>
                    {{ $location->location_name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="staff_id" class="form-label">スタッフ</label>
              <select id="staff_id" name="staff_id" class="form-select" required>
                @foreach ($staffs as $staff)
                  <option value="{{ $staff->staff_id }}" @selected(old('staff_id', $session->staff_id) === $staff->staff_id)>
                    {{ $staff->staff_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Schedule Section --}}
        <div class="content-section">
          <h3 class="section-title">スケジュール</h3>

          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="start_at" class="form-label">開始日時</label>
              <input
                id="start_at"
                type="datetime-local"
                name="start_at"
                class="form-input"
                value="{{ $startValue }}"
                required
              >
            </div>

            <div class="form-group">
              <label for="end_at" class="form-label">終了日時</label>
              <input
                id="end_at"
                type="datetime-local"
                name="end_at"
                class="form-input"
                value="{{ $endValue }}"
                required
              >
            </div>
          </div>
        </div>

        {{-- Capacity Section --}}
        <div class="content-section">
          <h3 class="section-title">定員設定</h3>

          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="capacity" class="form-label">通常定員</label>
              <input
                id="capacity"
                type="number"
                name="capacity"
                min="0"
                class="form-input"
                value="{{ old('capacity', $session->capacity ?? 0) }}"
                required
              >
              <small style="color: #64748B; font-size: 0.75rem;">通常会員向けの定員数</small>
            </div>

            <div class="form-group">
              <label for="exp_capacity" class="form-label">体験定員</label>
              <input
                id="exp_capacity"
                type="number"
                name="exp_capacity"
                min="0"
                class="form-input"
                value="{{ old('exp_capacity', $session->exp_capacity ?? 0) }}"
                required
              >
              <small style="color: #64748B; font-size: 0.75rem;">体験参加者向けの定員数</small>
            </div>
          </div>
        </div>

        {{-- Status Section --}}
        <div class="content-section">
          <h3 class="section-title">ステータス</h3>

          <div class="form-group" style="max-width: 300px;">
            <label for="status" class="form-label">状態</label>
            <select id="status" name="status" class="form-select" required>
              <option value="1" @selected((int) old('status', $session->status ?? 1) === 1)>✓ 予約受付中</option>
              <option value="2" @selected((int) old('status', $session->status ?? 1) === 2)>◯ 終了</option>
              <option value="9" @selected((int) old('status', $session->status ?? 1) === 9)>✗ 中止</option>
            </select>
          </div>
        </div>

        {{-- Form Actions --}}
        <div style="display: flex; gap: 1rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
          <button type="submit" class="btn btn-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $session->exists ? '更新する' : '作成する' }}
          </button>

          <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary">
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
