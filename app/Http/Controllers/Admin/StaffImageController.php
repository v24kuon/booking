<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffImageController extends Controller
{
    public function index(Staff $staff)
    {
        $images = $staff->images()->orderBy('img_type')->get();

        return view('admin.staffs.images', compact('staff', 'images'));
    }

    public function store(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'img_type' => ['required', 'integer', 'min:0', 'max:9'],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $imgType = (int) $data['img_type'];
        $file = $request->file('image');

        $dir = public_path('uploads/staff/'.$staff->staff_id);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $typeLabel = match ($imgType) {
            0 => 'main',
            1 => 'sub1',
            2 => 'sub2',
            default => 'sub'.$imgType,
        };

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $typeLabel.'-'.now()->format('YmdHis').'-'.Str::random(8).'.'.$ext;
        $file->move($dir, $filename);

        $relativePath = 'uploads/staff/'.$staff->staff_id.'/'.$filename;

        $existing = StaffImage::query()
            ->where('staff_id', $staff->staff_id)
            ->where('img_type', $imgType)
            ->first();

        if ($existing) {
            $this->deletePublicFile($existing->img_path);
            $existing->img_path = $relativePath;
            $existing->upd_time = now();
            $existing->save();
        } else {
            $image = new StaffImage;
            $image->staff_id = $staff->staff_id;
            $image->img_type = $imgType;
            $image->img_path = $relativePath;
            $image->crt_time = now();
            $image->upd_time = now();
            $image->save();
        }

        return redirect()
            ->route('admin.staffs.images.index', $staff)
            ->with('status', '画像をアップロードしました。');
    }

    public function destroy(Request $request, Staff $staff, StaffImage $image)
    {
        if ($image->staff_id !== $staff->staff_id) {
            abort(404);
        }

        $this->deletePublicFile($image->img_path);
        $image->delete();

        return redirect()
            ->route('admin.staffs.images.index', $staff)
            ->with('status', '画像を削除しました。');
    }

    private function deletePublicFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $path = public_path($relativePath);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
