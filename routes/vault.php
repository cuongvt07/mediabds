<?php

use App\Http\Controllers\Vault\VaultAuthController;
use App\Http\Controllers\Vault\VaultBankAccountController;
use App\Http\Controllers\Vault\VaultDashboardController;
use App\Http\Controllers\Vault\VaultDepositController;
use App\Http\Controllers\Vault\VaultWithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vault module routes
|--------------------------------------------------------------------------
|
| Module HOÀN TOÀN ĐỘC LẬP với routes/api.php: guard riêng ('vault', xem
| config/auth.php), model riêng (App\Models\Vault\*), bảng riêng (vault_*).
| Đăng ký qua bootstrap/app.php (withRouting -> then), prefix cuối cùng là
| /api/vault/v1/* (nhóm middleware 'api' đã bọc sẵn bên ngoài + apiPrefix
| 'api' KHÔNG áp dụng tự động cho file này nên khai báo tường minh ở đây).
|
*/

Route::prefix('api/vault/v1')->group(function () {
    // Public
    Route::post('/auth/register', [VaultAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/login', [VaultAuthController::class, 'login'])->middleware('throttle:10,1');

    // Authenticated (guard riêng 'vault', Bearer token thuần qua Sanctum)
    Route::middleware('auth:vault')->group(function () {
        Route::post('/auth/logout', [VaultAuthController::class, 'logout']);
        Route::get('/auth/me', [VaultAuthController::class, 'me']);
        Route::post('/auth/pin', [VaultAuthController::class, 'setPin']);
        Route::post('/auth/pin/verify', [VaultAuthController::class, 'verifyPin']);

        Route::get('/dashboard/summary', [VaultDashboardController::class, 'summary']);
        Route::get('/dashboard/vaults', [VaultDashboardController::class, 'vaults']);
        Route::get('/dashboard/interest-chart', [VaultDashboardController::class, 'interestChart']);
        Route::get('/dashboard/activity', [VaultDashboardController::class, 'recentActivity']);

        Route::get('/bank-accounts', [VaultBankAccountController::class, 'index']);
        Route::post('/bank-accounts', [VaultBankAccountController::class, 'store']);
        Route::post('/bank-accounts/{bankAccount}/default', [VaultBankAccountController::class, 'setDefault']);
        Route::delete('/bank-accounts/{bankAccount}', [VaultBankAccountController::class, 'destroy']);

        Route::get('/withdrawals', [VaultWithdrawalController::class, 'index']);
        Route::post('/withdrawals', [VaultWithdrawalController::class, 'store'])->middleware('throttle:20,1');
        Route::get('/withdrawals/{withdrawalRequest}', [VaultWithdrawalController::class, 'show']);

        Route::post('/deposits', [VaultDepositController::class, 'store'])->middleware('throttle:20,1');
        Route::get('/deposits/{depositRequest}', [VaultDepositController::class, 'show']);
    });
});
