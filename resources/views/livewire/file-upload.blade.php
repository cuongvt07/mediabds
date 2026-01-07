<div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Select Files (Max 4GB each)
        </label>
        <input type="file" wire:model="files" multiple 
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 focus:outline-none">
        @error('files.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    @if($files)
        <div class="mb-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selected Files:</h3>
            <ul class="space-y-1">
                @foreach($files as $file)
                    <li class="text-sm text-gray-600 dark:text-gray-400">{{ $file->getClientOriginalName() }} ({{ number_format($file->getSize() / 1024 / 1024, 2) }} MB)</li>
                @endforeach
            </ul>
        </div>
    @endif

    <button wire:click="uploadFiles" wire:loading.attr="disabled" 
        class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
        <span wire:loading.remove>Upload to S3</span>
        <span wire:loading>Uploading...</span>
    </button>
</div>
