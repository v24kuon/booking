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
            ->select('reseve_info.*')
            ->where('member_id', $member->getKey())
            ->join('session', 'session.session_id', '=', 'reseve_info.session_id')
            ->orderBy('session.start_at')
            ->with(['session.program', 'session.location', 'session.staff', 'contract.plan'])
            ->get();

        $contracts = Contract::query()
            ->where('member_id', $member->getKey())
            ->with('plan')
            ->orderBy('contract_id')
            ->get();

        return view('member.mypage', compact('member', 'reservations', 'contracts'));
    }
}
