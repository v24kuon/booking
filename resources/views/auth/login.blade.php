@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
  <h1>ログイン</h1>

  @include('admin.partials.errors')

  <form method="post" action="{{ url('/login') }}">
    @csrf

    <label>
      メールアドレス
      <input name="member_mail" type="email" value="{{ old('member_mail') }}" required autofocus>
    </label>

    <label>
      パスワード
      <input name="password" type="password" required>
    </label>

    <button type="submit">ログイン</button>
  </form>
@endsection
