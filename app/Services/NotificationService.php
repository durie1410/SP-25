<?php

namespace App\Services;

use App\Models\User;
use App\Models\Reader;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\InventoryReservation;
// use App\Models\Reservation; // Model đã bị xóa
use App\Models\Fine;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;
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
                    'content' => 'Xin chào {{reader_name}}, sách "{{book_title}}" bạn đặt trước đã sẵn sàng. Mời bạn đến nhận trước ngày {{expiry_date}}.',
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
            } elseif ($type === 'reservation_expiring') {
                Log::info("Using fallback template for type: {$type}");
                $template = (object) [
                    'subject' => 'Nhắc nhở nhận sách đặt trước',
                    'content' => 'Xin chào {{reader_name}}, sách "{{book_title}}" bạn đặt trước đang sẵn sàng và cần được nhận trước ngày {{pickup_date}} (còn {{days_remaining}} ngày).',
                    'type' => 'reservation_expiring',
                    'channel' => 'database',
                ];
            } elseif ($type === 'borrow_approved') {
                Log::info("Using fallback template for type: {$type}");
                $template = (object) [
                    'subject' => 'Đơn mượn sách #{{borrow_id}} đã được duyệt',
                    'content' => 'Xin chào {{reader_name}}, đơn mượn sách #{{borrow_id}} của bạn đã được duyệt. Sách: {{book_titles}}. Vui lòng theo dõi đơn mượn để hoàn tất các bước tiếp theo.',
                    'type' => 'borrow_approved',
                    'channel' => 'database',
                ];
            } elseif ($type === 'book_due_soon') {
                Log::info("Using fallback template for type: {$type}");
                $template = (object) [
                    'subject' => 'Nhắc nhở: sắp đến hạn trả sách',
                    'content' => 'Xin chào {{reader_name}}, sách "{{book_title}}" trong phiếu mượn #{{borrow_id}} sẽ đến hạn trả vào ngày {{due_date}} (còn {{days_remaining}} ngày). Vui lòng sắp xếp trả sách đúng hạn.',
                    'type' => 'book_due_soon',
                    'channel' => 'database',
                ];
            } elseif ($type === 'book_overdue') {
                Log::info("Using fallback template for type: {$type}");
                $template = (object) [
                    'subject' => 'Thông báo sách quá hạn trả',
                    'content' => 'Xin chào {{reader_name}}, sách "{{book_title}}" trong phiếu mượn #{{borrow_id}} đã quá hạn trả {{days_overdue}} ngày. Hạn trả là {{due_date}}. Vui lòng trả sách sớm để tránh phát sinh thêm phí phạt.',
                    'type' => 'book_overdue',
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
        $overdueItems = BorrowItem::with(['borrow.reader.user', 'book'])
            ->whereIn('trang_thai', ['Dang muon', 'Qua han'])
            ->whereDate('ngay_hen_tra', '<', Carbon::today()->toDateString())
            ->get();

        $sentCount = 0;

        foreach ($overdueItems as $item) {
            $borrow = $item->borrow;
            $reader = $borrow?->reader;
            $book = $item->book;

            if (!$borrow || !$reader || !$reader->email) {
                continue;
            }

            $daysOverdue = Carbon::parse($item->ngay_hen_tra)->diffInDays(Carbon::today());

            $data = [
                'reader_name' => $reader->ho_ten,
                'book_title' => $book?->ten_sach ?? 'Sách thư viện',
                'due_date' => Carbon::parse($item->ngay_hen_tra)->format('d/m/Y'),
                'days_overdue' => $daysOverdue,
                'fine_amount' => $this->calculateFine($daysOverdue),
                'borrow_id' => $borrow->id,
            ];

            if ($reader->user_id) {
                $this->sendNotification(
                    $reader->user_id,
                    'book_overdue',
                    $data,
                    ['database', 'email']
                );
            } else {
                $this->sendSimpleEmail(
                    $reader->email,
                    'Thông báo sách quá hạn trả',
                    'Xin chào {{reader_name}}, sách "{{book_title}}" trong phiếu mượn #{{borrow_id}} đã quá hạn trả {{days_overdue}} ngày. Hạn trả là {{due_date}}. Vui lòng trả sách sớm để tránh phát sinh thêm phí phạt.',
                    $data
                );
            }

            $sentCount++;
        }

        return $sentCount;
    }

    /**
     * Send upcoming due date notifications
     */
    public function sendUpcomingDueNotifications()
    {
        $upcomingItems = BorrowItem::with(['borrow.reader.user', 'book'])
            ->where('trang_thai', 'Dang muon')
            ->whereDate('ngay_hen_tra', '=', Carbon::tomorrow()->toDateString())
            ->get();

        $sentCount = 0;

        foreach ($upcomingItems as $item) {
            $borrow = $item->borrow;
            $reader = $borrow?->reader;
            $book = $item->book;

            if (!$borrow || !$reader || !$reader->email) {
                continue;
            }

            $data = [
                'reader_name' => $reader->ho_ten,
                'book_title' => $book?->ten_sach ?? 'Sách thư viện',
                'due_date' => Carbon::parse($item->ngay_hen_tra)->format('d/m/Y'),
                'days_remaining' => 1,
                'borrow_id' => $borrow->id,
            ];

            if ($reader->user_id) {
                $this->sendNotification(
                    $reader->user_id,
                    'book_due_soon',
                    $data,
                    ['database', 'email']
                );
            } else {
                $this->sendSimpleEmail(
                    $reader->email,
                    'Nhắc nhở: sắp đến hạn trả sách',
                    'Xin chào {{reader_name}}, sách "{{book_title}}" trong phiếu mượn #{{borrow_id}} sẽ đến hạn trả vào ngày {{due_date}} (còn {{days_remaining}} ngày). Vui lòng sắp xếp trả sách đúng hạn.',
                    $data
                );
            }

            $sentCount++;
        }

        return $sentCount;
    }

    public function sendBorrowApprovedNotification(Borrow $borrow)
    {
        $borrow->loadMissing(['reader.user', 'items.book']);

        $reader = $borrow->reader;
        if (!$reader) {
            Log::warning('Borrow approval email skipped: borrow has no reader', [
                'borrow_id' => $borrow->id,
            ]);

            return false;
        }

        $bookTitles = $borrow->items
            ->map(function ($item) {
                return optional($item->book)->ten_sach;
            })
            ->filter()
            ->implode(', ');

        $data = [
            'reader_name' => $reader->ho_ten,
            'borrow_id' => $borrow->id,
            'book_titles' => $bookTitles ?: 'Sách thư viện',
        ];

        if ($reader->user_id) {
            return $this->sendNotification(
                $reader->user_id,
                'borrow_approved',
                $data,
                ['database', 'email']
            );
        }

        if (!empty($reader->email)) {
            return $this->sendSimpleEmail(
                $reader->email,
                'Đơn mượn sách #{{borrow_id}} đã được duyệt',
                'Xin chào {{reader_name}}, đơn mượn sách #{{borrow_id}} của bạn đã được duyệt. Sách: {{book_titles}}. Vui lòng theo dõi đơn mượn để hoàn tất các bước tiếp theo.',
                $data
            );
        }

        Log::warning('Borrow approval email skipped: reader has no email', [
            'borrow_id' => $borrow->id,
            'reader_id' => $reader->id,
        ]);

        return false;
    }

    /**
     * Send reservation ready notifications
     */
    public function sendReservationReadyNotifications()
    {
        $readyReservations = InventoryReservation::with(['book', 'reader.user', 'user'])
            ->where('status', 'ready')
            ->whereDate('ready_at', Carbon::today()->toDateString())
            ->get();

        foreach ($readyReservations as $reservation) {
            $this->sendReservationReadyNotification($reservation);
        }

        return $readyReservations->count();
    }

    /**
     * Send reservation expiry notifications
     */
    public function sendReservationExpiryNotifications()
    {
        $expiringReservations = InventoryReservation::with(['book', 'reader.user', 'user'])
            ->where('status', 'ready')
            ->whereDate('pickup_date', Carbon::tomorrow()->toDateString())
            ->get();

        $sentCount = 0;

        foreach ($expiringReservations as $reservation) {
            $data = [
                'reader_name' => $reservation->reader?->ho_ten ?? $reservation->user?->name ?? 'Bạn',
                'book_title' => $reservation->book?->ten_sach ?? 'Sách',
                'pickup_date' => optional($reservation->pickup_date)->format('d/m/Y') ?? now()->addDay()->format('d/m/Y'),
                'days_remaining' => 1,
            ];

            $userId = $reservation->reader?->user_id ?? $reservation->user_id;

            if ($userId) {
                $this->sendNotification(
                    $userId,
                    'reservation_expiring',
                    $data,
                    ['database', 'email']
                );
            } else {
                $recipientEmail = $reservation->reader?->email ?? $reservation->user?->email;
                if ($recipientEmail) {
                    $this->sendSimpleEmail(
                        $recipientEmail,
                        'Nhắc nhở nhận sách đặt trước',
                        'Xin chào {{reader_name}}, sách "{{book_title}}" bạn đặt trước đang sẵn sàng và cần được nhận trước ngày {{pickup_date}} (còn {{days_remaining}} ngày).',
                        $data
                    );
                }
            }

            $sentCount++;
        }

        return $sentCount;
    }

    public function sendReservationReadyNotification(InventoryReservation $reservation)
    {
        $reservation->loadMissing(['book', 'reader.user', 'user']);

        $userId = $reservation->reader?->user_id ?? $reservation->user_id;
        $recipientEmail = $reservation->reader?->email ?? $reservation->user?->email;

        $data = [
            'reader_name' => $reservation->reader?->ho_ten ?? $reservation->user?->name ?? 'Bạn',
            'book_title' => $reservation->book?->ten_sach ?? 'Sách',
            'ready_date' => optional($reservation->ready_at)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'expiry_date' => optional($reservation->pickup_date)->format('d/m/Y') ?? now()->addDays(3)->format('d/m/Y'),
        ];

        if ($userId) {
            return $this->sendNotification(
                $userId,
                'reservation_ready',
                $data,
                ['database', 'email']
            );
        }

        if ($recipientEmail) {
            return $this->sendSimpleEmail(
                $recipientEmail,
                'Sách bạn đặt trước đã sẵn sàng',
                'Xin chào {{reader_name}}, sách "{{book_title}}" bạn đặt trước đã sẵn sàng. Mời bạn đến nhận trước ngày {{expiry_date}}.',
                $data
            );
        }

        Log::warning('Reservation ready email skipped: no recipient', [
            'reservation_id' => $reservation->id,
        ]);

        return false;
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
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
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
            if (empty($user->email)) {
                Log::warning('Email notification skipped: user has no email', [
                    'user_id' => $user->id,
                    'type' => $notificationData['type'],
                ]);

                NotificationLog::create([
                    'user_id' => $user->id,
                    'type' => $notificationData['type'],
                    'channel' => 'email',
                    'recipient' => (string) $user->id,
                    'subject' => $notificationData['subject'],
                    'content' => $notificationData['body'],
                    'body' => $notificationData['body'],
                    'priority' => $notificationData['priority'],
                    'status' => 'failed',
                    'error_message' => 'User does not have an email address.',
                    'metadata' => json_encode($this->getMailDebugContext($user->email, $notificationData['subject'])),
                ]);

                return false;
            }

            $mailDebugContext = $this->getMailDebugContext($user->email, $notificationData['subject']);

            Log::info('Starting email notification send', array_merge($mailDebugContext, [
                'user_id' => $user->id,
                'type' => $notificationData['type'],
            ]));

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

            $failures = method_exists(Mail::getFacadeRoot(), 'failures')
                ? Mail::failures()
                : [];

            if (!empty($failures)) {
                Log::warning('Email notification reported transport failures', array_merge($mailDebugContext, [
                    'user_id' => $user->id,
                    'type' => $notificationData['type'],
                    'failures' => $failures,
                ]));

                NotificationLog::create([
                    'user_id' => $user->id,
                    'type' => $notificationData['type'],
                    'channel' => 'email',
                    'recipient' => (string) ($user->email ?? $user->id),
                    'subject' => $notificationData['subject'],
                    'content' => $notificationData['body'],
                    'body' => $notificationData['body'],
                    'priority' => $notificationData['priority'],
                    'status' => 'failed',
                    'error_message' => 'Mail transport returned failures: ' . implode(', ', $failures),
                    'metadata' => json_encode(array_merge($mailDebugContext, ['failures' => $failures])),
                ]);

                return false;
            }

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
                'metadata' => json_encode($mailDebugContext),
                'sent_at' => now(),
            ]);

            Log::info('Email notification sent successfully', array_merge($mailDebugContext, [
                'user_id' => $user->id,
                'type' => $notificationData['type'],
            ]));

            return true;
        } catch (Throwable $e) {
            Log::error('Email notification failed', [
                'user_id' => $user->id,
                'type' => $notificationData['type'],
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'mail_config' => $this->getMailDebugContext($user->email ?? null, $notificationData['subject']),
            ]);

            NotificationLog::create([
                'user_id' => $user->id,
                'type' => $notificationData['type'],
                'channel' => 'email',
                'recipient' => (string) ($user->email ?? $user->id),
                'subject' => $notificationData['subject'],
                'content' => $notificationData['body'],
                'body' => $notificationData['body'],
                'priority' => $notificationData['priority'],
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => json_encode($this->getMailDebugContext($user->email ?? null, $notificationData['subject'])),
            ]);

            return false;
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
            $mailDebugContext = $this->getMailDebugContext($email, $processedSubject);

            Log::info('Starting simple email send', $mailDebugContext);

            Mail::send('emails.simple', [
                'content' => $processedContent,
                'subject' => $processedSubject,
                'data' => $data,
            ], function ($message) use ($email, $processedSubject) {
                $message->to($email)
                        ->subject($processedSubject);
            });

            $failures = method_exists(Mail::getFacadeRoot(), 'failures')
                ? Mail::failures()
                : [];

            if (!empty($failures)) {
                Log::warning('Simple email reported transport failures', array_merge($mailDebugContext, [
                    'failures' => $failures,
                ]));

                NotificationLog::create([
                    'user_id' => null,
                    'type' => 'simple_email_test',
                    'channel' => 'email',
                    'recipient' => (string) $email,
                    'subject' => $processedSubject,
                    'content' => $processedContent,
                    'body' => $processedContent,
                    'priority' => 'normal',
                    'status' => 'failed',
                    'error_message' => 'Mail transport returned failures: ' . implode(', ', $failures),
                    'metadata' => json_encode(array_merge($mailDebugContext, ['failures' => $failures])),
                ]);

                return false;
            }

            Log::info('Simple email sent successfully', $mailDebugContext);

            NotificationLog::create([
                'user_id' => null,
                'type' => 'simple_email_test',
                'channel' => 'email',
                'recipient' => (string) $email,
                'subject' => $processedSubject,
                'content' => $processedContent,
                'body' => $processedContent,
                'priority' => 'normal',
                'status' => 'sent',
                'metadata' => json_encode($mailDebugContext),
                'sent_at' => now(),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Simple email notification failed', [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'mail_config' => $this->getMailDebugContext($email, $subject),
            ]);

            NotificationLog::create([
                'user_id' => null,
                'type' => 'simple_email_test',
                'channel' => 'email',
                'recipient' => (string) $email,
                'subject' => $subject,
                'content' => $content,
                'body' => $content,
                'priority' => 'normal',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => json_encode($this->getMailDebugContext($email, $subject)),
            ]);

            return false;
        }
    }

    protected function getMailDebugContext($recipientEmail, $subject)
    {
        return [
            'mailer' => config('mail.default'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'smtp_encryption' => config('mail.mailers.smtp.encryption'),
            'smtp_username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'recipient' => $recipientEmail,
            'subject' => $subject,
        ];
    }
}