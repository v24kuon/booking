@props(['cancelRoute' => null, 'submitLabel' => '保存'])

<div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
  <button type="submit" class="btn btn-success">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ $submitLabel }}
  </button>

  @if($cancelRoute)
    <a href="{{ $cancelRoute }}" class="btn btn-secondary">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
      キャンセル
    </a>
  @endif
</div>
