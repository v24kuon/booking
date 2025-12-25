<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\IdGenerator;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::query()
            ->orderBy('cource_id')
            ->paginate(50);

        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $course = new Course;

        return view('admin.courses.form', compact('course'));
    }

    public function store(Request $request, IdGenerator $idGenerator)
    {
        $data = $request->validate([
            'cource_name' => ['required', 'string', 'max:100'],
            'cource_category' => ['nullable', 'string', 'max:3'],
            'cource_level' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $now = now();

        $course = new Course;
        $course->cource_id = $idGenerator->next('course', 'CR', 6);
        $course->crt_time = $now;
        $course->upd_time = $now;
        $course->fill($data);
        $course->save();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'コースを作成しました。');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'cource_name' => ['required', 'string', 'max:100'],
            'cource_category' => ['nullable', 'string', 'max:3'],
            'cource_level' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $course->fill($data);
        $course->upd_time = now();
        $course->save();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'コースを更新しました。');
    }

    public function destroy(Course $course)
    {
        $course->status = 9;
        $course->upd_time = now();
        $course->save();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'コースを無効化しました。');
    }
}
