<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Inventory;
use App\Services\NotificationService;

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
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

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

    public function cancel(string $reason = null, int $processedById = null): bool
    {
        $updated = $this->update([
            'status' => 'cancelled',
            'admin_note' => $reason ? ($this->admin_note ? $this->admin_note . "\n" . $reason : $reason) : $this->admin_note,
            'processed_by' => $processedById ?? auth()->id(),
            'cancelled_at' => now(),
        ]);

        if ($updated && $this->inventory_id) {
            $this->inventory->update(['status' => 'Co san']);
        }

        if ($updated) {
            $this->assignNextWaitingReservation();
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

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Đang chờ',
            'ready' => 'Đã sẵn sàng',
            'fulfilled' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    public function getProcessedByName(): ?string
    {
        return $this->processedBy ? $this->processedBy->name : null;
    }
}
