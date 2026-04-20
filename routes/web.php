<?php


use App\Livewire\FileManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Livewire\Auth\Login;

use App\Livewire\RealEstateListing;

Route::get('/login', Login::class)->name('login');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('listings');
    }
    return redirect()->route('landing.ctv');
});

Route::get('/media', FileManager::class)->middleware(['auth', 'admin'])->name('media');
Route::get('/listings', RealEstateListing::class)->middleware('auth')->name('listings');
Route::get('/accounts', \App\Livewire\AccountManagement::class)->middleware(['auth', 'admin'])->name('accounts');
Route::get('/accounts/detail/{id}', \App\Livewire\AccountDetail::class)->middleware(['auth', 'admin'])->name('account.detail');
Route::get('/ctv-ranks', \App\Livewire\CtvRankManagement::class)->middleware(['auth', 'admin'])->name('ctv.ranks');
Route::get('/business', \App\Livewire\BusinessManagement::class)->middleware(['auth', 'admin'])->name('business');
Route::get('/customers', \App\Livewire\CustomerManagement::class)->middleware('auth')->name('customers');
Route::get('/landing/ctv', \App\Livewire\CtvLanding::class)->name('landing.ctv');
Route::get('/my-profile', \App\Livewire\UserProfile::class)->middleware('auth')->name('user.profile');
Route::get('/business/detail/{id}', \App\Livewire\BusinessDetail::class)->middleware(['auth', 'admin'])->name('business.detail');
Route::get('/business/statistics', \App\Livewire\CtvStatistics::class)->middleware(['auth', 'admin'])->name('business.statistics');



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

