@extends('admin.layout')

@section('title', $plan->exists ? 'プラン編集' : 'プラン作成')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">{{ $plan->exists ? 'プラン編集' : 'プラン作成' }}</h1>
      @if($plan->exists)
        <p style="margin: 0; color: #64748B; font-size: 0.875rem;">ID: {{ $plan->plan_id }}</p>
      @endif
    </div>
    <div class="header-actions">
      <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        一覧へ戻る
      </a>
    </div>
  </div>

  {{-- Form Card --}}
  <div class="glass-card reveal reveal-delay-1">
    <form method="post" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}">
      @csrf
      @if ($plan->exists)
        @method('PUT')
      @endif

      <div style="display: grid; gap: 1.5rem;">
        {{-- Basic Info Section --}}
        <div class="content-section">
          <h3 class="section-title">基本情報</h3>

          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
            <div class="form-group">
              <label for="plan_type" class="form-label">種別</label>
              <select id="plan_type" name="plan_type" class="form-select" required>
                <option value="1" @selected((int) old('plan_type', $plan->plan_type ?? 1) === 1)>月額（サブスク）</option>
                <option value="2" @selected((int) old('plan_type', $plan->plan_type ?? 1) === 2)>回数券</option>
                <option value="3" @selected((int) old('plan_type', $plan->plan_type ?? 1) === 3)>ポイント</option>
                <option value="4" @selected((int) old('plan_type', $plan->plan_type ?? 1) === 4)>コース</option>
              </select>
            </div>

            <div class="form-group">
              <label for="plan_name" class="form-label">プラン名</label>
              <input
                id="plan_name"
                name="plan_name"
                class="form-input"
                value="{{ old('plan_name', $plan->plan_name) }}"
                maxlength="200"
                placeholder="例: プレミアム月額プラン"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="cource_id" class="form-label">コース（※サブスクの場合は必須）</label>
            <select id="cource_id" name="cource_id" class="form-select">
              <option value="">-</option>
              @foreach ($courses as $course)
                <option value="{{ $course->cource_id }}" @selected(old('cource_id', $plan->cource_id) === $course->cource_id)>
                  {{ $course->cource_id }} / {{ $course->cource_name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Usage Section --}}
        <div class="content-section">
          <h3 class="section-title">付与・有効期限</h3>

          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <div class="form-group">
              <label for="plan_usage_count" class="form-label">付与量</label>
              <input
                id="plan_usage_count"
                name="plan_usage_count"
                type="number"
                min="0"
                class="form-input"
                value="{{ old('plan_usage_count', $plan->plan_usage_count ?? 0) }}"
                required
              >
            </div>

            <div class="form-group">
              <label for="plan_usage_date" class="form-label">有効日数</label>
              <input
                id="plan_usage_date"
                name="plan_usage_date"
                type="number"
                min="0"
                class="form-input"
                value="{{ old('plan_usage_date', $plan->plan_usage_date ?? 0) }}"
                required
              >
            </div>

            <div class="form-group">
              <label for="plan_price" class="form-label">価格（円）</label>
              <input
                id="plan_price"
                name="plan_price"
                type="number"
                min="0"
                class="form-input"
                value="{{ old('plan_price', $plan->plan_price) }}"
                placeholder="0"
              >
            </div>
          </div>
        </div>

        {{-- Stripe Section --}}
        <div class="content-section">
          <h3 class="section-title">Stripe連携</h3>

          <div class="form-group">
            <label for="stripe_price_id" class="form-label">Stripe Price ID</label>
            <input
              id="stripe_price_id"
              name="stripe_price_id"
              class="form-input"
              value="{{ old('stripe_price_id', data_get($plan->additional_info, 'stripe_price_id')) }}"
              maxlength="255"
              placeholder="price_xxxxxxxxxxxxxxxxxx"
            >
          </div>
        </div>

        {{-- Status Section --}}
        <div class="content-section">
          <h3 class="section-title">ステータス</h3>

          <div class="form-group" style="max-width: 300px;">
            <label for="status" class="form-label">公開状態</label>
            <select id="status" name="status" class="form-select" required>
              <option value="1" @selected((int) old('status', $plan->status ?? 1) === 1)>✓ 有効</option>
              <option value="9" @selected((int) old('status', $plan->status ?? 1) === 9)>✗ 無効</option>
            </select>
          </div>
        </div>

        {{-- Form Actions --}}
        <div style="display: flex; gap: 1rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
          <button type="submit" class="btn btn-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $plan->exists ? '更新する' : '作成する' }}
          </button>

          <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
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
