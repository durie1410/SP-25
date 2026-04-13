@echo off
echo ==========================================
echo    CAP NHAT VNPAY CONFIG
echo ==========================================
echo.

cd /d D:\laragon\www\quanlythuviennn

echo [1/3] Cap nhat file .env...
php update_env.php
echo.

echo [2/3] Clear cache Laravel...
php artisan config:clear
php artisan cache:clear
echo.

echo [3/3] Kiem tra config...
php artisan tinker --execute="echo 'TMN_CODE: ' . config('services.vnpay.tmn_code') . PHP_EOL; echo 'HASH_SECRET: ' . (config('services.vnpay.hash_secret') ? 'Da cau hinh' : 'Chua cau hinh') . PHP_EOL;"
echo.

echo ==========================================
echo    HOAN THANH!
echo ==========================================
echo.
echo Tiep theo:
echo 1. Mo trinh duyet va truy cap: http://quanlythuviennn.test/test-vnpay-config
echo 2. Thu thanh toan lai
echo.
pause


