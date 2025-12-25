<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Program;
use App\Models\Session;
use App\Models\Staff;
use App\Services\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = Session::query()
            ->with(['program', 'location', 'staff'])
            ->orderByDesc('start_at')
            ->paginate(50);

        return view('admin.sessions.index', compact('sessions'));
    }

    public function create()
    {
        $session = new Session;
        $programs = Program::query()->where('status', 1)->orderBy('program_id')->get();
        $locations = Location::query()->where('status', 1)->orderBy('location_id')->get();
        $staffs = Staff::query()->where('status', 1)->orderBy('staff_id')->get();

        return view('admin.sessions.form', compact('session', 'programs', 'locations', 'staffs'));
    }

    public function store(Request $request, IdGenerator $idGenerator)
    {
        $data = $request->validate([
            'program_id' => ['required', 'string', 'max:8', Rule::exists('program_master', 'program_id')],
            'location_id' => ['required', 'string', 'max:6', Rule::exists('location_master', 'location_id')],
            'staff_id' => ['required', 'string', 'max:7', Rule::exists('staff_master', 'staff_id')],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'exp_capacity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'integer', Rule::in([1, 2, 9])],
        ]);

        $now = now();

        $session = new Session;
        $session->session_id = $idGenerator->next('session', 'SS', 8);
        $session->crt_time = $now;
        $session->upd_time = $now;
        $session->fill([
            'program_id' => $data['program_id'],
            'location_id' => $data['location_id'],
            'staff_id' => $data['staff_id'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'capacity' => $data['capacity'],
            'exp_capacity' => $data['exp_capacity'],
            'reserved_count' => 0,
            'reserved_exp_count' => 0,
            'status' => $data['status'],
        ]);
        $session->save();

        return redirect()
            ->route('admin.sessions.index')
            ->with('status', '枠（session）を作成しました。');
    }

    public function edit(Session $session)
    {
        $programs = Program::query()->where('status', 1)->orderBy('program_id')->get();
        $locations = Location::query()->where('status', 1)->orderBy('location_id')->get();
        $staffs = Staff::query()->where('status', 1)->orderBy('staff_id')->get();

        return view('admin.sessions.form', compact('session', 'programs', 'locations', 'staffs'));
    }

    public function update(Request $request, Session $session)
    {
        $data = $request->validate([
            'program_id' => ['required', 'string', 'max:8', Rule::exists('program_master', 'program_id')],
            'location_id' => ['required', 'string', 'max:6', Rule::exists('location_master', 'location_id')],
            'staff_id' => ['required', 'string', 'max:7', Rule::exists('staff_master', 'staff_id')],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'exp_capacity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'integer', Rule::in([1, 2, 9])],
        ]);

        if ((int) $data['capacity'] < (int) $session->reserved_count) {
            return back()
                ->withErrors(['capacity' => '現在の予約数より小さくできません。'])
                ->withInput();
        }

        if ((int) $data['exp_capacity'] < (int) $session->reserved_exp_count) {
            return back()
                ->withErrors(['exp_capacity' => '現在の体験予約数より小さくできません。'])
                ->withInput();
        }

        $session->fill([
            'program_id' => $data['program_id'],
            'location_id' => $data['location_id'],
            'staff_id' => $data['staff_id'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'capacity' => $data['capacity'],
            'exp_capacity' => $data['exp_capacity'],
            'status' => $data['status'],
        ]);
        $session->upd_time = now();
        $session->save();

        return redirect()
            ->route('admin.sessions.index')
            ->with('status', '枠（session）を更新しました。');
    }

    public function destroy(Session $session)
    {
        $session->status = 9;
        $session->upd_time = now();
        $session->save();

        return redirect()
            ->route('admin.sessions.index')
            ->with('status', '枠（session）を中止（status=9）にしました。');
    }
}
