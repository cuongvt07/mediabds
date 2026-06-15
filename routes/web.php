<?php

use App\Http\Controllers\Site\RoomSiteController;
use App\Livewire\Auth\Login;
use App\Livewire\SiteAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [RoomSiteController::class, 'index'])->name('site.home');
Route::get('/tin-dang/{listing}', [RoomSiteController::class, 'show'])->name('site.listings.show');

Route::get('/login', Login::class)->name('login');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::get('/admin', SiteAdmin::class)->middleware(['auth', 'admin'])->name('site.admin');
