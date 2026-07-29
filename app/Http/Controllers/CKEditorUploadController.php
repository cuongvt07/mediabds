<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Nhận ảnh chèn từ editor CKEditor (viết bài blog) và trả URL theo định dạng
 * CKEditor mong đợi: { "url": "..." } (hoặc { "error": { "message": "..." } }).
 *
 * Lưu vào disk 's3' (S3 cũ - Long Vân) như các thao tác admin khác, prefix
 * blog-content/Y/m; ghi vào thư viện file nếu có bảng files.
 */
class CKEditorUploadController extends Controller
{
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'upload' => 'required|image|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
        ], [], ['upload' => 'ảnh']);

        if ($validator->fails()) {
            return response()->json(['error' => ['message' => $validator->errors()->first('upload')]], 422);
        }

        try {
            $file = $request->file('upload');
            $disk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
            $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
            $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'blog';
            $filename = $name . '_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(5)) . '.' . $ext;
            $dir = 'blog-content/' . now()->format('Y/m');

            $path = $file->storeAs($dir, $filename, ['disk' => $disk, 'visibility' => 'public']);

            $url = $disk === 's3'
                ? rtrim((string) config('filesystems.disks.s3.endpoint'), '/') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path
                : Storage::disk($disk)->url($path);

            if (Schema::hasTable('files')) {
                \App\Models\File::create([
                    'folder_id' => null,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => $disk,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'metadata' => ['public_url' => $url],
                    'user_id' => auth()->id(),
                ]);
            }

            return response()->json(['url' => $url]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => ['message' => 'Tải ảnh thất bại: ' . $e->getMessage()]], 500);
        }
    }
}
