<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Services\IdGenerator;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $staffs = Staff::query()
            ->orderBy('staff_id')
            ->paginate(50);

        return view('admin.staffs.index', compact('staffs'));
    }

    public function create()
    {
        $staff = new Staff;

        return view('admin.staffs.form', compact('staff'));
    }

    public function store(Request $request, IdGenerator $idGenerator)
    {
        $data = $request->validate([
            'staff_name' => ['required', 'string', 'max:100'],
            'staff_gender' => ['nullable', 'string', 'max:10'],
            'staff_age' => ['nullable', 'integer', 'min:0', 'max:255'],
            'licence_skill' => ['nullable', 'string', 'max:200'],
            'main_expertise' => ['nullable', 'string', 'max:200'],
            'staff_role' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $now = now();

        $staff = new Staff;
        $staff->staff_id = $idGenerator->next('staff', 'SF', 5);
        $staff->crt_time = $now;
        $staff->upd_time = $now;
        $staff->fill($data);
        $staff->save();

        return redirect()
            ->route('admin.staffs.index')
            ->with('status', 'スタッフを作成しました。');
    }

    public function edit(Staff $staff)
    {
        return view('admin.staffs.form', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'staff_name' => ['required', 'string', 'max:100'],
            'staff_gender' => ['nullable', 'string', 'max:10'],
            'staff_age' => ['nullable', 'integer', 'min:0', 'max:255'],
            'licence_skill' => ['nullable', 'string', 'max:200'],
            'main_expertise' => ['nullable', 'string', 'max:200'],
            'staff_role' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $staff->fill($data);
        $staff->upd_time = now();
        $staff->save();

        return redirect()
            ->route('admin.staffs.index')
            ->with('status', 'スタッフを更新しました。');
    }

    public function destroy(Staff $staff)
    {
        $staff->status = 9;
        $staff->upd_time = now();
        $staff->save();

        return redirect()
            ->route('admin.staffs.index')
            ->with('status', 'スタッフを無効化しました。');
    }
}
