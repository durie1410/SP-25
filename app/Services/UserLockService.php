<?php

namespace App\Services;

use App\Models\InventoryReservation;
use App\Models\User;

class UserLockService
{
    public const NO_SHOW_LOCK_MESSAGE = 'Tài khoản của bạn đã bị khóa do vi phạm quy định của LibNet. Vui lòng liên hệ admin để được mở khóa.';
    public const CANCELLATION_LOCK_MESSAGE = 'Tài khoản của bạn đã bị khóa do hủy đặt trước quá số lần cho phép. Vui lòng liên hệ admin để được mở khóa.';

    public function incrementNoShowAndAutoLockByReservation(InventoryReservation $reservation): ?User
    {
        $user = $reservation->reader?->user ?? ($reservation->user_id ? User::find($reservation->user_id) : null);

        if (!$user) {
            return null;
        }

        $threshold = max(1, (int) config('library.no_show_lock_threshold', 3));

        $user->no_show_count = (int) ($user->no_show_count ?? 0) + 1;

        if ($user->no_show_count >= $threshold) {
            $user->is_locked = true;
            $user->locked_at = now();
            $user->locked_reason = self::NO_SHOW_LOCK_MESSAGE;
        }

        $user->save();

        return $user;
    }

    public function resetLockAndNoShow(User $user): void
    {
        $user->is_locked = false;
        $user->locked_at = null;
        $user->locked_reason = null;
        $user->no_show_count = 0;
        $user->save();
    }

    public function applyCancellationAutoLock(InventoryReservation $reservation, ?int $actorId = null, bool $wasApproved = false): ?User
    {
        $userId = (int) ($reservation->user_id ?? 0);
        $actorId = (int) ($actorId ?? auth()->id() ?? 0);

        // Chỉ xét vi phạm hủy nếu đơn đã được duyệt (ready) trước khi bị hủy.
        if (!$wasApproved) {
            return null;
        }

        // Chỉ tính vi phạm khi chính user tự hủy yêu cầu của mình.
        if ($userId <= 0 || $actorId <= 0 || $userId !== $actorId) {
            return null;
        }

        $user = $reservation->reader?->user ?? User::find($userId);
        if (!$user) {
            return null;
        }

        $dailyThreshold = max(1, (int) config('library.cancel_lock_daily_threshold', 3));
        $weeklyThreshold = max(1, (int) config('library.cancel_lock_weekly_threshold', 7));

        $dailyCount = InventoryReservation::query()
            ->where('user_id', $userId)
            ->where('status', 'cancelled')
            ->where('processed_by', $userId)
            ->whereNotNull('ready_at')
            ->whereNotNull('cancelled_at')
            ->where('cancelled_at', '>=', now()->copy()->subDay())
            ->count();

        $weeklyCount = InventoryReservation::query()
            ->where('user_id', $userId)
            ->where('status', 'cancelled')
            ->where('processed_by', $userId)
            ->whereNotNull('ready_at')
            ->whereNotNull('cancelled_at')
            ->where('cancelled_at', '>=', now()->copy()->subDays(7))
            ->count();

        if ($dailyCount >= $dailyThreshold || $weeklyCount >= $weeklyThreshold) {
            $user->is_locked = true;
            $user->locked_at = now();
            $user->locked_reason = self::CANCELLATION_LOCK_MESSAGE;
            $user->save();
        }

        return $user;
    }
}
