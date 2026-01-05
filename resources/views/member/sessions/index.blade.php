@extends('layouts.app')

@section('title', '枠一覧')

@section('content')
  <h1>枠一覧</h1>

  <p>予約したい枠を選択してください。</p>

  <table>
    <thead>
    <tr>
      <th>日時</th>
      <th>プログラム</th>
      <th>場所</th>
      <th>スタッフ</th>
      <th>通常</th>
      <th>体験</th>
      <th></th>
    </tr>
    </thead>
    <tbody>
    @forelse($sessions as $session)
      @php
        $normalLeft = max(0, (int) $session->capacity - (int) $session->reserved_count);
        $trialLeft = max(0, (int) $session->exp_capacity - (int) $session->reserved_exp_count);
      @endphp
      <tr>
        <td>
          {{ $session->start_at?->format('Y-m-d H:i') }}
          〜
          {{ $session->end_at?->format('H:i') }}
        </td>
        <td>{{ $session->program?->program_name }}</td>
        <td>{{ $session->location?->location_name }}</td>
        <td>{{ $session->staff?->staff_name }}</td>
        <td>{{ $normalLeft }} / {{ (int) $session->capacity }}</td>
        <td>{{ $trialLeft }} / {{ (int) $session->exp_capacity }}</td>
        <td>
          <a href="{{ route('member.sessions.reserve', $session) }}">予約する</a>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7">表示できる枠がありません。</td>
      </tr>
    @endforelse
    </tbody>
  </table>

  {{ $sessions->links() }}
@endsection

