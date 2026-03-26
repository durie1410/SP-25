<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Tự động tạo phạt cho sách trả muộn hàng ngày lúc 8:00
        $schedule->command('fines:create-late-returns')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Tự động tạo sao lưu hàng ngày lúc 2:00
        $schedule->command('backup:create --type=automatic --description="Daily automatic backup"')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Dọn dẹp sao lưu cũ hàng tuần (giữ lại 30 ngày)
        $schedule->command('backup:cleanup --days=30')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Housekeeping: mark overdue/cancelled reservations every 5 minutes + send notifications immediately
        $schedule->command('library:housekeeping')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Reminders: send due-soon and overdue emails daily at 09:00
        $schedule->command('library:reminders')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Nhắc nhở phiếu mượn sắp đến hạn trả và quá hạn
        $schedule->command('borrow:send-reminders --type=all')
             ->dailyAt('08:30')
             ->withoutOverlapping()
             ->runInBackground();

        // Nhắc nhở đặt trước sắp đến ngày nhận sách
        $schedule->command('notifications:send --type=reservation-expiring')
             ->dailyAt('08:15')
             ->withoutOverlapping()
             ->runInBackground();
        
        // Tự động xác nhận nhận sách sau 3 giờ (chạy mỗi 5 phút)
        $schedule->command('borrow:auto-confirm-delivery')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
