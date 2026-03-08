<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class DebugMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:debug {--email= : Email nhận test} {--user-id= : User ID để lấy email test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra cấu hình SMTP và thử gửi 1 email test để tìm nguyên nhân lỗi';

    public function handle(NotificationService $notificationService)
    {
        $recipient = $this->option('email');
        $userId = $this->option('user-id');

        if (!$recipient && $userId) {
            $recipient = optional(User::find($userId))->email;
        }

        if (!$recipient) {
            $recipient = optional(User::whereNotNull('email')->where('email', '!=', '')->first())->email;
        }

        $this->line('=== MAIL DEBUG ===');
        $this->line('Mailer: ' . config('mail.default'));
        $this->line('Host: ' . config('mail.mailers.smtp.host'));
        $this->line('Port: ' . config('mail.mailers.smtp.port'));
        $this->line('Encryption: ' . var_export(config('mail.mailers.smtp.encryption'), true));
        $this->line('Username: ' . var_export(config('mail.mailers.smtp.username'), true));
        $this->line('From address: ' . var_export(config('mail.from.address'), true));
        $this->line('From name: ' . var_export(config('mail.from.name'), true));
        $this->line('Recipient test: ' . var_export($recipient, true));
        $this->newLine();

        if (config('mail.default') === 'smtp') {
            $host = config('mail.mailers.smtp.host');
            $port = (int) config('mail.mailers.smtp.port');
            $errno = 0;
            $errstr = '';

            $this->line('Testing SMTP connection...');
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);
            if ($socket) {
                $this->info("✅ Kết nối SMTP thành công tới {$host}:{$port}");
                fclose($socket);
            } else {
                $this->error("❌ Không kết nối được SMTP tới {$host}:{$port}");
                $this->error("Lý do: [{$errno}] {$errstr}");
            }
            $this->newLine();
        }

        if (!$recipient) {
            $this->error('Không tìm thấy email nhận test. Dùng --email=example@gmail.com hoặc --user-id=1');
            return 1;
        }

        $subject = 'Test SMTP - ' . now()->format('d/m/Y H:i:s');
        $content = "Đây là email test để kiểm tra SMTP.\nThời gian: {{time}}\nMailer: {{mailer}}\nHost: {{host}}";

        $this->line('Sending test email...');
        $success = $notificationService->sendSimpleEmail($recipient, $subject, $content, [
            'time' => now()->toDateTimeString(),
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
        ]);

        $latestEmailLog = NotificationLog::where('channel', 'email')
            ->where('type', 'simple_email_test')
            ->latest()
            ->first();

        if ($success) {
            $this->info('✅ Hàm gửi mail trả về thành công.');
        } else {
            $this->error('❌ Hàm gửi mail trả về thất bại. Xem storage/logs/laravel.log');
        }

        if ($latestEmailLog) {
            $this->newLine();
            $this->line('Latest email log status: ' . $latestEmailLog->status);
            $this->line('Latest email log recipient: ' . $latestEmailLog->recipient);
            $this->line('Latest email log subject: ' . ($latestEmailLog->subject ?? ''));
            $this->line('Latest email log error: ' . ($latestEmailLog->error_message ?? ''));
        } else {
            $this->line('Chưa có bản ghi email trong notification_logs.');
        }

        $this->newLine();
        $this->line('Kiểm tra log ứng dụng tại: storage/logs/laravel.log');

        return $success ? 0 : 1;
    }
}
