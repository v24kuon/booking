@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
  <h1>マイページ</h1>

  <article>
    <header>
      <strong>{{ auth()->user()->member_name ?? '会員' }}</strong>
    </header>
    <p>ここに会員向けメニューを追加していきます。</p>
  </article>
@endsection
