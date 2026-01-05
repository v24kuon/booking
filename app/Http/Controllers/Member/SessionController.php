<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Session;
use App\Services\ReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(): View
    {
        $sessions = Session::query()
            ->with(['program', 'location', 'staff'])
            ->where('status', 1)
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->paginate(50);

        return view('member.sessions.index', compact('sessions'));
    }

    public function reserve(Request $request, Session $session, ReservationService $reservationService): View
    {
        /** @var Member $member */
        $member = $request->user();

        $session->load(['program', 'location', 'staff']);
        $deadlines = $reservationService->deadlines();
        $candidates = $reservationService->normalCandidates($member, $session);

        $alreadyReserved = Reservation::query()
            ->where('member_id', $member->getKey())
            ->where('session_id', $session->getKey())
            ->exists();

        return view('member.sessions.reserve', compact('session', 'deadlines', 'candidates', 'alreadyReserved'));
    }
}
