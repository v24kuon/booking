<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\CancelReservationRequest;
use App\Http\Requests\Member\StoreNormalReservationRequest;
use App\Http\Requests\Member\StoreTrialReservationRequest;
use App\Models\Member;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;

class ReservationController extends Controller
{
    public function storeNormal(StoreNormalReservationRequest $request, ReservationService $reservationService): RedirectResponse
    {
        /** @var Member $member */
        $member = $request->user();

        $reservationService->createNormalReservation(
            member: $member,
            sessionId: (string) $request->string('session_id'),
            contractId: (string) $request->string('contract_id'),
            channel: 'web'
        );

        return redirect()
            ->route('mypage')
            ->with('status', '予約が完了しました。');
    }

    public function storeTrial(StoreTrialReservationRequest $request, ReservationService $reservationService): RedirectResponse
    {
        /** @var Member $member */
        $member = $request->user();

        $reservationService->createTrialReservation(
            member: $member,
            sessionId: (string) $request->string('session_id'),
            channel: 'web'
        );

        return redirect()
            ->route('mypage')
            ->with('status', '体験予約が完了しました。');
    }

    public function cancel(
        CancelReservationRequest $request,
        Reservation $reservation,
        ReservationService $reservationService
    ): RedirectResponse {
        /** @var Member $member */
        $member = $request->user();

        $reservationService->cancelReservation(
            member: $member,
            reservation: $reservation,
            cancelReason: $request->input('cancel_reason')
        );

        return redirect()
            ->route('mypage')
            ->with('status', '予約をキャンセルしました。');
    }
}
