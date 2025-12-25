<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\IdGenerator;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::query()
            ->orderBy('location_id')
            ->paginate(50);

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        $location = new Location;

        return view('admin.locations.form', compact('location'));
    }

    public function store(Request $request, IdGenerator $idGenerator)
    {
        $data = $request->validate([
            'location_name' => ['required', 'string', 'max:100'],
            'location_address' => ['nullable', 'string', 'max:200'],
            'location_tel' => ['nullable', 'string', 'max:20'],
            'location_mail' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $now = now();

        $location = new Location;
        $location->location_id = $idGenerator->next('location', 'L', 5);
        $location->crt_time = $now;
        $location->upd_time = $now;
        $location->fill($data);
        $location->save();

        return redirect()
            ->route('admin.locations.index')
            ->with('status', 'ロケーションを作成しました。');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.form', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'location_name' => ['required', 'string', 'max:100'],
            'location_address' => ['nullable', 'string', 'max:200'],
            'location_tel' => ['nullable', 'string', 'max:20'],
            'location_mail' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'integer', 'in:1,9'],
        ]);

        $location->fill($data);
        $location->upd_time = now();
        $location->save();

        return redirect()
            ->route('admin.locations.index')
            ->with('status', 'ロケーションを更新しました。');
    }

    public function destroy(Location $location)
    {
        $location->status = 9;
        $location->upd_time = now();
        $location->save();

        return redirect()
            ->route('admin.locations.index')
            ->with('status', 'ロケーションを無効化しました。');
    }
}
