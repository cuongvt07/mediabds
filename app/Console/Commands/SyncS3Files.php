<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SyncS3Files extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:sync-s3 {prefix? : The folder prefix to scan (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan S3 and sync files to the database (Optimized)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prefix = $this->argument('prefix') ?? '';
        $this->info("Scanning S3 files" . ($prefix ? " with prefix: $prefix" : " from root") . " [Optimized Mode]...");

        try {
            // Get low-level S3 client for access to ListObjectsV2 pagination
            $client = Storage::disk('s3')->getClient();
            $bucket = config('filesystems.disks.s3.bucket');
            
            // Use Paginator to fetch in batches (1000 items per request)
            // This avoids the expensive 'HeadObject' call for every single file
            $results = $client->getPaginator('ListObjectsV2', [
                'Bucket' => $bucket,
                'Prefix' => $prefix,
            ]);

            $count = 0;
            $synced = 0;

            foreach ($results as $result) {
                if (empty($result['Contents'])) continue;
                
                foreach ($result['Contents'] as $object) {
                    $key = $object['Key'];
                    
                    // Skip folders (keys ending in /) and hidden files
                    if (substr($key, -1) === '/' || Str::startsWith(basename($key), '.')) continue;

                    $this->syncFile($object);
                    $count++;
                    if ($count % 50 === 0) {
                         $this->info("Processed $count files...");
                    }
                }
            }

            $this->newLine();
            $this->info("Sync completed! Processed $count files.");

        } catch (\Exception $e) {
            $this->error("Error connecting to S3: " . $e->getMessage());
        }
    }

    private function syncFile($s3Object)
    {
        $filePath = $s3Object['Key'];
        $size = $s3Object['Size'] ?? 0;
        
        // Quick check to avoid DB writes if exists
        // Optimizing: Check presence with a lightweight query or cache if really needed, 
        // but a indexed string query is fast enough for <10k files.
        if (File::where('path', $filePath)->exists()) {
            return;
        }

        // Determine parent folder structure
        $directory = dirname($filePath);
        $folderId = null;

        if ($directory && $directory !== '.') {
            $folderId = $this->getOrCreateDirectory($directory); 
        }

        $name = basename($filePath);
        $mimeType = $this->guessMimeType($name);
        
        // Generate public URL
        $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $filePath;

        File::create([
            'folder_id' => $folderId,
            'name' => $name,
            'path' => $filePath,
            'disk' => 's3',
            'mime_type' => $mimeType,
            'size' => $size,
            'metadata' => ['public_url' => $publicUrl],
        ]);
        
        // $this->line("Synced: $name");
    }

    // Cache folder lookups to avoid thousands of DB queries
    private $folderCache = [];

    private function getOrCreateDirectory($path)
    {
        if (isset($this->folderCache[$path])) {
            return $this->folderCache[$path];
        }

        $parts = explode('/', $path);
        $parentId = null;
        $currentPath = '';

        foreach ($parts as $part) {
            if (!$part) continue;

            $currentPath = $currentPath ? "$currentPath/$part" : $part;

            // Check cache for this level
            if (isset($this->folderCache[$currentPath])) {
                $parentId = $this->folderCache[$currentPath];
                continue;
            }

            // DB Lookup/Create
            $folder = Folder::firstOrCreate(
                [
                    'name' => $part,
                    'parent_id' => $parentId
                ],
                [
                    'path' => $currentPath
                ]
            );

            $parentId = $folder->id;
            $this->folderCache[$currentPath] = $folder->id;
        }
        
        return $parentId;
    }

    private function guessMimeType($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'pdf' => 'application/pdf',
            'doc', 'docx' => 'application/msword',
            'xls', 'xlsx' => 'application/vnd.ms-excel',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
