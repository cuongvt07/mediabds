<?php

namespace App\Console\Commands;

use App\Services\WatermarkService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Re-stamps images already stored on disk with the current watermark settings
 * so the whole site — not just new uploads — carries the mark.
 *
 * Idempotent: processed keys are recorded in a manifest on the same disk and
 * skipped on the next run. Use --force to re-stamp everything (note: this stacks
 * a second watermark on top of images already stamped by a previous run).
 */
class WatermarkBackfill extends Command
{
    protected $signature = 'media:watermark-backfill
        {--prefix=listing-uploads : Folder prefix to scan}
        {--disk= : Force a disk (s3|public); auto-detected when omitted}
        {--dry-run : List what would change without writing}
        {--force : Re-stamp even files recorded as already done}';

    protected $description = 'Apply the current watermark to images already stored on disk';

    private const MANIFEST = '_watermark/backfill-done.json';

    private const SUPPORTED_EXT = ['jpg', 'jpeg', 'png', 'webp'];

    public function handle(WatermarkService $watermark): int
    {
        $prefix = trim((string) $this->option('prefix'), '/');
        $disk = (string) ($this->option('disk') ?: (config('filesystems.disks.s3.bucket') ? 's3' : 'public'));
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info("Disk: {$disk} | Prefix: {$prefix}" . ($dryRun ? ' | DRY-RUN' : '') . ($force ? ' | FORCE' : ''));

        try {
            $keys = $this->listImageKeys($disk, $prefix);
        } catch (Throwable $e) {
            $this->error('Cannot list files: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Found ' . count($keys) . ' image(s).');
        if ($keys === []) {
            return self::SUCCESS;
        }

        $done = $force ? [] : $this->loadManifest($disk);
        $doneSet = array_fill_keys($done, true);

        $processed = 0;
        $skipped = 0;
        $failed = 0;
        $newlyDone = $done;

        foreach ($keys as $key) {
            if (isset($doneSet[$key])) {
                $skipped++;

                continue;
            }

            $mime = $this->mimeForKey($key);
            if ($mime === null) {
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line("would stamp: {$key}");
                $processed++;

                continue;
            }

            try {
                $raw = Storage::disk($disk)->get($key);
                if ($raw === null || $raw === '') {
                    $failed++;

                    continue;
                }

                $stamped = $watermark->stampBinary($raw, $mime, false);
                if ($stamped === null) {
                    // Unsupported/undecodable — count as failed so it is not marked done.
                    $failed++;

                    continue;
                }

                Storage::disk($disk)->put($key, $stamped, ['visibility' => 'public']);
                $newlyDone[] = $key;
                $processed++;

                if ($processed % 25 === 0) {
                    $this->info("Stamped {$processed}...");
                    $this->saveManifest($disk, $newlyDone);
                }
            } catch (Throwable $e) {
                $failed++;
                $this->warn("failed {$key}: " . $e->getMessage());
            }
        }

        if (! $dryRun) {
            $this->saveManifest($disk, $newlyDone);
        }

        $this->newLine();
        $this->info("Done. Stamped: {$processed} | Skipped: {$skipped} | Failed: {$failed}");

        return self::SUCCESS;
    }

    /** All object keys under the prefix (recursive), across s3 or a Flysystem disk. */
    private function listImageKeys(string $disk, string $prefix): array
    {
        if ($disk === 's3') {
            $client = Storage::disk('s3')->getClient();
            $bucket = config('filesystems.disks.s3.bucket');
            $paginator = $client->getPaginator('ListObjectsV2', [
                'Bucket' => $bucket,
                'Prefix' => $prefix,
            ]);

            $keys = [];
            foreach ($paginator as $page) {
                foreach ($page['Contents'] ?? [] as $object) {
                    $key = $object['Key'];
                    if (substr($key, -1) === '/' || Str::startsWith(basename($key), '.')) {
                        continue;
                    }
                    if ($this->mimeForKey($key) !== null) {
                        $keys[] = $key;
                    }
                }
            }

            return $keys;
        }

        return array_values(array_filter(
            Storage::disk($disk)->allFiles($prefix),
            fn ($key) => ! Str::startsWith(basename($key), '.') && $this->mimeForKey($key) !== null,
        ));
    }

    private function mimeForKey(string $key): ?string
    {
        $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        if (! in_array($ext, self::SUPPORTED_EXT, true)) {
            return null;
        }

        return match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    private function loadManifest(string $disk): array
    {
        try {
            if (! Storage::disk($disk)->exists(self::MANIFEST)) {
                return [];
            }
            $decoded = json_decode((string) Storage::disk($disk)->get(self::MANIFEST), true);

            return is_array($decoded) ? array_values(array_unique(array_filter($decoded, 'is_string'))) : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function saveManifest(string $disk, array $keys): void
    {
        try {
            Storage::disk($disk)->put(
                self::MANIFEST,
                json_encode(array_values(array_unique($keys)), JSON_UNESCAPED_SLASHES),
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}
