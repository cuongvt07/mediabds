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
    public $search = '';
    
    public $isFlatView = false; // "All Files" mode
    public $expandedFolders = []; // IDs of folders expanded in sidebar
    public $selectedFileForDetails = null;

    public $perPage = 24;
    public $loadingMore = false;
    public $hasMore = false;

    // Upload & Grouping state
    public $isGrouping = false;
    public $newGroupName = '';
    public $selectedFolderIdForUpload = null;
    public $targetParentId = null; // For Create Folder Modal
    
    // License State
    public $showLicenseModal = false;
    public $trialDaysLeft = 0;
    public $licenseKeyInput = '';
    public $licenseError = '';

    public $isModeSelect = false; // "Select from Media" mode

    
    public function mount($folderId = null, $isModeSelect = false)
    {
        $this->checkLicense();
        $this->currentFolderId = $folderId;
        $this->isModeSelect = $isModeSelect;
        $this->loadItems();
    }

    public function checkLicense()
    {
        $user = auth()->user();
        $masterKey = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', config('app.license_master_key')));
        
        $hasLicense = $user->license_key && $user->license_key === $masterKey;
        // Check if trial active: trial_ends_at is set AND is in the future
        $inTrial = $user->trial_ends_at && now()->lt($user->trial_ends_at);
        
        $this->trialDaysLeft = $inTrial ? now()->diffInDays($user->trial_ends_at, false) : 0;
        // Round up if less than 1 day but active? diffInDays rounds down.
        // If inTrial is true, at least < 24h. Let's use ceil or diffInHours/24 + 1
        if ($inTrial && $this->trialDaysLeft < 1) $this->trialDaysLeft = 1;

        // If NO license AND trial expired (or not set?), block.
        // Logic: Allow if (Has License) OR (In Trial).
        if (!$hasLicense && !$inTrial) {
            $this->showLicenseModal = true;
        }
    }

    public function activateLicense()
    {
        // Sanitize input: remove spaces and dashes
        $input = preg_replace('/[^a-zA-Z0-9]/', '', $this->licenseKeyInput);
        $masterKey = preg_replace('/[^a-zA-Z0-9]/', '', config('app.license_master_key'));
        
        // Normalize to uppercase for comparison
        $input = strtoupper($input);
        $masterKey = strtoupper($masterKey);

        \Log::info('License Activation Attempt', [
            'input_sanitized' => $input, 
            'master_key_sanitized' => $masterKey
        ]);

        if ($input === $masterKey) {
            auth()->user()->update([
                'license_key' => $masterKey,
                'license_expires_at' => now()->addYears(10) // Lifetime (limited by MySQL Timestamp 2038)
            ]);
            $this->showLicenseModal = false;
            $this->licenseError = '';
            $this->dispatch('toast', ['message' => 'License activated successfully!', 'type' => 'success']);
        } else {
            $this->licenseError = 'Invalid License Key. Please try again.';
        }
    }

    public function updatedSearch()
    {
        $this->perPage = 40;
        $this->loadItems();
    }

    public function loadMore()
    {
        $this->perPage += 40;
        $this->loadItems();
    }

    public function loadItems()
    {
        $folderQuery = Folder::query()
            ->with(['children'])
            ->orderBy('name');

        $fileQuery = File::query()
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            // Global Search: Search all files and folders by name
            $folderQuery->where('name', 'like', '%' . $this->search . '%');
            $fileQuery->where('name', 'like', '%' . $this->search . '%');
        } elseif ($this->isFlatView) {
            // Flat view: show all files, no folders
            // No specific query filters needed for files (shows all)
        } else {
            // Hierarchical view: filter by current folder
            $folderQuery->where('parent_id', $this->currentFolderId);
            $fileQuery->where('folder_id', $this->currentFolderId);
        }

        // Execute Queries
        if ($this->isFlatView && !$this->search) {
            $this->folders = collect();
        } else {
            $this->folders = $folderQuery->get();
        }

        $this->files = $fileQuery->paginate($this->perPage)->items();
        $this->hasMore = count($this->files) >= $this->perPage;
    }

    public function confirmSelection($items = [])
    {
        // $items can be passed from Alpine
        $ids = is_array($items) ? $items : $this->selectedItems;
        
        $selectedFiles = File::whereIn('id', $ids)->get();
        // Return array of URLs
        $urls = $selectedFiles->map(fn($f) => $f->metadata['public_url'] ?? '')->filter()->toArray();
        
        $this->dispatch('media-selected', ['images' => $urls]);
        $this->selectedItems = []; // Reset
    }

    public function toggleFlatView($state)
    {
        $this->isFlatView = $state;
        $this->perPage = 40;
        if ($state) {
            $this->currentFolderId = null;
        }
        $this->loadItems();
    }

    public function toggleFolder($folderId)
    {
        if (in_array($folderId, $this->expandedFolders)) {
            $this->expandedFolders = array_diff($this->expandedFolders, [$folderId]);
        } else {
            $this->expandedFolders[] = $folderId;
        }
    }

    public function selectFolder($folderId)
    {
        $this->currentFolderId = $folderId;
        $this->isFlatView = false;
        $this->perPage = 40; // Reset pagination on folder change
        
        if ($folderId && !in_array($folderId, $this->expandedFolders)) {
            $this->expandedFolders[] = $folderId;
        }

        $this->loadItems();
    }

    public function getRootFolders()
    {
        return Folder::whereNull('parent_id')->with('children')->get();
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

    // Computed property for the dropdown
    public function getAllFoldersProperty()
    {
        // Get all folders to build tree. 
        // Optimization: Fetch all and build tree in memory to avoid N+1 if we used recursive relationships without eager loading
        // But for simplicity and since we have getRootFolders, let's use that but we need ALL folders recursively.
        // Let's simple fetch all and organize.
        
        $allFolders = Folder::orderBy('name')->get();
        return $this->flattenTree($allFolders->whereNull('parent_id'), $allFolders);
    }

    private function flattenTree($nodes, $allFolders, $depth = 0)
    {
        $result = collect([]);
        
        foreach ($nodes as $node) {
            $node->depth_name = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '📂 ' . $node->name;
            $result->push($node);
            
            $children = $allFolders->where('parent_id', $node->id);
            if ($children->count() > 0) {
                $result = $result->merge($this->flattenTree($children, $allFolders, $depth + 1));
            }
        }
        
        return $result;
    }

    public function openCreateFolderModal()
    {
        $this->newFolderName = '';
        $this->targetParentId = $this->currentFolderId; // Default to current
        $this->dispatch('open-modal', 'createFolderModal');
    }

    public function openCreateFolderModalWithParent($parentId)
    {
        $this->newFolderName = '';
        $this->targetParentId = $parentId;
        $this->dispatch('open-modal', 'createFolderModal');
    }

    public function createFolder()
    {
        if (!$this->newFolderName) return;

        // Use the selected target parent, or fallback to current
        $parentId = $this->targetParentId;

        $this->createFolderAndGetId($this->newFolderName, $parentId);
        
        $this->newFolderName = '';
        $this->targetParentId = null;
        $this->dispatch('close-modal', 'createFolderModal');
        $this->loadItems();
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

    // Removed duplicate createFolder method

    public $itemToDeleteId = null;
    public $itemToDeleteType = null;

    public function requestDelete($type, $id)
    {
        $this->itemToDeleteType = $type;
        $this->itemToDeleteId = $id;
        $this->dispatch('open-modal', 'deleteConfirmationModal');
    }

    public function performDelete()
    {
        if (!$this->itemToDeleteId || !$this->itemToDeleteType) return;

        if ($this->itemToDeleteType === 'file') {
            $file = File::find($this->itemToDeleteId);
            if ($file) $file->delete(); // Soft Delete
        } elseif ($this->itemToDeleteType === 'folder') {
            $folder = Folder::find($this->itemToDeleteId);
            if ($folder) $folder->delete(); // Soft Delete
        }

        $this->itemToDeleteId = null;
        $this->itemToDeleteType = null;
        $this->dispatch('close-modal', 'deleteConfirmationModal');
        $this->dispatch('toast', ['message' => 'Item moved to trash', 'type' => 'success']);
        $this->loadItems();
    }

    public function downloadFile($fileId)
    {
        $file = File::find($fileId);
        // Use Storage::download to force a download response
        if ($file && $file->path) {
            return Storage::disk('s3')->download($file->path, $file->name);
        }
    }

    public function showFileDetails($fileId)
    {
        $this->selectedFileForDetails = File::findOrFail($fileId);
        $this->dispatch('open-modal', 'fileDetailsModal');
    }

    public function showUploadModal()
    {
        $this->isGrouping = false;
        $this->newGroupName = '';
        $this->selectedFolderIdForUpload = $this->currentFolderId;
        $this->dispatch('open-modal', 'uploadModal');
    }

    private function buildPath($name, $parentId = null)
    {
        $pid = $parentId ?? $this->currentFolderId;
        if (!$pid) {
            return $name;
        }
        $parent = Folder::find($pid);
        return ($parent->path ?? '') . '/' . $name;
    }

    public function saveUploadedFile($uploadedFilename, $originalName, $mimeType, $size, $targetFolderId = null)
    {
        // Use provided targetFolderId (e.g. from grouping) or current view
        $folderId = $targetFolderId ?? $this->currentFolderId;

        \Log::info('🔵 Starting file upload process', [
            'filename' => $originalName,
            'target_folder_id' => $folderId,
            'uploaded_filename' => $uploadedFilename,
        ]);

        if (!$uploadedFilename) return;

        try {
            $tempPath = storage_path('app/private/livewire-tmp/' . $uploadedFilename);
            if (!file_exists($tempPath)) $tempPath = storage_path('app/livewire-tmp/' . $uploadedFilename);
            if (!file_exists($tempPath)) throw new \Exception('Temp file not found.');

            
            $storagePath = '';
            if ($folderId) {
                $folder = Folder::find($folderId);
                // EXACT STRUCTURE: Use the folder's path
                if ($folder) $storagePath = $folder->path . '/' . $originalName;
            } else {
                 // Root upload: Use Date-based structure or just Root?
                 // User said "Right structure". If root, maybe just root?
                 // But let's keep date structure for Root to avoid clutter, 
                 // UNLESS user considers "Root" as a folder itself.
                 // Let's use date for root to prevent million files in bucket root.
                 $baseYear = date('Y');
                 $currentMonth = date('m');
                 $storagePath = "{$baseYear}/{$currentMonth}/{$originalName}";
            }
            
            // Note: putFileAs automatically prepends bucket if not careful, but here we provide 'path'
            // storagePath already includes filename
            $s3Path = Storage::disk('s3')->putFileAs(
                dirname($storagePath), // Path (without filename)
                new \Illuminate\Http\File($tempPath), // File
                basename($storagePath), // Filename
                'public'
            );
            
            $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $s3Path;
            
            File::create([
                'folder_id' => $folderId,
                'name' => $originalName,
                'path' => $s3Path,
                'disk' => 's3',
                'mime_type' => $mimeType,
                'size' => $size,
                'metadata' => ['public_url' => $publicUrl],
                'user_id' => auth()->id(),
            ]);

            @unlink($tempPath);
            
        } catch (\Exception $e) {
            \Log::error('❌ S3 Upload failed!', ['error' => $e->getMessage()]);
            throw $e;
        }
        
        // $this->loadItems();
    }

    public function createFolderAndGetId($name, $specificParentId = null)
    {
        // Use specific parent if provided, otherwise current
        $parentId = $specificParentId; 

        // Build path based on PARENT
        $parentFolder = $parentId ? Folder::find($parentId) : null;
        $path = $parentFolder ? $parentFolder->path . '/' . $name : $name;

        $folder = Folder::create([
            'parent_id' => $parentId,
            'name' => $name,
            'path' => $path,
        ]);
        return $folder->id;
    }

    // -- Sync S3 Logic --
    public $isSyncing = false;
    public $syncMessage = '';

    public function syncFromS3()
    {
        $this->isSyncing = true;
        $this->syncMessage = 'Connecting to S3...';
        
        // Dispatch event to show popup immediately
        $this->dispatch('sync-started');

        try {
            $client = Storage::disk('s3')->getClient();
            $bucket = config('filesystems.disks.s3.bucket');
            
            $results = $client->getPaginator('ListObjectsV2', [
                'Bucket' => $bucket,
            ]);

            $count = 0;
            $newFiles = 0;
            $this->syncMessage = 'Scanning files...';

            foreach ($results as $result) {
                if (empty($result['Contents'])) continue;
                
                foreach ($result['Contents'] as $object) {
                    $key = $object['Key'];
                    if (substr($key, -1) === '/') continue; // Skip folders

                    // Check if exists (INCLUDING TRASHED)
                    // If it's in trash, we skip it (user deleted it intentionally)
                    if (File::withTrashed()->where('path', $key)->exists()) {
                        continue;
                    }

                    $this->syncIndividualFile($object);
                    $newFiles++;
                    $count++;
                }
            }

            $this->loadItems();
            $this->syncMessage = "Done! Found $newFiles new files.";
            $this->dispatch('toast', ['message' => "Sync complete. Added $newFiles new files.", 'type' => 'success']);

        } catch (\Exception $e) {
            \Log::error('Sync error: '.$e->getMessage());
            $this->syncMessage = 'Error: ' . $e->getMessage();
        }

        $this->isSyncing = false;
    }

    private function syncIndividualFile($object)
    {
        $filePath = $object['Key'];
        $size = $object['Size'] ?? 0;
        $name = basename($filePath);
        
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };

        // Determine parent folder structure
        $directory = dirname($filePath);
        $folderId = null;

        if ($directory && $directory !== '.') {
            $folderId = $this->getOrCreateDirectory($directory); 
        }

        $publicUrl = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $filePath;

        File::create([
            'folder_id' => $folderId,
            'name' => $name,
            'path' => $filePath,
            'disk' => 's3',
            'mime_type' => $mimeType,
            'size' => $size,
            'metadata' => ['public_url' => $publicUrl],
            'user_id' => auth()->id(),
        ]);
    }

    private function getOrCreateDirectory($path)
    {
        $parts = explode('/', $path);
        $parentId = null;
        $currentPath = '';

        foreach ($parts as $part) {
            if (!$part) continue;
            $currentPath = $currentPath ? "$currentPath/$part" : $part;
            
            // Check based on Path to ensure uniqueness
            $folder = Folder::withTrashed()->where('path', $currentPath)->first();
            
            if (!$folder) {
                // Create if not exists (and not deleted)
                 $folder = Folder::create([
                    'name' => $part,
                    'parent_id' => $parentId,
                    'path' => $currentPath
                ]);
            } elseif ($folder->trashed()) {
                 // If folder is trashed, we might want to restore it or reuse it?
                 // For now, let's just reuse ID but keep it trashed? 
                 // Actually, if we are syncing files INTO it, we probably should Restore it?
                 // User said "deleted file... shouldn't show". Maybe folder too?
                 // Let's assume if folder is deleted, we shouldn't add files to it visible?
                 // BUT, if we add a NEW file from S3 that happens to be in a deleted folder...
                 // Complicated. Let's simple check: if folder exists (trash or not), use its ID.
                 // If it's trashed, the file will be created pointing to a trashed folder (so it might be hidden effectively).
            }

            $parentId = $folder->id;
        }
        
        return $parentId;
    }

    public function logout()
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.file-manager');
    }
}
