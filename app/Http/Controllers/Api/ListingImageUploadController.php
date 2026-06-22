<?php

namespace App\Http\Controllers\Api;

use App\Models\SiteSetting;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingImageUploadController extends BaseApiController
{
    public function store(Request $request, WatermarkService $watermark)
    {
        $maxCount = (int) SiteSetting::get('upload.max_count', 20);
        $maxKb = (int) SiteSetting::get('upload.max_size_mb', 5) * 1024;

        $data = $request->validate([
            'images' => "required|array|min:1|max:$maxCount",
            'images.*' => "required|file|image|mimes:jpg,jpeg,png,webp,heic,heif|max:$maxKb",
        ]);

        $disk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        $prefix = 'listing-uploads/' . now()->format('Y/m');
        $items = [];

        foreach ($data['images'] as $file) {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'listing';
            $filename = $name . '-' . Str::lower(Str::random(10)) . '.' . $extension;
            $path = $prefix . '/' . $filename;

            // Watermark in-memory when enabled & format is supported; otherwise store as-is.
            $stamped = $watermark->apply($file->getRealPath(), $file->getMimeType());

            if ($stamped !== null) {
                Storage::disk($disk)->put($path, $stamped, ['visibility' => 'public']);
            } else {
                $path = $file->storeAs($prefix, $filename, ['disk' => $disk, 'visibility' => 'public']);
            }

            $url = $disk === 's3'
                ? rtrim((string) config('filesystems.disks.s3.endpoint'), '/') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path
                : Storage::disk($disk)->url($path);

            $items[] = [
                'url' => $url,
                'path' => $path,
                'disk' => $disk,
                'name' => $file->getClientOriginalName(),
                'size' => $stamped !== null ? strlen($stamped) : $file->getSize(),
                'mime' => $file->getMimeType(),
                'watermarked' => $stamped !== null,
            ];
        }

        return $this->ok($items, 'Uploaded');
    }
}
