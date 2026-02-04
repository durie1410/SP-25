<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationBellController extends Controller
{
    public function count(Request $request)
    {
        $user = $request->user();
        $service = app(NotificationService::class);

        return response()->json([
            'count' => $service->getUnreadCount($user->id),
        ]);
    }

    public function latest(Request $request)
    {
        try {
            $user = $request->user();
            $limit = (int) $request->query('limit', 10);
            $limit = max(1, min($limit, 30));

            $service = app(NotificationService::class);

            return response()->json([
                'items' => $service->getUserNotifications($user->id, $limit),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch latest notifications', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Server error while fetching notifications.'], 500);
        }
    }

    public function markRead(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'id' => 'required|integer',
        ]);

        $service = app(NotificationService::class);
        $service->markAsRead((int) $data['id'], $user->id);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $service = app(NotificationService::class);
        $service->markAllAsRead($user->id);

        return response()->json(['success' => true]);
    }
}
