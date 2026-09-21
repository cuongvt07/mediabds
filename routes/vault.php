<?php

use App\Http\Controllers\Vault\VaultAuthController;
use App\Http\Controllers\Vault\VaultBankAccountController;
use App\Http\Controllers\Vault\VaultChangePhoneController;
use App\Http\Controllers\Vault\VaultCronController;
use App\Http\Controllers\Vault\VaultDashboardController;
use App\Http\Controllers\Vault\VaultDepositController;
use App\Http\Controllers\Vault\VaultEkycController;
use App\Http\Controllers\Vault\VaultOtpController;
use App\Http\Controllers\Vault\VaultWithdrawalController;
use App\Http\Controllers\Vault\SePayWebhookController;
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
        Route::post('/auth/verify-phone', [VaultAuthController::class, 'verifyPhone']);
        Route::post('/auth/pin', [VaultAuthController::class, 'setPin']);
        Route::post('/auth/pin/verify', [VaultAuthController::class, 'verifyPin']);

        // OTP dùng chung: verify_phone (gửi lại), set_pin, withdrawal (gửi lại).
        Route::post('/otp/request', [VaultOtpController::class, 'request'])->middleware('throttle:10,1');
        Route::post('/otp/verify', [VaultOtpController::class, 'verify'])->middleware('throttle:10,1');

        // Đổi số điện thoại — 3 bước tuần tự, OTP cả số cũ lẫn số mới.
        Route::post('/change-phone/start', [VaultChangePhoneController::class, 'start'])->middleware('throttle:5,1');
        Route::post('/change-phone/verify-old', [VaultChangePhoneController::class, 'verifyOld'])->middleware('throttle:10,1');
        Route::post('/change-phone/verify-new', [VaultChangePhoneController::class, 'verifyNew'])->middleware('throttle:10,1');

        // "Cron giả lập qua FE" — gọi khi mở Dashboard (xem VaultCronController).
        Route::post('/cron/accrue-check', [VaultCronController::class, 'accrueCheck']);

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
        Route::post('/withdrawals/{withdrawalRequest}/confirm', [VaultWithdrawalController::class, 'confirm'])->middleware('throttle:10,1');

        Route::post('/deposits', [VaultDepositController::class, 'store'])->middleware('throttle:20,1');
        Route::get('/deposits/{depositRequest}', [VaultDepositController::class, 'show']);

        Route::get('/ekyc', [VaultEkycController::class, 'show']);
        // throttle chặt — nộp hồ sơ kèm 2 ảnh, tránh spam ổ đĩa S3.
        Route::post('/ekyc', [VaultEkycController::class, 'store'])->middleware('throttle:3,60');
    });
});

// Webhook SePay — PUBLIC (không qua guard 'vault', SePay không có Bearer
// token user), tự xác thực bằng chữ ký HMAC-SHA256 riêng (xem
// SePayWebhookController). Path khớp đúng URL đã khai báo trên SePay Console:
// https://vm24h.vn/vault/hooks/sepay-payment — KHÔNG có prefix /api/vault/v1.
// throttle rộng (không giới hạn theo user vì không có auth) nhưng vẫn chặn
// flood cơ bản.
Route::post('/vault/hooks/sepay-payment', [SePayWebhookController::class, 'handle'])
    ->middleware('throttle:60,1');
