@if ($errors->any())
  <article aria-label="入力エラー" style="border-left: 4px solid #EF4444; padding: 1rem;">
    <strong>入力内容を確認してください</strong>
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </article>
@endif

