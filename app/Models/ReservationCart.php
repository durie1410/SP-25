<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\InventoryReservation;

class ReservationCart extends Model
{
    protected $fillable = ['user_id', 'reader_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationCartItem::class, 'cart_id');
    }

    public function getItemCountAttribute(): int
    {
        return $this->items()->count();
    }

    public function hasBook(int $bookId): bool
    {
        return $this->items()->where('book_id', $bookId)->exists();
    }

    public function addBook(int $bookId): ?ReservationCartItem
    {
        if ($this->hasBook($bookId)) {
            return null; // Đã có sách này trong giỏ
        }

        return $this->items()->create(['book_id' => $bookId]);
    }

    public function removeBook(int $bookId): bool
    {
        return (bool) $this->items()->where('book_id', $bookId)->delete();
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function submitReservations(string $notes = null): array
    {
        $createdReservations = [];
        $skippedCount = 0;

        return \DB::transaction(function () use ($notes, &$createdReservations, &$skippedCount) {
            // Lấy danh sách book_id từ giỏ hàng
            $bookIds = $this->items->pluck('book_id')->toArray();

            // Lock các reservation hiện có để tránh race condition
            $existingByBookId = InventoryReservation::where('user_id', $this->user_id)
                ->whereIn('book_id', $bookIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('book_id');

            foreach ($this->items as $item) {
                $existing = $existingByBookId->get($item->book_id);

                // Chỉ skip nếu đã có reservation đang pending/ready
                if ($existing && in_array($existing->status, ['pending', 'ready'], true)) {
                    $skippedCount++;
                    continue;
                }

                // Nếu đã fulfilled/cancelled, cho phép tạo lại (đè status cũ)
                try {
                    if ($existing) {
                        $existing->update([
                            'status' => 'pending',
                            'notes' => $notes,
                            'admin_note' => null,
                            'inventory_id' => null,
                            'borrow_id' => null,
                            'processed_by' => null,
                            'ready_at' => null,
                            'fulfilled_at' => null,
                            'cancelled_at' => null,
                        ]);
                        $createdReservations[] = $existing;
                    } else {
                        $reservation = InventoryReservation::create([
                            'book_id' => $item->book_id,
                            'user_id' => $this->user_id,
                            'reader_id' => $this->reader_id,
                            'status' => 'pending',
                            'notes' => $notes,
                        ]);
                        $createdReservations[] = $reservation;
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    // 1062 Duplicate entry (race condition)
                    $skippedCount++;
                }
            }

            // Xóa giỏ sau khi gửi yêu cầu
            $this->clear();

            return [
                'created' => $createdReservations,
                'skipped' => $skippedCount,
            ];
        });
    }
}
