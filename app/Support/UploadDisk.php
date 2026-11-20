<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Disk dùng cho MỌI file upload mới: s3_uploads (AZ Cloud, bucket vm24h).
 * Thiếu env thì fallback s3 (Long Vân) rồi public để không gián đoạn.
 * File cũ trên Long Vân vẫn đọc qua disk 's3' theo cột `disk` đã lưu.
 */
class UploadDisk
{
    /** Prefix chứa file riêng tư (ảnh CCCD eKYC) — không được liệt kê/đồng bộ ra FileManager. */
    public const PRIVATE_PREFIXES = ['vault-ekyc/'];

    public static function name(): string
    {
        if (config('filesystems.disks.s3_uploads.bucket')) {
            return 's3_uploads';
        }

        return config('filesystems.disks.s3.bucket') ? 's3' : 'public';
    }

    /** URL public của file: endpoint/bucket/path với S3, Storage::url với disk local. */
    public static function url(string $path, ?string $disk = null): string
    {
        $disk ??= self::name();
        $cfg = config("filesystems.disks.$disk");

        return ! empty($cfg['endpoint'])
            ? rtrim((string) $cfg['endpoint'], '/') . '/' . $cfg['bucket'] . '/' . ltrim($path, '/')
            : Storage::disk($disk)->url($path);
    }
}
