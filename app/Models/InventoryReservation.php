<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'inventory_id',
        'borrow_id',
        'user_id',
        'reader_id',
        'pickup_date',
        'return_date',
        'total_fee',
        'status',
        'notes',
        'admin_note',
        'processed_by',
        'ready_at',
        'fulfilled_at',
        'cancelled_at',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'return_date' => 'date',
        'total_fee' => 'decimal:2',
        'ready_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Quan hệ với sách
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // Quan hệ với bản sao sách (có thể null nếu chưa gán)
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    // Quan hệ với user (người tạo yêu cầu)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với reader (người sẽ mượn sách)
    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    // Quan hệ với admin xử lý
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scope: Lấy các yêu cầu đang chờ
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope: Lấy các yêu cầu đã sẵn sàng
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    // Scope: Lấy các yêu cầu đã hoàn thành
    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    // Scope: Lấy các yêu cầu đã hủy
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Kiểm tra xem yêu cầu có đang chờ không
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Kiểm tra xem sách đã sẵn sàng chưa
    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    // Kiểm tra xem yêu cầu đã hoàn thành chưa
    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    // Kiểm tra xem yêu cầu đã bị hủy chưa
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Đánh dấu là đã sẵn sàng
    public function markAsReady(string $adminNote = null, int $processedById = null): bool
    {
        return $this->update([
            'status' => 'ready',
            'admin_note' => $adminNote,
            'processed_by' => $processedById ?? auth()->id(),
            'ready_at' => now(),
        ]);
    }

    // Đánh dấu là đã hoàn thành (đã nhận sách)
    public function markAsFulfilled(int $processedById = null): bool
    {
        return $this->update([
            'status' => 'fulfilled',
            'processed_by' => $processedById ?? auth()->id(),
            'fulfilled_at' => now(),
        ]);
    }

    // Hủy yêu cầu
    public function cancel(string $reason = null, int $processedById = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'admin_note' => $reason ? ($this->admin_note ? $this->admin_note . "\n" . $reason : $reason) : $this->admin_note,
            'processed_by' => $processedById ?? auth()->id(),
            'cancelled_at' => now(),
        ]);
    }

    // Kiểm tra xem user hiện tại có thể hủy yêu cầu không
    public function canBeCancelledBy(int $userId): bool
    {
        return $this->user_id === $userId && $this->isPending();
    }

    // Lấy thông báo trạng thái
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Đang chờ',
            'ready' => 'Đã sẵn sàng',
            'fulfilled' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    // Lấy tên người xử lý
    public function getProcessedByName(): ?string
    {
        return $this->processedBy ? $this->processedBy->name : null;
    }
}