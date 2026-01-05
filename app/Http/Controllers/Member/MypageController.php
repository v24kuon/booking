<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Member $member */
        $member = $request->user();

        $reservationTable = (new Reservation)->getTable();
        $sessionTable = (new Session)->getTable();
        $sessionStartAtSubquery = Session::query()
            ->select('start_at')
            ->whereColumn("{$sessionTable}.session_id", "{$reservationTable}.session_id")
            ->limit(1);

        $activeReservations = $member->reservations()
            ->where('reserve_status', 1)
            ->with(['session.program', 'session.location', 'session.staff', 'contract.plan'])
            ->orderBy($sessionStartAtSubquery)
            ->get();

        $canceledReservations = $member->reservations()
            ->where('reserve_status', 9)
            ->with(['session.program'])
            ->orderBy($sessionStartAtSubquery)
            ->get();

        $contracts = $member->contracts()
            ->with('plan')
            ->orderBy('contract_id')
            ->get();

        return view('member.mypage', compact(
            'member',
            'activeReservations',
            'canceledReservations',
            'contracts'
        ));
    }
}
