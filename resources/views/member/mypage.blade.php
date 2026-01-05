@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
  <h1>マイページ</h1>

  <article>
    <header>
      <strong>{{ $member->member_name ?? '会員' }}</strong>
    </header>
    <p style="margin-bottom: 0;">
      会員ID: {{ $member->member_id }}
    </p>
  </article>

  <p>
    <a href="{{ route('member.sessions.index') }}">枠一覧を見る</a>
  </p>

  <h2>予約一覧</h2>

  @if($activeReservations->isEmpty())
    <p>現在、予約はありません。</p>
  @else
    <table>
      <thead>
      <tr>
        <th>日時</th>
        <th>プログラム</th>
        <th>種別</th>
        <th>契約</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($activeReservations as $reservation)
        <tr>
          <td>{{ $reservation->session?->start_at?->format('Y-m-d H:i') }}</td>
          <td>{{ $reservation->session?->program?->program_name }}</td>
          <td>{{ (int) $reservation->reserve_type === \App\Models\Reservation::TYPE_TRIAL ? '体験' : '通常' }}</td>
          <td>
            @if($reservation->contract)
              {{ $reservation->contract->plan?->plan_name }}（{{ $reservation->contract_id }}）
            @else
              -
            @endif
          </td>
          <td>
            <form method="post" action="{{ route('member.reservations.cancel', $reservation) }}" style="margin:0;">
              @csrf
              <button type="submit" class="secondary" onclick="return confirm('予約をキャンセルしますか？');">キャンセル</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endif

  @if($canceledReservations->isNotEmpty())
    <details style="margin-top: 1rem;">
      <summary>キャンセル済み（{{ $canceledReservations->count() }}件）</summary>
      <table>
        <thead>
        <tr>
          <th>日時</th>
          <th>プログラム</th>
          <th>種別</th>
          <th>キャンセル日時</th>
        </tr>
        </thead>
        <tbody>
        @foreach($canceledReservations as $reservation)
          <tr>
            <td>{{ $reservation->session?->start_at?->format('Y-m-d H:i') }}</td>
            <td>{{ $reservation->session?->program?->program_name }}</td>
            <td>{{ (int) $reservation->reserve_type === \App\Models\Reservation::TYPE_TRIAL ? '体験' : '通常' }}</td>
            <td>{{ $reservation->canceled_at?->format('Y-m-d H:i') }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </details>
  @endif

  <h2 style="margin-top: 2rem;">契約</h2>
  @if($contracts->isEmpty())
    <p>利用できる契約はありません。</p>
  @else
    <table>
      <thead>
      <tr>
        <th>契約ID</th>
        <th>プラン</th>
        <th>残数</th>
        <th>期限</th>
      </tr>
      </thead>
      <tbody>
      @foreach($contracts as $contract)
        <tr>
          <td>{{ $contract->contract_id }}</td>
          <td>{{ $contract->plan?->plan_name }}</td>
          <td>{{ (int) $contract->plan_remain_count }}</td>
          <td>{{ $contract->plan_limit_date?->toDateString() ?? '無期限' }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endif
@endsection
