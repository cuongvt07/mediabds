<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Module Vault: cộng lãi hằng ngày lúc 00:00 (khớp UI "Tự động cộng dồn lãi
// kép... 00:00 mỗi ngày"). LƯU Ý: cần cron server gọi `php artisan schedule:run`
// mỗi phút thì lịch này mới thực sự chạy — kiểm tra crontab trên server.
Schedule::command('vault:accrue-interest')->dailyAt('00:00')->onOneServer();
