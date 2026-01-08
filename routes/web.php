<?php


use App\Livewire\FileManager;
use Illuminate\Support\Facades\Route;

use App\Livewire\Auth\Login;

Route::get('/login', Login::class)->name('login');

Route::get('/', FileManager::class)->middleware('auth');

Route::get('/test-s3', function () {
    try {
        $config = config('filesystems.disks.s3');
        
        echo "<h1>S3 Connection Test - Longvan</h1>";
        echo "<h2>Configuration:</h2><pre>";
        echo "Endpoint: " . $config['endpoint'] . "\n";
        echo "Bucket: " . $config['bucket'] . "\n";
        echo "Region: " . $config['region'] . "\n";
        echo "Access Key: " . substr($config['key'], 0, 10) . "...\n";
        echo "</pre>";
        
        echo "<h2>Testing Upload:</h2>";
        $testContent = "Test at " . now();
        $testPath = 'test-' . time() . '.txt';
        
        \Storage::disk('s3')->put($testPath, $testContent, 'public');
        echo "<p style='color: green;'>✅ Upload successful!</p>";
        
        $url = $config['endpoint'] . '/' . $config['bucket'] . '/' . $testPath;
        echo "<p>URL: <a href='" . $url . "' target='_blank'>" . $url . "</a></p>";
        
        echo "<h2>Files in uploads/:</h2><ul>";
        $files = \Storage::disk('s3')->files('uploads');
        foreach (array_slice($files, 0, 10) as $file) {
            $fileUrl = $config['endpoint'] . '/' . $config['bucket'] . '/' . $file;
            echo "<li><a href='" . $fileUrl . "' target='_blank'>" . basename($file) . "</a></li>";
        }
        echo "</ul><p>Total: " . count($files) . " files</p>";
        
        // \Storage::disk('s3')->delete($testPath);
        // echo "<p>🗑️ Test file deleted (Commented out for verification)</p>";
        echo "<p>ℹ️ File kept for verification. Please check S3 console.</p>";
        
    } catch (\Exception $e) {
        echo "<h2 style='color: red;'>❌ Error:</h2><pre>" . $e->getMessage() . "</pre>";
    }
});

