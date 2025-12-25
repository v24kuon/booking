<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Services\IdGenerator;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::query()
            ->orderBy('program_id')
            ->paginate(50);

        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        $program = new Program;

        return view('admin.programs.form', compact('program'));
    }

    public function store(Request $request, IdGenerator $idGenerator)
    {
        $data = $request->validate([
            'program_category' => ['required', 'string', 'max:50'],
            'program_name' => ['required', 'string', 'max:100'],
            'program_level' => ['nullable', 'string', 'max:50'],
            'program_overview' => ['nullable', 'string', 'max:500'],
            'program_detail' => ['nullable', 'string', 'max:500'],
            'program_price' => ['nullable', 'numeric', 'min:0'],
            'program_point' => ['nullable', 'integer', 'min:0'],
            'program_ticket' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $now = now();

        $program = new Program;
        $program->program_id = $idGenerator->next('program', 'PR', 6);
        $program->crt_time = $now;
        $program->upd_time = $now;
        $program->fill($data);
        $program->save();

        return redirect()
            ->route('admin.programs.index')
            ->with('status', 'プログラムを作成しました。');
    }

    public function edit(Program $program)
    {
        return view('admin.programs.form', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'program_category' => ['required', 'string', 'max:50'],
            'program_name' => ['required', 'string', 'max:100'],
            'program_level' => ['nullable', 'string', 'max:50'],
            'program_overview' => ['nullable', 'string', 'max:500'],
            'program_detail' => ['nullable', 'string', 'max:500'],
            'program_price' => ['nullable', 'numeric', 'min:0'],
            'program_point' => ['nullable', 'integer', 'min:0'],
            'program_ticket' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $program->fill($data);
        $program->upd_time = now();
        $program->save();

        return redirect()
            ->route('admin.programs.index')
            ->with('status', 'プログラムを更新しました。');
    }

    public function destroy(Program $program)
    {
        $program->status = 9;
        $program->upd_time = now();
        $program->save();

        return redirect()
            ->route('admin.programs.index')
            ->with('status', 'プログラムを無効化しました。');
    }
}
