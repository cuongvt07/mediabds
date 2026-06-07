<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingImageUploadController extends BaseApiController
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'images' => 'required|array|min:1|max:30',
            'images.*' => 'required|file|image|mimes:jpg,jpeg,png,webp,heic,heif|max:12288',
        ]);

        $disk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        $prefix = 'listing-uploads/' . now()->format('Y/m');
        $items = [];

        foreach ($data['images'] as $file) {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'listing';
            $filename = $name . '-' . Str::lower(Str::random(10)) . '.' . $extension;
            $path = $file->storeAs($prefix, $filename, ['disk' => $disk, 'visibility' => 'public']);

            $url = $disk === 's3'
                ? rtrim((string) config('filesystems.disks.s3.endpoint'), '/') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path
                : Storage::disk($disk)->url($path);

            $items[] = [
                'url' => $url,
                'path' => $path,
                'disk' => $disk,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ];
        }

        return $this->ok($items, 'Uploaded');
    }
}
