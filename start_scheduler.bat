@echo off
cd /d C:\laragon\www\quanlythuviennn
echo Starting Laravel Schedule Worker...
"C:\laragon\bin\php\php-8.2.20-Win32-vs16-x64\php.exe" artisan schedule:work
