@extends('admin.layout')

@section('title', 'ロケーション画像')

@section('content')
  {{-- Page Header --}}
  <div class="page-header-section reveal">
    <div>
      <h1 class="page-title">ロケーション画像</h1>
      <p style="margin: 0; color: #64748B; font-size: 0.875rem;">{{ $location->location_id }} / {{ $location->location_name }}</p>
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

  {{-- Upload Form --}}
  <div class="glass-card reveal reveal-delay-1" style="margin-bottom: 2rem;">
    <h3 class="section-title">画像をアップロード</h3>

    <form method="post" action="{{ route('admin.locations.images.store', $location) }}" enctype="multipart/form-data">
      @csrf

      <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
          <label for="img_type" class="form-label">種別</label>
          <select id="img_type" name="img_type" class="form-select" required>
            <option value="0">メイン</option>
            <option value="1">サブ1</option>
            <option value="2">サブ2</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label for="image" class="form-label">画像ファイル</label>
          <input
            id="image"
            type="file"
            name="image"
            accept="image/*"
            class="form-input"
            required
          >
        </div>

        <button type="submit" class="btn btn-primary">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          アップロード
        </button>
      </div>
    </form>
  </div>

  {{-- Current Images --}}
  <div class="glass-card reveal reveal-delay-2">
    <h3 class="section-title">登録済み画像</h3>

    @if($images->count() > 0)
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
        @foreach ($images as $image)
          <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; overflow: hidden;">
            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
              <span class="badge badge-success">種別: {{ $image->img_type == 0 ? 'メイン' : 'サブ' . $image->img_type }}</span>
              <form method="post" action="{{ route('admin.locations.images.destroy', [$location, $image]) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('この画像を削除しますか？')">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </form>
            </div>
            <div style="padding: 1rem;">
              <img src="{{ asset($image->img_path) }}" alt="" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
            </div>
          </div>
        @endforeach
      </div>
    @else
      <p style="color: #64748B; text-align: center; padding: 3rem;">
        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 1rem; opacity: 0.5;">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <br>画像が登録されていません
      </p>
    @endif
  </div>
@endsection
