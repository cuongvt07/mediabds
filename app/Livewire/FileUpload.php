<?php

namespace App\Livewire;


use Livewire\Component;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    public $files = [];
    public $folderId = null;

    protected $listeners = ['openUploadModal' => 'resetForm'];

    public function resetForm()
    {
        $this->files = [];
    }

    public function uploadFiles()
    {
        if (empty($this->files)) {
            return;
        }

        // Handle single file or array
        $filesToUpload = is_array($this->files) ? $this->files : [$this->files];

        foreach ($filesToUpload as $uploadedFile) {
            $path = $uploadedFile->store('uploads', 's3');
            
            \App\Models\File::create([
                'folder_id' => $this->folderId,
                'name' => $uploadedFile->getClientOriginalName(),
                'path' => $path,
                'disk' => 's3',
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
            ]);
        }

        $this->files = [];
        $this->dispatch('filesUploaded');
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
