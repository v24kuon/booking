<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Plan;
use App\Services\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::query()
            ->orderBy('plan_id')
            ->paginate(50);

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $plan = new Plan;
        $courses = Course::query()->orderBy('cource_id')->get();

        return view('admin.plans.form', compact('plan', 'courses'));
    }

    public function store(Request $request, IdGenerator $idGenerator)
    {
        $data = $request->validate([
            'plan_type' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'plan_name' => ['required', 'string', 'max:200'],
            'cource_id' => ['nullable', 'string', 'max:8', Rule::exists('cource_master', 'cource_id')],
            'plan_usage_count' => ['required', 'integer', 'min:0'],
            'plan_usage_date' => ['required', 'integer', 'min:0'],
            'plan_price' => ['nullable', 'numeric', 'min:0'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        if ((int) $data['plan_type'] === 1 && empty($data['cource_id'])) {
            return back()
                ->withErrors(['cource_id' => '月額（サブスク）の場合はコースIDが必須です。'])
                ->withInput();
        }

        $now = now();

        $plan = new Plan;
        $plan->plan_id = $idGenerator->next('plan', 'PL', 6);
        $plan->crt_time = $now;
        $plan->upd_time = $now;

        $plan->fill([
            'plan_type' => $data['plan_type'],
            'plan_name' => $data['plan_name'],
            'cource_id' => $data['cource_id'] ?? null,
            'plan_usage_count' => $data['plan_usage_count'],
            'plan_usage_date' => $data['plan_usage_date'],
            'plan_price' => $data['plan_price'] ?? null,
            'status' => $data['status'],
        ]);

        $additional = (array) ($plan->additional_info ?? []);
        if (! empty($data['stripe_price_id'])) {
            $additional['stripe_price_id'] = $data['stripe_price_id'];
        } else {
            unset($additional['stripe_price_id']);
        }
        $plan->additional_info = $additional;

        $plan->save();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'プランを作成しました。');
    }

    public function edit(Plan $plan)
    {
        $courses = Course::query()->orderBy('cource_id')->get();

        return view('admin.plans.form', compact('plan', 'courses'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'plan_type' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'plan_name' => ['required', 'string', 'max:200'],
            'cource_id' => ['nullable', 'string', 'max:8', Rule::exists('cource_master', 'cource_id')],
            'plan_usage_count' => ['required', 'integer', 'min:0'],
            'plan_usage_date' => ['required', 'integer', 'min:0'],
            'plan_price' => ['nullable', 'numeric', 'min:0'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        if ((int) $data['plan_type'] === 1 && empty($data['cource_id'])) {
            return back()
                ->withErrors(['cource_id' => '月額（サブスク）の場合はコースIDが必須です。'])
                ->withInput();
        }

        $plan->fill([
            'plan_type' => $data['plan_type'],
            'plan_name' => $data['plan_name'],
            'cource_id' => $data['cource_id'] ?? null,
            'plan_usage_count' => $data['plan_usage_count'],
            'plan_usage_date' => $data['plan_usage_date'],
            'plan_price' => $data['plan_price'] ?? null,
            'status' => $data['status'],
        ]);

        $additional = (array) ($plan->additional_info ?? []);
        if (! empty($data['stripe_price_id'])) {
            $additional['stripe_price_id'] = $data['stripe_price_id'];
        } else {
            unset($additional['stripe_price_id']);
        }
        $plan->additional_info = $additional;

        $plan->upd_time = now();
        $plan->save();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'プランを更新しました。');
    }

    public function destroy(Plan $plan)
    {
        $plan->status = 9;
        $plan->upd_time = now();
        $plan->save();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'プランを無効化しました。');
    }
}
