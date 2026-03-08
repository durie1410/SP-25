<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendBorrowReminders extends Command
{
    protected $signature = 'borrow:send-reminders {--type=all : Type of reminder (all, due-soon, overdue)}';
    protected $description = 'Send automatic reminders for book borrowings';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $type = $this->option('type');
        
        $this->info("Sending {$type} reminders...");

        switch ($type) {
            case 'due-soon':
                $this->sendDueSoonReminders();
                break;
            case 'overdue':
                $this->sendOverdueReminders();
                break;
            case 'all':
            default:
                $this->sendDueSoonReminders();
                $this->sendOverdueReminders();
                break;
        }

        $this->info('Reminders sent successfully!');
    }

    private function sendDueSoonReminders()
    {
        $this->info('Sending due soon reminders...');

        $count = $this->notificationService->sendUpcomingDueNotifications();

        $this->info("Sent {$count} due soon reminders");
    }

    private function sendOverdueReminders()
    {
        $this->info('Sending overdue reminders...');

        $count = $this->notificationService->sendOverdueNotifications();

        $this->info("Sent {$count} overdue reminders");
    }
}