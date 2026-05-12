<?php

namespace App\Http\Controllers;

use App\Models\File as FileModel;
use Illuminate\Http\Request;

class QuickImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|image|max:20480',
        ]);

        $file = $request->file('file');

        $filenameOnly = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filenameOnly);
        $uniqueSuffix = time() . '_' . substr(uniqid(), -4);
        $filename = $safeFilename . '_' . $uniqueSuffix . '.' . $extension;

        $path = $file->storeAs(date('Y/m'), $filename, ['disk' => 's3', 'visibility' => 'public']);

        $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path;

        FileModel::create([
            'folder_id' => null,
            'name' => $filename,
            'path' => $path,
            'disk' => 's3',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'metadata' => [
                'source' => 'real_estate_quick_upload',
                'public_url' => $publicUrl,
            ],
        ]);

        return response()->json(['url' => $publicUrl]);
    }
}
