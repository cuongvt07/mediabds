<?php

namespace App\Livewire;


use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileManager extends Component
{
    use WithFileUploads;

    public $currentFolderId = null;
    public $folders = [];
    public $files = [];
    public $selectedItems = [];
    public $viewMode = 'grid'; // grid or list
    public $uploadFiles = []; // For file uploads

    public function mount($folderId = null)
    {
        $this->currentFolderId = $folderId;
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->folders = Folder::where('parent_id', $this->currentFolderId)
            ->orderBy('name')
            ->get();
        
        $this->files = File::where('folder_id', $this->currentFolderId)
            ->orderBy('name')
            ->get();
    }

    public function getBreadcrumbs()
    {
        $breadcrumbs = [];
        $currentFolder = $this->currentFolderId ? Folder::find($this->currentFolderId) : null;

        while ($currentFolder) {
            array_unshift($breadcrumbs, $currentFolder);
            $currentFolder = $currentFolder->parent;
        }

        return $breadcrumbs;
    }

    public function goBack()
    {
        if ($this->currentFolderId) {
            $currentFolder = Folder::find($this->currentFolderId);
            $this->currentFolderId = $currentFolder?->parent_id;
            $this->loadItems();
        }
    }

    public function openFolder($folderId)
    {
        $this->currentFolderId = $folderId;
        $this->loadItems();
    }

    public function createFolder($name)
    {
        Folder::create([
            'parent_id' => $this->currentFolderId,
            'name' => $name,
            'path' => $this->buildPath($name),
        ]);
        $this->loadItems();
    }

    public function deleteItem($type, $id)
    {
        if ($type === 'folder') {
            Folder::find($id)?->delete();
        } else {
            $file = File::find($id);
            if ($file) {
                Storage::disk($file->disk)->delete($file->path);
                $file->delete();
            }
        }
        $this->loadItems();
    }

    private function buildPath($name)
    {
        if (!$this->currentFolderId) {
            return $name;
        }
        $parent = Folder::find($this->currentFolderId);
        return ($parent->path ?? '') . '/' . $name;
    }

    public function saveUploadedFile($uploadedFilename, $originalName, $mimeType, $size)
    {
        \Log::info('🔵 Starting file upload process', [
            'filename' => $originalName,
            'size' => number_format($size / 1024 / 1024, 2) . ' MB',
            'mime_type' => $mimeType,
            'uploaded_filename' => $uploadedFilename,
        ]);

        // The uploaded filename is already the temp file path
        if (!$uploadedFilename) {
            \Log::error('❌ No uploaded filename provided', ['filename' => $originalName]);
            return;
        }

        \Log::info('📁 Processing temp file, uploading to Longvan S3...', [
            'temp_file' => $uploadedFilename,
        ]);

        try {
            // Get the file from Livewire's temporary storage (stored in private disk)
            $tempPath = storage_path('app/private/livewire-tmp/' . $uploadedFilename);
            
            // Fallback to check in app/livewire-tmp if not found
            if (!file_exists($tempPath)) {
                $tempPath = storage_path('app/livewire-tmp/' . $uploadedFilename);
            }
            
            if (!file_exists($tempPath)) {
                throw new \Exception('Temp file not found in either location. Checked: ' . $tempPath);
            }

            \Log::info('📂 Temp file found', ['path' => $tempPath]);

            // Base structure: Year (e.g. 2026)
            $baseYear = date('Y');
            $currentMonth = date('m');
            
            // Determine folder name if inside a folder
            $folderName = '';
            if ($this->currentFolderId) {
                // Find current folder name
                $currentFolder = Folder::find($this->currentFolderId);
                if ($currentFolder) {
                    // Slugify folder name for safe URL (e.g. "Dự Án A" -> "Du-An-A")
                    $folderName = \Illuminate\Support\Str::slug($currentFolder->name);
                }
            }

            // Construct Storage Path
            if ($folderName) {
                // Structure: 2026/folder-name/01
                $storagePath = "{$baseYear}/{$folderName}/{$currentMonth}";
            } else {
                // Structure: 2026/01 (Root)
                $storagePath = "{$baseYear}/{$currentMonth}";
            }
            
            // Upload to S3 with public visibility and ORIGINAL filename
            $s3Path = Storage::disk('s3')->putFileAs(
                $storagePath, 
                new \Illuminate\Http\File($tempPath),
                $originalName, 
                'public'
            );
            
            // Generate public URL
            $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $s3Path;
            
            \Log::info('✅ File uploaded to Longvan S3 successfully!', [
                'filename' => $originalName,
                's3_path' => $s3Path,
                'public_url' => $publicUrl,
                'bucket' => config('filesystems.disks.s3.bucket'),
                'endpoint' => config('filesystems.disks.s3.endpoint'),
            ]);

            // Save to database
            $fileRecord = File::create([
                'folder_id' => $this->currentFolderId,
                'name' => $originalName,
                'path' => $s3Path,
                'disk' => 's3',
                'mime_type' => $mimeType,
                'size' => $size,
                'metadata' => [
                    'public_url' => $publicUrl,
                ],
            ]);

            \Log::info('💾 File metadata saved to database', [
                'filename' => $originalName,
                'folder_id' => $this->currentFolderId,
                'public_url' => $publicUrl,
            ]);

            // Delete temp file
            @unlink($tempPath);
            \Log::info('🗑️ Temporary file deleted from local storage');
            
        } catch (\Exception $e) {
            \Log::error('❌ S3 Upload failed!', [
                'filename' => $originalName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
        
        // Reload items
        $this->loadItems();
    }

    public function render()
    {
        return view('livewire.file-manager');
    }
}
