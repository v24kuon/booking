<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Member;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Member $member */
        $member = $request->user();

        $reservations = Reservation::query()
            ->where('member_id', $member->getKey())
            ->with(['session.program', 'session.location', 'session.staff', 'contract.plan'])
            ->get();

        // Relationship-based ordering (avoid manual joins / raw table usage)
        $reservations = $reservations
            ->sortBy(fn (Reservation $reservation) => $reservation->session?->start_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values();

        $activeReservations = $reservations->filter(fn (Reservation $reservation) => (int) $reservation->reserve_status === 1);
        $canceledReservations = $reservations->filter(fn (Reservation $reservation) => (int) $reservation->reserve_status === 9);

        $contracts = Contract::query()
            ->where('member_id', $member->getKey())
            ->with('plan')
            ->orderBy('contract_id')
            ->get();

        return view('member.mypage', compact(
            'member',
            'reservations',
            'activeReservations',
            'canceledReservations',
            'contracts'
        ));
    }
}
