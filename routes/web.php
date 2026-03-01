<?php


use App\Livewire\FileManager;
use Illuminate\Support\Facades\Route;

use App\Livewire\Auth\Login;

use App\Livewire\RealEstateListing;

Route::get('/login', Login::class)->name('login');

Route::get('/', function () {
    return redirect()->route('listings');
});

Route::get('/media', FileManager::class)->middleware(['auth', 'admin'])->name('media');
Route::get('/listings', RealEstateListing::class)->middleware('auth')->name('listings');
Route::get('/accounts', \App\Livewire\AccountManagement::class)->middleware(['auth', 'admin'])->name('accounts');
Route::get('/customers', \App\Livewire\CustomerManagement::class)->middleware('auth')->name('customers');

Route::get('/proxy-image-download', function (\Illuminate\Http\Request $request) {
    $url = $request->query('url');
    if (!$url)
        return abort(400);

    try {
        $response = \Illuminate\Support\Facades\Http::get($url);
        if ($response->successful()) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            if (!$filename)
                $filename = 'image.jpg';

            return response($response->body())
                ->header('Content-Type', $response->header('Content-Type') ?? 'image/jpeg')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
    } catch (\Exception $e) {
    }

    return redirect()->away($url);
})->middleware('auth')->name('proxy-image-download');
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

