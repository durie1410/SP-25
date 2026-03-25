<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Inventory;
use App\Services\NotificationService;
use Carbon\Carbon;

class InventoryReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'inventory_id',
        'borrow_id',
        'user_id',
        'reader_id',
        'reservation_code',
        'pickup_date',
        'pickup_time',
        'return_date',
        'total_fee',
        'status',
        'notes',
        'admin_note',
        'processed_by',
        'ready_at',
        'customer_confirmed_at',
        'fulfilled_at',
        'cancelled_at',
        'proof_images',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'return_date' => 'date',
        'total_fee' => 'decimal:2',
        'proof_images' => 'array',
        'ready_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Decode proof_images từ database, xử lý trường hợp bị json_encode sai (forward slash bị escape)
     */
    public function getProofImages(): array
    {
        $raw = $this->proof_images;

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw)) {
            // Xử lý dữ liệu bị lưu sai: forward slash bị escape thành \/
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            // Thử unescape \/ rồi decode lại
            $decoded = json_decode(str_replace('\\/', '/', $raw), true);
            if (is_array($decoded)) {
                return $decoded;
            }

            // Thử stripslashes rồi decode
            $decoded = json_decode(stripslashes($raw), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function markAsReady(string $adminNote = null, int $processedById = null): bool
    {
        return $this->update([
            'status' => 'ready',
            'admin_note' => $adminNote,
            'processed_by' => $processedById ?? auth()->id(),
            'ready_at' => now(),
        ]);
    }

    public function markAsFulfilled(int $processedById = null): bool
    {
        return $this->update([
            'status' => 'fulfilled',
            'processed_by' => $processedById ?? auth()->id(),
            'fulfilled_at' => now(),
        ]);
    }

    public function markAsOverdue(string $reason = null, int $processedById = null, bool $sendNotification = true): bool
    {
        // Ghi nhận ghi chú admin và chuyển trạng thái sang 'overdue'
        $note = $reason ? ($this->admin_note ? $this->admin_note . "\n" . $reason : $reason) : $this->admin_note;
        \DB::table('inventory_reservations')
            ->where('id', $this->id)
            ->update([
                'status' => 'overdue',
                'admin_note' => $note,
                'processed_by' => $processedById ?? auth()->id(),
            ]);
        $this->refresh();

        // Gửi thông báo quá hạn cho độc giả (nếu bật)
        if ($sendNotification) {
            $this->sendOverdueNotification();
        }

        return true;
    }

    /**
     * Gửi thông báo quá hạn cho độc giả — chỉ 1 notification_log (database), email gửi riêng
     */
    protected function sendOverdueNotification(): void
    {
        $userId = $this->reader?->user_id ?? $this->user_id;
        $email  = $this->reader?->email ?? $this->user?->email;

        $data = [
            'reader_name'  => $this->reader?->ho_ten ?? ($this->user?->name ?? 'Bạn'),
            'book_title'  => $this->book?->ten_sach ?? 'Sách',
            'pickup_date' => $this->pickup_date ? $this->pickup_date->format('d/m/Y') : '',
            'pickup_time' => $this->pickup_time ?? '',
        ];

        try {
            $ns = app(\App\Services\NotificationService::class);

            if ($userId) {
                // Chỉ ghi 1 log cho database notification
                $ns->sendNotification($userId, 'reservation_overdue', $data, ['database']);

                // Email gửi riêng để không tạo thêm log trùng type
                if (!empty($email)) {
                    $ns->sendSimpleEmail(
                        $email,
                        'Yêu cầu đặt trước đã quá hạn',
                        'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu mới nếu vẫn cần.',
                        $data
                    );
                }
            } elseif ($email) {
                $ns->sendSimpleEmail(
                    $email,
                    'Yêu cầu đặt trước đã quá hạn',
                    'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu mới nếu vẫn cần.',
                    $data
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send overdue notification for reservation #' . $this->id . ': ' . $e->getMessage());
        }
    }

    public function cancel(string $reason = null, int $processedById = null): bool
    {
        $updated = $this->update([
            'status' => 'cancelled',
            'admin_note' => $reason ? ($this->admin_note ? $this->admin_note . "\n" . $reason : $reason) : $this->admin_note,
            'processed_by' => $processedById ?? auth()->id(),
            'cancelled_at' => now(),
        ]);

        // Chỉ giải phóng inventory khi chưa tạo borrow (chưa fulfilled)
        if ($updated && $this->inventory_id && !$this->borrow_id) {
            $this->inventory->update(['status' => 'Co san']);
        }

        if ($updated) {
            // Không gọi assignNextWaitingReservation để tránh gửi thông báo trùng
            // Admin sẽ tự xử lý ready thủ công nếu cần
        }

        return $updated;
    }

    public function assignNextWaitingReservation(): void
    {
        $nextReservation = self::where('book_id', $this->book_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextReservation) {
            return;
        }

        $inventory = Inventory::where('book_id', $this->book_id)
            ->where('status', 'Co san')
            ->orderBy('id', 'asc')
            ->first();

        if (!$inventory) {
            return;
        }

        $nextReservation->update([
            'inventory_id' => $inventory->id,
            'status' => 'ready',
            'ready_at' => now(),
        ]);

        app(NotificationService::class)
            ->sendReservationReadyNotification($nextReservation->fresh(['book', 'reader.user', 'user']));
    }

    public function canBeCancelledBy(int $userId): bool
    {
        return $this->user_id === $userId && $this->isPending();
    }

    public function canBeCancelledByUser(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->user_id === $userId && in_array($this->status, ['pending', 'ready', 'overdue']);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Đang chờ',
            'ready' => 'Đã sẵn sàng',
            'fulfilled' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            'overdue' => 'Quá hạn',
            default => 'Không xác định',
        };
    }

    public function getProcessedByName(): ?string
    {
        return $this->processedBy ? $this->processedBy->name : null;
    }

    public function getPickupDateTimeAttribute(): ?Carbon
    {
        if (!$this->pickup_date) {
            return null;
        }

        $date = $this->pickup_date instanceof Carbon
            ? $this->pickup_date->copy()
            : Carbon::parse($this->pickup_date);

        $time = $this->pickup_time ?: config('library.open_hour', '08:00');

        try {
            [$hour, $minute] = array_pad(array_map('intval', explode(':', $time)), 2, 0);
            return $date->setTime($hour, $minute, 0);
        } catch (\Throwable $e) {
            return $date->setTime(8, 0, 0);
        }
    }

    public function getPickupDeadlineAttribute(): ?Carbon
    {
        $pickupDateTime = $this->pickup_date_time;

        if (!$pickupDateTime) {
            return null;
        }

        return $pickupDateTime->copy()->addHours(2);
    }

    public function getIsPickupOverdueAttribute(): bool
    {
        if (!in_array($this->status, ['pending', 'ready', 'overdue'], true)) {
            return false;
        }

        $deadline = $this->pickup_deadline;

        if (!$deadline) {
            return false;
        }

        return now()->gt($deadline);
    }

    public function getPickupDisplayAttribute(): string
    {
        $pickupDateTime = $this->pickup_date_time;

        if (!$pickupDateTime) {
            return 'N/A';
        }

        return $pickupDateTime->format('d/m/Y - H:i');
    }

    public function getPickupDeadlineDisplayAttribute(): string
    {
        $deadline = $this->pickup_deadline;

        if (!$deadline) {
            return 'N/A';
        }

        return $deadline->format('H:i');
    }
}
