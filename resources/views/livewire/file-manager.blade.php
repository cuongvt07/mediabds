<div>
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">File Manager</h1>
            <div class="flex gap-2">
                <button wire:click="$set('viewMode', 'grid')" 
                    class="px-3 py-2 rounded {{ $viewMode === 'grid' ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    Grid
                </button>
                <button wire:click="$set('viewMode', 'list')" 
                    class="px-3 py-2 rounded {{ $viewMode === 'list' ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    List
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 p-6">
        <!-- Breadcrumb Navigation -->
        <div class="mb-4 flex items-center gap-2 text-sm">
            @if($currentFolderId)
                <button wire:click="goBack" 
                    class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center gap-1">
                    <span>←</span> Back
                </button>
            @endif
            
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                <button wire:click="openFolder(null)" class="hover:text-blue-600 dark:hover:text-blue-400 font-medium">
                    🏠 Home
                </button>
                
                @foreach($this->getBreadcrumbs() as $folder)
                    <span>/</span>
                    <button wire:click="openFolder('{{ $folder->id }}')" 
                        class="hover:text-blue-600 dark:hover:text-blue-400 {{ $loop->last ? 'font-semibold text-gray-900 dark:text-white' : '' }}">
                        {{ $folder->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4 flex gap-2">
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                📤 Upload Files
            </button>
            <button onclick="createNewFolder()" 
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                📁 New Folder
            </button>
        </div>

        <!-- File Grid/List -->
        @if($viewMode === 'grid')
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($folders as $folder)
                    <div wire:click="openFolder('{{ $folder->id }}')" 
                        class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <div class="text-4xl mb-2">📁</div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $folder->name }}</div>
                    </div>
                @endforeach

                @foreach($files as $file)
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg cursor-pointer border border-gray-200 dark:border-gray-700 group relative">
                        <div class="h-32 w-full mb-2 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded overflow-hidden">
                            @if(str_starts_with($file->mime_type, 'image/') && !empty($file->metadata['public_url']))
                                <img src="{{ $file->metadata['public_url'] }}" alt="{{ $file->name }}" class="object-cover w-full h-full hover:scale-105 transition-transform duration-300">
                            @elseif(str_starts_with($file->mime_type, 'image/'))
                                <span class="text-4xl">🖼️</span>
                            @elseif(str_starts_with($file->mime_type, 'video/'))
                                <span class="text-4xl">🎬</span>
                            @else
                                <span class="text-4xl">📄</span>
                            @endif
                        </div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $file->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($file->size / 1024 / 1024, 2) }} MB</div>
                        <button wire:click.stop="deleteItem('file', '{{ $file->id }}')" 
                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 bg-red-500 text-white px-2 py-1 rounded text-xs">
                            Delete
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Size</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($folders as $folder)
                            <tr wire:click="openFolder('{{ $folder->id }}')" class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">📁 {{ $folder->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Folder</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">-</td>
                                <td class="px-4 py-3 text-sm">
                                    <button wire:click.stop="deleteItem('folder', '{{ $folder->id }}')" class="text-red-600 hover:text-red-800">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                        @foreach($files as $file)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white flex items-center gap-3">
                                    @if(str_starts_with($file->mime_type, 'image/') && !empty($file->metadata['public_url']))
                                        <img src="{{ $file->metadata['public_url'] }}" class="w-8 h-8 rounded object-cover border border-gray-200 dark:border-gray-600">
                                    @endif
                                    {{ $file->name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $file->mime_type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ number_format($file->size / 1024 / 1024, 2) }} MB</td>
                                <td class="px-4 py-3 text-sm">
                                    <button wire:click="deleteItem('file', '{{ $file->id }}')" class="text-red-600 hover:text-red-800">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Files</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="uploadForm">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Files
                </label>
                <input type="file" id="fileInput" multiple 
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 focus:outline-none mb-4">
                
                <button onclick="startUpload()" 
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Start Upload
                </button>
            </div>

            <!-- Upload Progress -->
            <div id="uploadProgress" class="hidden">
                <div id="filesList" class="space-y-3 mb-4"></div>
                
                <!-- Overall Progress -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                        <span>Overall Progress</span>
                        <span id="overallPercent">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div id="overallProgressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Success Message -->
                <div id="successMessage" class="hidden text-center py-4">
                    <div class="text-green-500 text-6xl mb-2">✓</div>
                    <p class="text-gray-900 dark:text-white font-semibold">Upload Complete!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function createNewFolder() {
            const name = prompt('Enter folder name:');
            if (name) {
                @this.call('createFolder', name);
            }
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
            document.getElementById('uploadForm').classList.remove('hidden');
            document.getElementById('uploadProgress').classList.add('hidden');
            document.getElementById('fileInput').value = '';
        }

        async function startUpload() {
            const fileInput = document.getElementById('fileInput');
            const files = fileInput.files;
            
            if (files.length === 0) {
                alert('Please select files to upload');
                return;
            }

            // Show progress section
            document.getElementById('uploadForm').classList.add('hidden');
            document.getElementById('uploadProgress').classList.remove('hidden');

            const filesList = document.getElementById('filesList');
            filesList.innerHTML = '';

            let totalSize = 0;
            let uploadedSize = 0;

            // Calculate total size
            for (let file of files) {
                totalSize += file.size;
            }

            // Upload each file
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Create file progress item
                const fileItem = document.createElement('div');
                fileItem.className = 'border border-gray-200 dark:border-gray-700 rounded p-3';
                fileItem.innerHTML = `
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-900 dark:text-white truncate">${file.name}</span>
                        <span class="text-gray-500 dark:text-gray-400">${formatFileSize(file.size)}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                        <div class="file-progress bg-green-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                `;
                filesList.appendChild(fileItem);

                // Upload file via Livewire
                await uploadFileToLivewire(file, fileItem.querySelector('.file-progress'), (loaded) => {
                    uploadedSize += loaded;
                    updateOverallProgress((uploadedSize / totalSize) * 100);
                });
            }

            // Show success
            document.getElementById('successMessage').classList.remove('hidden');
            
            // Reload files after 2 seconds
            setTimeout(() => {
                @this.call('loadItems');
                closeUploadModal();
            }, 2000);
        }

        function uploadFileToLivewire(file, progressBar, onProgress) {
            return new Promise((resolve, reject) => {
                @this.upload('files', file, (uploadedFilename) => {
                    // Success - file uploaded to temp storage
                    progressBar.style.width = '100%';
                    onProgress(file.size);
                    
                    // Save file metadata to database
                    @this.call('saveUploadedFile', uploadedFilename, file.name, file.type, file.size)
                        .then(() => {
                            resolve();
                        })
                        .catch(() => {
                            reject();
                        });
                }, () => {
                    // Error
                    reject();
                }, (event) => {
                    // Progress
                    const percent = (event.detail.progress || 0);
                    progressBar.style.width = percent + '%';
                });
            });
        }

        function updateOverallProgress(percent) {
            document.getElementById('overallPercent').textContent = Math.round(percent) + '%';
            document.getElementById('overallProgressBar').style.width = percent + '%';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>
</div>

</div>
