@props(['title', 'createRoute' => null, 'createLabel' => '新規作成'])

<div class="page-header-section reveal">
  <h1 class="page-title">{{ $title }}</h1>
  @if($createRoute)
    <div class="header-actions">
      <a href="{{ $createRoute }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ $createLabel }}
      </a>
    </div>
  @endif
</div>
