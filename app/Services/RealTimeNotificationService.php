<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RealTimeNotificationService
{
    /**
     * Send real-time notification to user
     */
    public static function sendToUser($userId, $type, $title, $message, $data = [])
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // Create notification log
            $notification = NotificationLog::create([
                'user_id' => $userId,
                'type' => $type,
                'channel' => 'realtime',
                'recipient' => (string) ($userId),
                'subject' => $title,
                'content' => $message,
                'body' => $message,
                'priority' => $data['priority'] ?? 'normal',
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Store in cache for real-time delivery
            $notificationData = [
                'id' => $notification->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'timestamp' => now()->toISOString(),
                'read' => false,
            ];

            // Store in user's notification queue
            $cacheKey = "user_notifications_{$userId}";
            $notifications = Cache::get($cacheKey, []);
            $notifications[] = $notificationData;
            
            // Keep only last 50 notifications
            if (count($notifications) > 50) {
                $notifications = array_slice($notifications, -50);
            }
            
            Cache::put($cacheKey, $notifications, now()->addDays(7));

            // Trigger real-time event (for WebSocket/Pusher)
            event(new \App\Events\NotificationSent($userId, $notificationData));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send real-time notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public static function sendToUsers($userIds, $type, $title, $message, $data = [])
    {
        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = self::sendToUser($userId, $type, $title, $message, $data);
        }
        return $results;
    }

    /**
     * Send notification to all online users
     */
    public static function sendToOnlineUsers($type, $title, $message, $data = [])
    {
        $onlineUsers = self::getOnlineUsers();
        return self::sendToUsers($onlineUsers, $type, $title, $message, $data);
    }

    /**
     * Get user's notifications
     */
    public static function getUserNotifications($userId, $limit = 20)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);
        
        return array_slice(array_reverse($notifications), 0, $limit);
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($userId, $notificationId)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);
        
        foreach ($notifications as &$notification) {
            if ($notification['id'] == $notificationId) {
                $notification['read'] = true;
                break;
            }
        }
        
        Cache::put($cacheKey, $notifications, now()->addDays(7));
        
        // Update database
        NotificationLog::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read
     */
    public static function markAllAsRead($userId)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);
        
        foreach ($notifications as &$notification) {
            $notification['read'] = true;
        }
        
        Cache::put($cacheKey, $notifications, now()->addDays(7));
        
        // Update database
        NotificationLog::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notification count
     */
    public static function getUnreadCount($userId)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);
        
        return collect($notifications)->where('read', false)->count();
    }

    /**
     * Get online users
     */
    public static function getOnlineUsers()
    {
        $onlineUsers = Cache::get('online_users', []);
        return array_keys($onlineUsers);
    }

    /**
     * Mark user as online
     */
    public static function markUserOnline($userId)
    {
        $onlineUsers = Cache::get('online_users', []);
        $onlineUsers[$userId] = now()->toISOString();
        
        // Remove users who haven't been active for 5 minutes
        foreach ($onlineUsers as $id => $lastSeen) {
            if (now()->diffInMinutes($lastSeen) > 5) {
                unset($onlineUsers[$id]);
            }
        }
        
        Cache::put('online_users', $onlineUsers, now()->addMinutes(10));
    }

    /**
     * Mark user as offline
     */
    public static function markUserOffline($userId)
    {
        $onlineUsers = Cache::get('online_users', []);
        unset($onlineUsers[$userId]);
        Cache::put('online_users', $onlineUsers, now()->addMinutes(10));
    }

    /**
     * Send system-wide announcements
     */
    public static function sendAnnouncement($title, $message, $priority = 'normal')
    {
        $onlineUsers = self::getOnlineUsers();
        
        return self::sendToUsers($onlineUsers, 'announcement', $title, $message, [
            'priority' => $priority,
            'system' => true
        ]);
    }

    /**
     * Send book availability notifications
     */
    public static function notifyBookAvailable($bookId, $userId)
    {
        $book = \App\Models\Book::find($bookId);
        if (!$book) return false;

        return self::sendToUser($userId, 'book_available', 
            'Sách có sẵn!', 
            "Sách '{$book->ten_sach}' đã có sẵn để mượn.",
            [
                'book_id' => $bookId,
                'book_title' => $book->ten_sach,
                'action_url' => route('books.show', $bookId)
            ]
        );
    }

    /**
     * Send overdue book notifications
     */
    public static function notifyOverdueBook($borrowId, $userId)
    {
        $borrow = \App\Models\Borrow::with('book')->find($borrowId);
        if (!$borrow) return false;

        return self::sendToUser($userId, 'overdue_book',
            'Sách quá hạn trả!',
            "Sách '{$borrow->book->ten_sach}' đã quá hạn trả. Vui lòng trả sách sớm nhất có thể.",
            [
                'borrow_id' => $borrowId,
                'book_title' => $borrow->book->ten_sach,
                'due_date' => $borrow->ngay_hen_tra->format('d/m/Y'),
                'action_url' => route('borrows.show', $borrowId)
            ]
        );
    }

    /**
     * Send reservation ready notifications
     */
    public static function notifyReservationReady($reservationId, $userId)
    {
        // Reservation model đã bị xóa
        $reservation = null;
        if (!$reservation) return false;

        return self::sendToUser($userId, 'reservation_ready',
            'Đặt chỗ sẵn sàng!',
            "Sách '{$reservation->book->ten_sach}' đã sẵn sàng để mượn.",
            [
                'reservation_id' => $reservationId,
                'book_title' => $reservation->book->ten_sach,
                'action_url' => route('reservations.show', $reservationId)
            ]
        );
    }
}























