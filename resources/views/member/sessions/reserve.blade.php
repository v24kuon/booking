@extends('layouts.app')

@section('title', '予約する')

@section('content')
  <h1>予約する</h1>

  @include('partials.errors')

  <article>
    <header>
      <strong>{{ $session->program?->program_name }}</strong>
    </header>
    <p style="margin-bottom: 0.5rem;">
      {{ $session->start_at?->format('Y-m-d H:i') }} 〜 {{ $session->end_at?->format('H:i') }}
    </p>
    <p style="margin-bottom: 0;">
      場所: {{ $session->location?->location_name }} / スタッフ: {{ $session->staff?->staff_name }}
    </p>
  </article>

  @if($alreadyReserved)
    <article aria-label="注意" style="border-left: 4px solid #F59E0B; padding: 1rem;">
      すでにこの枠を予約しています。マイページから確認できます。
      <div style="margin-top: 0.75rem;">
        <a href="{{ route('mypage') }}">マイページへ</a>
      </div>
    </article>
  @else
    @php
      $normalLeft = max(0, (int) $session->capacity - (int) $session->reserved_count);
      $trialLeft = max(0, (int) $session->exp_capacity - (int) $session->reserved_exp_count);

      $reserveDeadlineHours = (int) ($deadlines['reserve_deadline'] ?? 0);
      $reserveDeadlineAt = $reserveDeadlineHours > 0 && $session->start_at
        ? $session->start_at->copy()->subHours($reserveDeadlineHours)
        : null;
    @endphp

    @if($reserveDeadlineAt)
      <p>
        予約締切: {{ $reserveDeadlineAt->format('Y-m-d H:i') }}
      </p>
    @endif

    <h2>通常予約</h2>
    <p>通常枠: {{ $normalLeft }} / {{ (int) $session->capacity }}</p>

    @if($normalLeft <= 0)
      <article aria-label="満席" style="border-left: 4px solid #EF4444; padding: 1rem;">
        通常枠は満席です。
      </article>
    @elseif($candidates->isEmpty())
      <article aria-label="注意" style="border-left: 4px solid #F59E0B; padding: 1rem;">
        利用できる契約（ポイント/回数券/サブスク）がありません。
      </article>
    @else
      <form method="post" action="{{ route('member.reservations.normal.store') }}">
        @csrf
        <input type="hidden" name="session_id" value="{{ $session->session_id }}">

        <fieldset>
          <legend>利用する契約</legend>

          @foreach($candidates as $candidate)
            <label>
              <input
                type="radio"
                name="contract_id"
                value="{{ $candidate['contract']->contract_id }}"
                @checked(old('contract_id') === $candidate['contract']->contract_id || ($loop->first && !old('contract_id')))
                required
              >
              {{ $candidate['label'] }}
              （消費: {{ (int) $candidate['consume_amount'] }}）
            </label>
          @endforeach
        </fieldset>

        <button type="submit">通常予約する</button>
      </form>
    @endif

    <hr>

    <h2>体験予約（現金）</h2>
    <p>体験枠: {{ $trialLeft }} / {{ (int) $session->exp_capacity }}</p>

    @if($trialLeft <= 0)
      <article aria-label="満席" style="border-left: 4px solid #EF4444; padding: 1rem;">
        体験枠は満席です。
      </article>
    @else
      <form method="post" action="{{ route('member.reservations.trial.store') }}">
        @csrf
        <input type="hidden" name="session_id" value="{{ $session->session_id }}">
        <button type="submit" class="secondary">体験予約する</button>
      </form>
      <small>※ 体験カード（Stripe決済）は後続の実装で追加予定です。</small>
    @endif
  @endif

  <p style="margin-top: 1.5rem;">
    <a href="{{ route('member.sessions.index') }}">← 枠一覧へ戻る</a>
  </p>
@endsection

