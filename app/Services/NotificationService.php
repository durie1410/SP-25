<?php

namespace App\Services;

use App\Models\User;
use App\Models\Reader;
use App\Models\Borrow;
// use App\Models\Reservation; // Model đã bị xóa
use App\Models\Fine;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send notification to user
     */
    public function sendNotification($userId, $type, $data, $channels = ['database', 'email'])
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $template = $this->getTemplate($type);
        if (!$template) {
            Log::warning("Notification template not found for type: {$type}");

            // Fallback for reservation_ready / reservation_overdue notification if template is missing
            if ($type === 'reservation_ready') {
                Log::info("Using fallback template for type: {$type}");
                $template = (object) [
                    'subject' => 'Sách bạn đặt trước đã sẵn sàng',
                    'content' => 'Sách "{{book_title}}" đã sẵn sàng. Mời bạn đến quầy để nhận sách trước ngày {{expiry_date}}.',
                    'type' => 'reservation_ready',
                    'channel' => 'database', // Default to database channel
                ];
            } elseif ($type === 'reservation_overdue') {
                Log::info("Using fallback template for type: {$type}");
                $template = (object) [
                    'subject' => 'Yêu cầu đặt trước đã quá hạn',
                    'content' => 'Yêu cầu nhận sách "{{book_title}}" của bạn đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu đặt trước mới nếu vẫn còn nhu cầu.',
                    'type' => 'reservation_overdue',
                    'channel' => 'database',
                ];
            } else {
                return false; // Keep original behavior for other types
            }
        }

        $notificationData = $this->prepareNotificationData($template, $data);
        
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'database':
                    $this->sendDatabaseNotification($user, $notificationData);
                    break;
                case 'email':
                    $this->sendEmailNotification($user, $notificationData);
                    break;
                case 'sms':
                    $this->sendSmsNotification($user, $notificationData);
                    break;
            }
        }

        return true;
    }

    /**
     * Send overdue book notifications
     */
    public function sendOverdueNotifications()
    {
        $overdueBorrows = Borrow::with(['reader.user', 'book'])
            ->where('trang_thai', 'Dang muon')
            ->where('ngay_hen_tra', '<', Carbon::today())
            ->get();

        foreach ($overdueBorrows as $borrow) {
            $daysOverdue = Carbon::parse($borrow->ngay_hen_tra)->diffInDays(Carbon::today());
            
            $data = [
                'reader_name' => $borrow->reader->ho_ten,
                'book_title' => $borrow->book->ten_sach,
                'due_date' => $borrow->ngay_hen_tra,
                'days_overdue' => $daysOverdue,
                'fine_amount' => $this->calculateFine($daysOverdue),
            ];

            $this->sendNotification(
                $borrow->reader->user_id,
                'book_overdue',
                $data,
                ['database', 'email']
            );
        }

        return $overdueBorrows->count();
    }

    /**
     * Send upcoming due date notifications
     */
    public function sendUpcomingDueNotifications()
    {
        $upcomingBorrows = Borrow::with(['reader.user', 'book'])
            ->where('trang_thai', 'Dang muon')
            ->where('ngay_hen_tra', '=', Carbon::tomorrow())
            ->get();

        foreach ($upcomingBorrows as $borrow) {
            $data = [
                'reader_name' => $borrow->reader->ho_ten,
                'book_title' => $borrow->book->ten_sach,
                'due_date' => $borrow->ngay_hen_tra,
            ];

            $this->sendNotification(
                $borrow->reader->user_id,
                'book_due_tomorrow',
                $data,
                ['database', 'email']
            );
        }

        return $upcomingBorrows->count();
    }

    /**
     * Send reservation ready notifications
     */
    public function sendReservationReadyNotifications()
    {
        // Reservation model đã bị xóa
        $readyReservations = collect();

        foreach ($readyReservations as $reservation) {
            $data = [
                'reader_name' => $reservation->reader->ho_ten,
                'book_title' => $reservation->book->ten_sach,
                'expiry_date' => $reservation->expiry_date,
            ];

            $this->sendNotification(
                $reservation->reader->user_id,
                'reservation_ready',
                $data,
                ['database', 'email']
            );

            $reservation->update(['notified_at' => now()]);
        }

        return $readyReservations->count();
    }

    /**
     * Send reservation expiry notifications
     */
    public function sendReservationExpiryNotifications()
    {
        // Reservation model đã bị xóa
        $expiringReservations = collect();

        foreach ($expiringReservations as $reservation) {
            $data = [
                'reader_name' => $reservation->reader->ho_ten,
                'book_title' => $reservation->book->ten_sach,
                'expiry_date' => $reservation->expiry_date,
            ];

            $this->sendNotification(
                $reservation->reader->user_id,
                'reservation_expiring',
                $data,
                ['database', 'email']
            );
        }

        return $expiringReservations->count();
    }

    /**
     * Send fine notifications
     */
    public function sendFineNotifications()
    {
        $pendingFines = Fine::with(['reader.user', 'borrow.book'])
            ->where('status', 'pending')
            ->where('notified_at', null)
            ->get();

        foreach ($pendingFines as $fine) {
            $data = [
                'reader_name' => $fine->reader->ho_ten,
                'book_title' => $fine->borrow ? $fine->borrow->book->ten_sach : 'N/A',
                'fine_amount' => $fine->amount,
                'reason' => $fine->reason,
            ];

            $this->sendNotification(
                $fine->reader->user_id,
                'fine_created',
                $data,
                ['database', 'email']
            );

            $fine->update(['notified_at' => now()]);
        }

        return $pendingFines->count();
    }

    /**
     * Send reader card expiry notifications
     */
    public function sendReaderCardExpiryNotifications()
    {
        $expiringReaders = Reader::with('user')
            ->where('trang_thai', 'Hoat dong')
            ->where('ngay_het_han', '<=', Carbon::now()->addDays(30))
            ->where('ngay_het_han', '>', Carbon::today())
            ->get();

        foreach ($expiringReaders as $reader) {
            $daysToExpiry = Carbon::today()->diffInDays(Carbon::parse($reader->ngay_het_han));
            
            $data = [
                'reader_name' => $reader->ho_ten,
                'card_number' => $reader->so_the_doc_gia,
                'expiry_date' => $reader->ngay_het_han,
                'days_to_expiry' => $daysToExpiry,
            ];

            $this->sendNotification(
                $reader->user_id,
                'reader_card_expiring',
                $data,
                ['database', 'email']
            );
        }

        return $expiringReaders->count();
    }

    /**
     * Send bulk notifications
     */
    public function sendBulkNotification($userIds, $type, $data, $channels = ['database'])
    {
        $successCount = 0;
        
        foreach ($userIds as $userId) {
            if ($this->sendNotification($userId, $type, $data, $channels)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Get notification template
     */
    protected function getTemplate($type)
    {
        return NotificationTemplate::where('type', $type)->first();
    }

    /**
     * Prepare notification data
     */
    protected function prepareNotificationData($template, $data)
    {
        $subject = $this->replacePlaceholders($template->subject, $data);
        $body = $this->replacePlaceholders($template->content, $data);

        return [
            'type' => $template->type,
            'subject' => $subject,
            'body' => $body,
            'priority' => 'normal',
            'channels' => [$template->channel],
        ];
    }

    /**
     * Replace placeholders in template
     */
    protected function replacePlaceholders($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }
        return $template;
    }

    /**
     * Send database notification
     */
    protected function sendDatabaseNotification($user, $notificationData)
    {
        NotificationLog::create([
            'user_id' => $user->id,
            'type' => $notificationData['type'],
            'channel' => 'database',
            'recipient' => (string) ($user->id),
            'subject' => $notificationData['subject'],
            'content' => $notificationData['body'],
            'body' => $notificationData['body'],
            'priority' => $notificationData['priority'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification($user, $notificationData)
    {
        try {
            Mail::send('emails.notification', [
                'user' => $user,
                'subject' => $notificationData['subject'],
                'body' => $notificationData['body'],
                'action_url' => $notificationData['action_url'] ?? null,
                'action_text' => $notificationData['action_text'] ?? null,
                'additional_info' => $notificationData['additional_info'] ?? null,
            ], function ($message) use ($user, $notificationData) {
                $message->to($user->email, $user->name)
                        ->subject($notificationData['subject']);
            });

            NotificationLog::create([
                'user_id' => $user->id,
                'type' => $notificationData['type'],
                'channel' => 'email',
                'recipient' => (string) ($user->email ?? $user->id),
                'subject' => $notificationData['subject'],
                'content' => $notificationData['body'],
                'body' => $notificationData['body'],
                'priority' => $notificationData['priority'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Email notification failed', [
                'user_id' => $user->id,
                'type' => $notificationData['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSmsNotification($user, $notificationData)
    {
        // Implement SMS sending logic here
        // This would typically integrate with SMS providers like Twilio, Nexmo, etc.
        
        NotificationLog::create([
            'user_id' => $user->id,
            'type' => $notificationData['type'],
            'channel' => 'sms',
            'recipient' => (string) ($user->phone ?? $user->id),
            'subject' => $notificationData['subject'],
            'content' => $notificationData['body'],
            'body' => $notificationData['body'],
            'priority' => $notificationData['priority'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Calculate fine amount
     */
    protected function calculateFine($daysOverdue)
    {
        $finePerDay = 5000; // 5,000 VND per day
        return $daysOverdue * $finePerDay;
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 20)
    {
        return NotificationLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'subject' => $notification->subject,
                    'body' => $notification->body,
                    'priority' => $notification->priority,
                    'channel' => $notification->channel,
                    'read_at' => $notification->read_at,
                    'sent_at' => $notification->sent_at,
                    'created_at' => $notification->created_at,
                ];
            });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        return NotificationLog::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead($userId)
    {
        return NotificationLog::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount($userId)
    {
        return NotificationLog::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Clean old notifications
     */
    public function cleanOldNotifications($days = 30)
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        return NotificationLog::where('created_at', '<', $cutoffDate)
            ->whereNotNull('read_at')
            ->delete();
    }

    /**
     * Send simple email notification
     */
    public function sendSimpleEmail($email, $subject, $content, $data = [])
    {
        try {
            // Replace placeholders in content
            $processedContent = $this->replacePlaceholders($content, $data);
            $processedSubject = $this->replacePlaceholders($subject, $data);

            Mail::send('emails.simple', [
                'content' => $processedContent,
                'subject' => $processedSubject,
                'data' => $data,
            ], function ($message) use ($email, $processedSubject) {
                $message->to($email)
                        ->subject($processedSubject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Simple email notification failed', [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}