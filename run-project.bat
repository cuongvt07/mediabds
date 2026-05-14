@echo off
title Project Starter - MediaBDS
:: Tu dong set PHP 8.1 tu Laragon vao PATH cho phien lam viec nay
set "PHP_DIR=d:\laragon\bin\php\php-8.1.10"
set "PATH=%PHP_DIR%;%PATH%"

echo ==========================================
echo    DANG KHOI DONG MOI TRUONG PHAT TRIEN (PHP 8.1)
echo ==========================================
echo Dang dung PHP tai:
php -v | findstr "PHP"

echo.
echo [1/4] Dang chay Composer Install...
call composer install

echo.
echo [2/4] Dang chay NPM Build...
call npm run build

echo.
echo [3/4] Dang khoi dong PHP Artisan Server...
start "Laravel Server" cmd /k "set PATH=%PHP_DIR%;%%PATH%% && php artisan serve"

echo.
echo [4/4] Dang khoi dong NPM Dev (Vite)...
start "Vite Dev" cmd /k "npm run dev"

echo.
echo ==========================================
echo TAT CA DA SAN SANG!
echo Laravel: http://127.0.0.1:8000 (PHP 8.1)
echo ==========================================
pause
