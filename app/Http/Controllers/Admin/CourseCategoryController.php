<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseProgram;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseCategoryController extends Controller
{
    public function index(Course $course)
    {
        $availableCategories = Program::query()
            ->select('program_category')
            ->distinct()
            ->orderBy('program_category')
            ->pluck('program_category')
            ->values();

        $selectedCategories = $course->categorySets()
            ->orderBy('program_category')
            ->pluck('program_category')
            ->values();

        return view('admin.courses.categories', compact('course', 'availableCategories', 'selectedCategories'));
    }

    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'program_category' => [
                'required',
                'string',
                'max:50',
                Rule::exists('program_master', 'program_category'),
            ],
        ]);

        $exists = CourseProgram::query()
            ->where('cource_id', $course->cource_id)
            ->where('program_category', $data['program_category'])
            ->exists();

        if (! $exists) {
            CourseProgram::query()->create([
                'cource_id' => $course->cource_id,
                'program_category' => $data['program_category'],
                'crt_time' => now(),
                'upd_time' => now(),
            ]);
        }

        return redirect()
            ->route('admin.courses.categories.index', $course)
            ->with('status', 'カテゴリを追加しました。');
    }

    public function destroy(Request $request, Course $course)
    {
        $data = $request->validate([
            'program_category' => ['required', 'string', 'max:50'],
        ]);

        CourseProgram::query()
            ->where('cource_id', $course->cource_id)
            ->where('program_category', $data['program_category'])
            ->delete();

        return redirect()
            ->route('admin.courses.categories.index', $course)
            ->with('status', 'カテゴリを削除しました。');
    }
}
