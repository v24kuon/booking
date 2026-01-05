@if (session('status') || session('success'))
  <article aria-label="通知" style="border-left: 4px solid #10B981; padding: 1rem;">
    {{ session('status') ?? session('success') }}
  </article>
@endif

@if (session('error'))
  <article aria-label="エラー" style="border-left: 4px solid #EF4444; padding: 1rem;">
    {{ session('error') }}
  </article>
@endif

