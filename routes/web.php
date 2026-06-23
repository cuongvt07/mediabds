<?php

use App\Http\Controllers\Site\RoomSiteController;
use App\Http\Controllers\Site\SiteAuthController;
use App\Http\Controllers\WatermarkController;
use App\Livewire\Auth\Login;
use App\Livewire\SiteAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ảnh tin đăng có watermark chèn on-the-fly (cache theo version cấu hình watermark).
Route::get('/wm/{version}/{path}', [WatermarkController::class, 'show'])
    ->where('path', '.*')
    ->name('wm');

Route::get('/', [RoomSiteController::class, 'index'])->name('site.home');
Route::get('/tin-dang/{listing}', [RoomSiteController::class, 'show'])->name('site.listings.show');

Route::get('/login', Login::class)->name('login');
Route::post('/auth/login', [SiteAuthController::class, 'login'])->name('site.auth.login');
Route::post('/auth/register', [SiteAuthController::class, 'register'])->name('site.auth.register');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::get('/admin', SiteAdmin::class)->middleware(['auth', 'admin'])->name('site.admin');

Route::middleware('auth')->group(function () {
    Route::get('/trang-ca-nhan', \App\Livewire\User\Dashboard::class)->name('user.dashboard');
    Route::get('/dang-tin', \App\Livewire\User\PostListing::class)->name('user.listing.create');
    Route::get('/dang-tin/{listing}', \App\Livewire\User\PostListing::class)->name('user.listing.edit');
});
